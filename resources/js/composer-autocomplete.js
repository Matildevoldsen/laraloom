import { activeComposerToken } from './composer-token';
import { positionComposerMenu } from './composer-menu-position';

const selectionKeys = new Set(['ArrowLeft', 'ArrowRight', 'Home', 'End']);
const verticalSelectionKeys = new Set(['ArrowDown', 'ArrowUp']);

const createAutocomplete = (wire) => ({
    activeIndex: 0,
    failed: false,
    instanceId: crypto.randomUUID(),
    loading: false,
    open: false,
    requestSequence: 0,
    suggestions: [],
    token: null,
    composing: false,
    timer: null,
    textarea: null,
    handlers: {},

    init() {
        this.textarea = this.$root.closest('form')?.querySelector('[data-composer-textarea]') ?? null;

        if (!(this.textarea instanceof HTMLTextAreaElement)) {
            return;
        }

        this.$refs.menu.id = `composer-suggestions-${this.instanceId}`;
        this.textarea.setAttribute('aria-autocomplete', 'list');
        this.textarea.setAttribute('aria-controls', this.$refs.menu.id);
        this.textarea.setAttribute('aria-expanded', 'false');
        this.textarea.setAttribute('aria-haspopup', 'listbox');

        this.handlers = {
            click: () => this.schedule(0),
            compositionend: () => {
                this.composing = false;
                this.schedule(0);
            },
            compositionstart: () => {
                this.composing = true;
                this.closeMenu();
            },
            input: () => {
                if (!this.composing) {
                    this.schedule();
                }
            },
            keydown: (event) => this.onKeydown(event),
            keyup: (event) => {
                if (selectionKeys.has(event.key) || (!this.open && verticalSelectionKeys.has(event.key))) {
                    this.schedule(0);
                }
            },
            pointerdown: (event) => {
                if (event.target !== this.textarea && !this.$refs.menu.contains(event.target)) {
                    this.closeMenu();
                }
            },
            reposition: () => this.positionMenu(),
            scroll: () => this.positionMenu(),
            submit: () => this.closeMenu(),
        };

        ['click', 'compositionend', 'compositionstart', 'input', 'keydown', 'keyup']
            .forEach((event) => this.textarea.addEventListener(event, this.handlers[event]));
        this.textarea.closest('form')?.addEventListener('submit', this.handlers.submit);
        this.textarea.addEventListener('scroll', this.handlers.scroll);
        document.addEventListener('pointerdown', this.handlers.pointerdown);
        window.addEventListener('resize', this.handlers.reposition);
        window.addEventListener('scroll', this.handlers.reposition, true);
        window.visualViewport?.addEventListener('resize', this.handlers.reposition);
        window.visualViewport?.addEventListener('scroll', this.handlers.reposition);
    },

    destroy() {
        window.clearTimeout(this.timer);
        this.requestSequence += 1;

        if (!(this.textarea instanceof HTMLTextAreaElement)) {
            return;
        }

        ['click', 'compositionend', 'compositionstart', 'input', 'keydown', 'keyup']
            .forEach((event) => this.textarea.removeEventListener(event, this.handlers[event]));
        this.textarea.closest('form')?.removeEventListener('submit', this.handlers.submit);
        this.textarea.removeEventListener('scroll', this.handlers.scroll);
        document.removeEventListener('pointerdown', this.handlers.pointerdown);
        window.removeEventListener('resize', this.handlers.reposition);
        window.removeEventListener('scroll', this.handlers.reposition, true);
        window.visualViewport?.removeEventListener('resize', this.handlers.reposition);
        window.visualViewport?.removeEventListener('scroll', this.handlers.reposition);
        this.closeMenu();
    },

    schedule(delay = 140) {
        window.clearTimeout(this.timer);

        if (!(this.textarea instanceof HTMLTextAreaElement)) {
            return;
        }

        const token = activeComposerToken(this.textarea);

        if (!token) {
            this.closeMenu();

            return;
        }

        this.token = token;
        this.failed = false;
        this.loading = true;
        this.suggestions = [];
        this.activeIndex = 0;
        this.openMenu();
        const request = ++this.requestSequence;
        this.timer = window.setTimeout(() => this.load(token, request), delay);
    },

    async load(token, request) {
        try {
            const suggestions = await wire.suggest(token.trigger, token.query);

            if (request !== this.requestSequence) {
                return;
            }

            this.suggestions = suggestions;
            this.failed = false;
            this.activeIndex = 0;
            this.confirmExactMention(token, suggestions);
        } catch (error) {
            if (request === this.requestSequence) {
                this.suggestions = [];
                this.failed = true;
                console.error('Unable to load composer suggestions.', error);
            }
        } finally {
            if (request === this.requestSequence) {
                this.loading = false;
                this.syncAria();
                this.$nextTick(() => window.requestAnimationFrame(() => this.positionMenu()));
            }
        }
    },

    onKeydown(event) {
        if (!this.open) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            this.closeMenu();

            return;
        }

        if (this.suggestions.length === 0) {
            return;
        }

        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            const movement = event.key === 'ArrowDown' ? 1 : -1;
            this.activeIndex = (this.activeIndex + movement + this.suggestions.length) % this.suggestions.length;
            this.syncAria();
            this.$nextTick(() => document.getElementById(this.optionId(this.activeIndex))?.scrollIntoView({ block: 'nearest' }));

            return;
        }

        if (event.key === 'Enter' || event.key === 'Tab') {
            event.preventDefault();
            this.choose(this.activeIndex);
        }
    },

    choose(index) {
        const suggestion = this.suggestions[index];

        if (!suggestion || !this.token || !(this.textarea instanceof HTMLTextAreaElement)) {
            return;
        }

        const nextCharacter = this.textarea.value.slice(this.token.end, this.token.end + 1);
        const spacer = nextCharacter === '' ? ' ' : '';
        const replacement = suggestion.replacement + spacer;
        const caret = this.token.start + replacement.length;

        this.textarea.setRangeText(replacement, this.token.start, this.token.end, 'end');
        this.textarea.setSelectionRange(caret, caret);
        this.textarea.dispatchEvent(new InputEvent('input', { bubbles: true, inputType: 'insertReplacementText' }));
        this.confirmMention(suggestion);
        this.closeMenu();
        this.textarea.focus({ preventScroll: true });
    },

    confirmExactMention(token, suggestions) {
        if (token.trigger !== '@') {
            return;
        }

        const expected = `@${token.query}`.toLowerCase();
        const suggestion = suggestions.find((candidate) => (
            candidate.type === 'mention'
            && candidate.replacement.toLowerCase() === expected
        ));

        if (suggestion) {
            this.confirmMention(suggestion);
        }
    },

    confirmMention(suggestion) {
        if (suggestion.type !== 'mention' || !(this.textarea instanceof HTMLTextAreaElement)) {
            return;
        }

        this.textarea.dispatchEvent(new CustomEvent('composer:mention-resolved', {
            detail: { handle: suggestion.replacement.slice(1).toLowerCase() },
        }));
    },

    openMenu() {
        if (!this.$refs.menu.matches(':popover-open')) {
            this.$refs.menu.showPopover();
        }

        this.open = true;
        this.syncAria();
        this.$nextTick(() => window.requestAnimationFrame(() => this.positionMenu()));
    },

    closeMenu() {
        window.clearTimeout(this.timer);
        this.requestSequence += 1;
        this.failed = false;
        this.loading = false;
        this.open = false;
        this.suggestions = [];
        this.token = null;

        if (this.$refs.menu?.matches(':popover-open')) {
            this.$refs.menu.hidePopover();
        }

        this.syncAria();
    },

    positionMenu() {
        if (!this.open || !this.token || !(this.textarea instanceof HTMLTextAreaElement)) {
            return;
        }

        positionComposerMenu(this.$refs.menu, this.textarea, this.token);
    },

    syncAria() {
        if (!(this.textarea instanceof HTMLTextAreaElement)) {
            return;
        }

        this.textarea.setAttribute('aria-expanded', this.open ? 'true' : 'false');

        if (this.open && this.suggestions[this.activeIndex]) {
            this.textarea.setAttribute('aria-activedescendant', this.optionId(this.activeIndex));
        } else {
            this.textarea.removeAttribute('aria-activedescendant');
        }
    },

    optionId(index) {
        return `composer-suggestion-${this.instanceId}-${index}`;
    },
});

document.addEventListener('alpine:init', () => {
    window.Alpine.data('composerAutocomplete', createAutocomplete);
});
