const tokenPattern = /(?<![A-Za-z0-9_-])@[A-Za-z0-9_-]{1,30}|(?<![\p{L}\p{N}_])#[\p{L}\p{N}_]{1,100}/gu;

const isInsideUrl = (text, offset) => {
    const segment = text.slice(0, offset).match(/\S*$/u)?.[0]
        ?.replace(/^[([{<'"]+/, '') ?? '';

    return /^(?:https?:\/\/|www\.)/i.test(segment);
};

export const composerTokens = (text) => {
    const tokens = [];
    let offset = 0;

    for (const match of text.matchAll(tokenPattern)) {
        const index = match.index ?? 0;

        if (index > offset) {
            tokens.push({ text: text.slice(offset, index), type: 'text' });
        }

        tokens.push({
            text: match[0],
            type: isInsideUrl(text, index)
                ? 'text'
                : match[0].startsWith('@') ? 'mention' : 'hashtag',
        });
        offset = index + match[0].length;
    }

    if (offset < text.length) {
        tokens.push({ text: text.slice(offset), type: 'text' });
    }

    return tokens;
};

const mirroredProperties = [
    'border-bottom-width',
    'border-left-width',
    'border-right-width',
    'border-top-width',
    'box-sizing',
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

const offsetWithin = (element, ancestor) => {
    let current = element;
    let left = 0;
    let top = 0;

    while (current instanceof HTMLElement && current !== ancestor) {
        left += current.offsetLeft;
        top += current.offsetTop;
        current = current.offsetParent;
    }

    if (current === ancestor) {
        return { left, top };
    }

    const ancestorRect = ancestor.getBoundingClientRect();
    const elementRect = element.getBoundingClientRect();

    return {
        left: elementRect.left - ancestorRect.left,
        top: elementRect.top - ancestorRect.top,
    };
};

const createHighlighter = () => ({
    highlighter: null,
    observer: null,
    resolvedMentions: null,
    textarea: null,

    init() {
        this.highlighter = this.$refs.highlighter;
        this.textarea = this.$root.querySelector('[data-composer-textarea]');

        if (!(this.highlighter instanceof HTMLElement) || !(this.textarea instanceof HTMLTextAreaElement)) {
            return;
        }

        this.resolvedMentions = new Set();
        this.resolveMention = (event) => {
            const handle = event.detail?.handle;

            if (typeof handle !== 'string' || handle === '') {
                return;
            }

            this.resolvedMentions.add(handle.toLowerCase());
            this.render();
        };
        this.sync = () => {
            this.render();
            this.syncScroll();
        };
        this.syncScroll = () => {
            this.highlighter.scrollTop = this.textarea.scrollTop;
            this.highlighter.scrollLeft = this.textarea.scrollLeft;
        };
        this.syncLayout = () => {
            const computed = window.getComputedStyle(this.textarea);
            const offset = offsetWithin(this.textarea, this.$root);

            mirroredProperties.forEach((property) => {
                this.highlighter.style.setProperty(property, computed.getPropertyValue(property));
            });
            Object.assign(this.highlighter.style, {
                height: `${this.textarea.offsetHeight}px`,
                left: `${offset.left}px`,
                top: `${offset.top}px`,
                width: `${this.textarea.offsetWidth}px`,
            });
            this.render();
            this.syncScroll();
        };
        this.textarea.dataset.highlightActive = 'true';
        this.textarea.addEventListener('composer:mention-resolved', this.resolveMention);
        this.textarea.addEventListener('input', this.sync);
        this.textarea.addEventListener('scroll', this.syncScroll);
        this.observer = new ResizeObserver(this.syncLayout);
        this.observer.observe(this.textarea);
        this.$nextTick(this.syncLayout);
    },

    destroy() {
        this.textarea?.removeEventListener('composer:mention-resolved', this.resolveMention);
        this.textarea?.removeEventListener('input', this.sync);
        this.textarea?.removeEventListener('scroll', this.syncScroll);
        this.observer?.disconnect();
    },

    render() {
        this.highlighter.replaceChildren();

        for (const token of composerTokens(this.textarea.value)) {
            const isHighlighted = token.type === 'hashtag'
                || (token.type === 'mention' && this.resolvedMentions.has(token.text.slice(1).toLowerCase()));
            const node = ! isHighlighted
                ? document.createTextNode(token.text)
                : Object.assign(document.createElement('span'), {
                    className: 'social-token',
                    textContent: token.text,
                });

            this.highlighter.append(node);
        }

        if (this.textarea.value.endsWith('\n')) {
            this.highlighter.append(document.createTextNode('\u200b'));
        }
    },

    sync: () => {},
    syncLayout: () => {},
    syncScroll: () => {},
    resolveMention: () => {},
});

document.addEventListener('alpine:init', () => {
    window.Alpine.data('composerHighlighter', createHighlighter);
});
