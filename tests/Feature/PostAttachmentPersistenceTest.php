<?php

use App\Actions\CreatePostAction;
use App\Actions\PersistPostAttachmentsAction;
use App\Data\StoredPostAttachments;
use App\Models\Hashtag;
use App\Models\Mention;
use App\Models\Post;
use App\Models\PostAttachment;
use App\Models\User;
use App\PostKind;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use function Pest\Laravel\mock;

test('attachments and references are persisted with the post after every upload succeeds', function (): void {
    Storage::fake('r2');
    $author = User::factory()->create(['username' => 'author']);
    $mentioned = User::factory()->create(['username' => 'mentioned']);

    $post = app(CreatePostAction::class)->execute($author, [
        'kind' => PostKind::Note->value,
        'body' => 'A release for @mentioned and #Laravel.',
        'attachments' => [
            UploadedFile::fake()->image('release.jpg'),
            UploadedFile::fake()->create('demo.mp4', 512, 'video/mp4'),
        ],
    ])->load(['attachments', 'hashtags', 'mentions']);

    expect($post->attachments)->toHaveCount(2)
        ->and($post->attachments->pluck('media_type')->all())->toBe(['image', 'video'])
        ->and($post->hashtags->pluck('slug')->all())->toBe(['laravel'])
        ->and($post->mentions->pluck('mentioned_user_id')->all())->toBe([$mentioned->id]);

    foreach ($post->attachments as $attachment) {
        expect(Str::isUlid(basename(dirname($attachment->path))))->toBeTrue()
            ->and(Str::isUlid(pathinfo($attachment->path, PATHINFO_FILENAME)))->toBeTrue()
            ->and($attachment->path)->not->toContain($attachment->original_name);
        Storage::disk('r2')->assertExists($attachment->path);
    }
});

test('a partial upload failure removes every written object and creates no records', function (): void {
    Storage::fake('r2');
    $realDisk = Storage::disk('r2');
    $failingDisk = Mockery::mock(Filesystem::class);
    $writeCount = 0;

    $failingDisk->shouldReceive('putFileAs')
        ->twice()
        ->andReturnUsing(function (
            string $directory,
            UploadedFile $file,
            string $name,
            array $options,
        ) use (&$writeCount, $realDisk): string {
            $storedPath = $realDisk->putFileAs($directory, $file, $name, $options);

            if ($storedPath === false) {
                throw new RuntimeException('The test disk could not write the attachment.');
            }

            $writeCount++;

            if ($writeCount === 2) {
                throw new RuntimeException('The second upload failed.');
            }

            return $storedPath;
        });
    $failingDisk->shouldReceive('delete')
        ->once()
        ->withArgs(fn (array $paths): bool => count($paths) === 2)
        ->andReturnUsing(fn (array $paths): bool => $realDisk->delete($paths));
    Storage::set('r2', $failingDisk);

    $author = User::factory()->create();

    expect(fn (): Post => app(CreatePostAction::class)->execute($author, [
        'kind' => PostKind::Note->value,
        'body' => 'This post must never be published.',
        'attachments' => [
            UploadedFile::fake()->image('first.jpg'),
            UploadedFile::fake()->image('second.jpg'),
        ],
    ]))->toThrow(RuntimeException::class, 'The second upload failed.');

    expect(Post::query()->doesntExist())->toBeTrue()
        ->and(PostAttachment::query()->doesntExist())->toBeTrue();
    $realDisk->assertDirectoryEmpty('/');
});

test('a database failure rolls back every record and removes every uploaded object', function (): void {
    Storage::fake('r2');
    $author = User::factory()->create(['username' => 'author']);
    $mentioned = User::factory()->create(['username' => 'mentioned']);
    $persistAttachments = mock(PersistPostAttachmentsAction::class);

    $persistAttachments->shouldReceive('execute')
        ->once()
        ->andReturnUsing(function (Post $post, StoredPostAttachments $attachments): never {
            $attachment = $attachments->items[0] ?? throw new RuntimeException('Missing stored attachment.');
            $post->attachments()->create($attachment->toAttributes());

            throw new RuntimeException('The database transaction failed.');
        });

    expect(fn (): Post => app(CreatePostAction::class)->execute($author, [
        'kind' => PostKind::Note->value,
        'body' => 'A failed post for @mentioned and #Laravel.',
        'attachments' => [UploadedFile::fake()->image('rollback.jpg')],
    ]))->toThrow(RuntimeException::class, 'The database transaction failed.');

    expect(Post::query()->doesntExist())->toBeTrue()
        ->and(PostAttachment::query()->doesntExist())->toBeTrue()
        ->and(Mention::query()->doesntExist())->toBeTrue()
        ->and(Hashtag::query()->doesntExist())->toBeTrue()
        ->and($mentioned->notifications()->doesntExist())->toBeTrue();
    Storage::disk('r2')->assertDirectoryEmpty('/');
});
