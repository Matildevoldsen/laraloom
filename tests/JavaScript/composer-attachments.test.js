import assert from 'node:assert/strict';
import test from 'node:test';
import {
    clipboardImages,
    isPreviewableImage,
    limitAttachments,
    mergeAttachments,
    supportedPastedImages,
} from '../../resources/js/composer-attachments.js';

const file = (name, type) => ({ name, type });

test('clipboard images excludes pasted text and non-image files', () => {
    const image = file('screenshot.png', 'image/png');
    const clipboardData = {
        files: [image, file('notes.txt', 'text/plain')],
    };

    assert.deepEqual(clipboardImages(clipboardData), [image]);
    assert.deepEqual(clipboardImages(null), []);
});

test('supported pasted images follow the post upload image types', () => {
    const png = file('screenshot.png', 'image/png');
    const heic = file('photo.heic', 'image/heic');

    assert.deepEqual(
        supportedPastedImages([png, heic, file('scan.tiff', 'image/tiff')]),
        [png, heic],
    );
    assert.equal(isPreviewableImage(png), true);
    assert.equal(isPreviewableImage(heic), false);
});

test('pasted images append to the current attachment list up to four files', () => {
    const current = [file('one.png', 'image/png'), file('two.mp4', 'video/mp4')];
    const incoming = [
        file('three.png', 'image/png'),
        file('four.png', 'image/png'),
        file('five.png', 'image/png'),
    ];
    const merged = mergeAttachments(current, incoming);

    assert.deepEqual(merged.files, [...current, ...incoming].slice(0, 4));
    assert.equal(merged.overflowed, true);
    assert.equal(limitAttachments(current).overflowed, false);
});
