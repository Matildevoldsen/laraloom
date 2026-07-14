const mentionPattern = /(^|[^A-Za-z0-9_-])@([A-Za-z0-9_-]{0,30})$/;
const hashtagPattern = /(^|[^\p{L}\p{N}_])#([\p{L}\p{N}_]{0,100})$/u;

export const activeComposerToken = (textarea) => {
    const caret = textarea.selectionStart;

    if (caret === null || caret !== textarea.selectionEnd) {
        return null;
    }

    const beforeCaret = textarea.value.slice(0, caret);
    const mention = beforeCaret.match(mentionPattern);
    const hashtag = beforeCaret.match(hashtagPattern);
    const match = mention ?? hashtag;

    if (!match) {
        return null;
    }

    const trigger = mention ? '@' : '#';
    const query = match[2];
    const start = caret - query.length - 1;
    const segmentStart = Math.max(
        beforeCaret.lastIndexOf(' '),
        beforeCaret.lastIndexOf('\n'),
        beforeCaret.lastIndexOf('\t'),
    ) + 1;

    if (/^(?:https?:\/\/|www\.)/i.test(beforeCaret.slice(segmentStart))) {
        return null;
    }

    const suffixPattern = trigger === '@'
        ? /^[A-Za-z0-9_-]*/
        : /^[\p{L}\p{N}_]*/u;
    const suffix = textarea.value.slice(caret).match(suffixPattern)?.[0] ?? '';

    return { trigger, query, start, end: caret + suffix.length, caret };
};
