import assert from 'node:assert/strict';
import test from 'node:test';
import { createComposerForm } from '../../resources/js/composer-form.js';

class TestDataTransfer {
    constructor() {
        this.files = [];
        this.items = {
            add: (file) => this.files.push(file),
        };
    }
}

const image = (name, type = 'image/png') => ({
    lastModified: 1,
    name,
    size: 1024,
    type,
});

test('pasting an image updates the submitted file input and removable preview', () => {
    globalThis.DataTransfer = TestDataTransfer;
    const createdUrls = [];
    const revokedUrls = [];
    URL.createObjectURL = (file) => {
        const url = `blob:${file.name}`;
        createdUrls.push(url);

        return url;
    };
    URL.revokeObjectURL = (url) => revokedUrls.push(url);

    const form = createComposerForm({ body: '', showDetails: false });
    form.$refs = { attachments: { files: [] } };
    let prevented = false;

    form.pasteAttachments({
        clipboardData: { files: [image('clipboard.png')] },
        preventDefault: () => {
            prevented = true;
        },
    });

    assert.equal(prevented, true);
    assert.equal(form.attachments.length, 1);
    assert.equal(form.$refs.attachments.files[0].name, 'clipboard.png');
    assert.equal(form.attachmentItems[0].url, 'blob:clipboard.png');
    assert.deepEqual(createdUrls, ['blob:clipboard.png']);

    form.removeAttachment(0);

    assert.equal(form.$refs.attachments.files.length, 0);
    assert.deepEqual(revokedUrls, ['blob:clipboard.png']);
});

test('unsupported pasted images report an error without changing the file input', () => {
    globalThis.DataTransfer = TestDataTransfer;
    const form = createComposerForm({ body: '', showDetails: false });
    form.$refs = { attachments: { files: [] } };

    form.pasteAttachments({
        clipboardData: { files: [image('scan.tiff', 'image/tiff')] },
        preventDefault: () => {},
    });

    assert.equal(form.attachments.length, 0);
    assert.match(form.attachmentError, /JPEG/);
});
