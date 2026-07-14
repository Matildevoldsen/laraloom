<?php

namespace App\View\Components;

use App\Models\Mention;
use App\Models\Post;
use App\Models\User;
use App\Services\PostReferenceExtractor;
use App\SocialTextTokenType;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Illuminate\View\Component;

class PostBody extends Component
{
    public readonly HtmlString $content;

    public function __construct(Post $post, PostReferenceExtractor $extractor)
    {
        /** @var array<string, User> $mentionedUsers */
        $mentionedUsers = $post->relationLoaded('mentions')
            ? $post->mentions
                ->filter(fn (Mention $mention): bool => $mention->mentionedUser instanceof User)
                ->mapWithKeys(fn (Mention $mention): array => [
                    mb_strtolower($mention->handle) => $mention->mentionedUser,
                ])
                ->all()
            : [];

        $html = '';

        foreach ($extractor->tokens($post->body) as $token) {
            $text = e($token->text);

            if ($token->type === SocialTextTokenType::Hashtag && $token->value !== null) {
                $url = e(route('hashtags.show', ['hashtag' => $token->value]));
                $html .= '<a href="'.$url.'" class="social-token">'.$text.'</a>';

                continue;
            }

            $mentionedUser = $token->value === null ? null : ($mentionedUsers[$token->value] ?? null);

            if ($token->type === SocialTextTokenType::Mention && $mentionedUser instanceof User) {
                $url = e(route('profiles.show', $mentionedUser));
                $html .= '<a href="'.$url.'" class="social-token">'.$text.'</a>';

                continue;
            }

            $html .= $text;
        }

        $this->content = new HtmlString($html);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.post-body');
    }
}
