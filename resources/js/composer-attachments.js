const acceptedPastedImageTypes = new Set([
    'image/gif',
    'image/heic',
    'image/heif',
    'image/jpeg',
    'image/png',
    'image/webp',
]);
const previewableImageTypes = new Set([
    'image/gif',
    'image/jpeg',
    'image/png',
    'image/webp',
]);
const maximumAttachments = 4;

export const clipboardImages = (clipboardData) => Array.from(clipboardData?.files ?? [])
    .filter((file) => file.type.startsWith('image/'));

export const supportedPastedImages = (files) => files
    .filter((file) => acceptedPastedImageTypes.has(file.type));

export const isPreviewableImage = (file) => previewableImageTypes.has(file.type);

export const mergeAttachments = (current, incoming) => {
    const files = [...current, ...incoming];

    return {
        files: files.slice(0, maximumAttachments),
        overflowed: files.length > maximumAttachments,
    };
};

export const limitAttachments = (files) => ({
    files: files.slice(0, maximumAttachments),
    overflowed: files.length > maximumAttachments,
});
