import {
    clipboardImages,
    isPreviewableImage,
    limitAttachments,
    mergeAttachments,
    supportedPastedImages,
} from './composer-attachments.js';

export const createComposerForm = ({ body, showDetails }) => ({
    attachmentError: '',
    attachmentItems: [],
    attachments: [],
    body,
    showDetails,
    submitting: false,

    destroy() {
        this.releaseAttachmentUrls();
    },

    selectAttachments(event) {
        this.setAttachments(Array.from(event.target.files ?? []));
    },

    pasteAttachments(event) {
        const images = clipboardImages(event.clipboardData);

        if (images.length === 0) {
            return;
        }

        event.preventDefault();
        const supportedImages = supportedPastedImages(images);

        if (supportedImages.length === 0) {
            this.attachmentError = 'Paste a JPEG, PNG, WebP, GIF, HEIC, or HEIF image.';

            return;
        }

        const merged = mergeAttachments(this.attachments, supportedImages);
        this.setAttachments(merged.files, merged.overflowed
            ? 'You can attach up to 4 files.'
            : '');
    },

    removeAttachment(index) {
        this.setAttachments(this.attachments.filter((file, fileIndex) => fileIndex !== index));
    },

    setAttachments(files, error = '') {
        const limited = limitAttachments(files);
        const transfer = new DataTransfer();

        limited.files.forEach((file) => transfer.items.add(file));
        this.$refs.attachments.files = transfer.files;
        this.releaseAttachmentUrls();
        this.attachments = limited.files;
        this.attachmentItems = limited.files.map((file, index) => ({
            id: `${file.name}-${file.size}-${file.lastModified}-${index}`,
            index,
            name: file.name || `Pasted image ${index + 1}`,
            url: isPreviewableImage(file) ? URL.createObjectURL(file) : null,
        }));
        this.attachmentError = error || (limited.overflowed
            ? 'You can attach up to 4 files.'
            : '');
    },

    releaseAttachmentUrls() {
        this.attachmentItems.forEach((attachment) => {
            if (attachment.url) {
                URL.revokeObjectURL(attachment.url);
            }
        });
    },
});

if (typeof document !== 'undefined') {
    document.addEventListener('alpine:init', () => {
        window.Alpine.data('composerForm', createComposerForm);
    });
}
