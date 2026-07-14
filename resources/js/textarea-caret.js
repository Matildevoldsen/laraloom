const mirroredProperties = [
    'border-bottom-width',
    'border-left-width',
    'border-right-width',
    'border-top-width',
    'box-sizing',
    'direction',
    'font-family',
    'font-size',
    'font-style',
    'font-variant',
    'font-weight',
    'letter-spacing',
    'line-height',
    'padding-bottom',
    'padding-left',
    'padding-right',
    'padding-top',
    'tab-size',
    'text-align',
    'text-indent',
    'text-rendering',
    'text-transform',
    'word-spacing',
];

export const textareaCaret = (textarea, position) => {
    const computed = window.getComputedStyle(textarea);
    const mirror = document.createElement('div');
    const marker = document.createElement('span');

    mirror.setAttribute('aria-hidden', 'true');
    Object.assign(mirror.style, {
        height: computed.height,
        left: '-9999px',
        overflow: 'hidden',
        position: 'fixed',
        top: '0',
        visibility: 'hidden',
        whiteSpace: 'pre-wrap',
        width: computed.width,
        wordBreak: 'break-word',
        overflowWrap: 'break-word',
    });

    mirroredProperties.forEach((property) => {
        mirror.style.setProperty(property, computed.getPropertyValue(property));
    });

    mirror.textContent = textarea.value.slice(0, position);
    marker.textContent = textarea.value.slice(position) || '\u200b';
    mirror.append(marker);
    document.body.append(mirror);

    const textareaRect = textarea.getBoundingClientRect();
    const borderLeft = Number.parseFloat(computed.borderLeftWidth) || 0;
    const borderTop = Number.parseFloat(computed.borderTopWidth) || 0;
    const lineHeight = Number.parseFloat(computed.lineHeight)
        || (Number.parseFloat(computed.fontSize) * 1.2);
    const coordinates = {
        left: textareaRect.left + marker.offsetLeft + borderLeft - textarea.scrollLeft,
        top: textareaRect.top + marker.offsetTop + borderTop - textarea.scrollTop,
        lineHeight,
    };

    mirror.remove();

    return coordinates;
};
