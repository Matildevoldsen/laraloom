import { textareaCaret } from './textarea-caret';

export const positionComposerMenu = (menu, textarea, token) => {
    const caret = textareaCaret(textarea, token.caret);
    const tokenStart = textareaCaret(textarea, token.start);
    const textareaRect = textarea.getBoundingClientRect();
    const viewport = window.visualViewport;
    const viewportLeft = viewport?.offsetLeft ?? 0;
    const viewportTop = viewport?.offsetTop ?? 0;
    const viewportWidth = viewport?.width ?? window.innerWidth;
    const viewportHeight = viewport?.height ?? window.innerHeight;
    const width = Math.min(320, textareaRect.width, viewportWidth - 24);
    const anchorLeft = Math.abs(tokenStart.top - caret.top) < 1
        ? tokenStart.left
        : caret.left;
    const left = Math.min(
        Math.max(anchorLeft, viewportLeft + 12),
        viewportLeft + viewportWidth - width - 12,
    );
    let top = caret.top + caret.lineHeight + 8;
    let availableHeight = viewportTop + viewportHeight - top - 12;

    if (availableHeight < 96) {
        const menuHeight = Math.min(288, menu.scrollHeight || 96);
        top = Math.max(viewportTop + 12, caret.top - menuHeight - 8);
        availableHeight = caret.top - top - 8;
    }

    Object.assign(menu.style, {
        inset: 'auto',
        left: `${left}px`,
        margin: '0',
        maxHeight: `${Math.max(96, Math.min(288, availableHeight))}px`,
        top: `${top}px`,
        width: `${width}px`,
    });
};
