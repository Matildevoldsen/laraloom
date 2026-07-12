const indicatorSelector = '[data-notification-indicator]';
const faviconSelector = 'link[rel~="icon"]';
const originalTitle = document.title;
const favicons = Array.from(document.querySelectorAll(faviconSelector))
    .filter((favicon) => favicon instanceof HTMLLinkElement)
    .map((favicon) => ({ favicon, href: favicon.href }));
let renderedCount = null;

const renderFavicon = (count) => {
    if (count === renderedCount) {
        return;
    }

    renderedCount = count;
    document.title = count > 0 ? `(${count > 99 ? '99+' : count}) ${originalTitle}` : originalTitle;

    if (count === 0) {
        favicons.forEach(({ favicon, href }) => {
            favicon.href = href;
        });

        return;
    }

    const source = new Image();
    source.addEventListener('load', () => {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');

        if (!context) {
            return;
        }

        canvas.width = 64;
        canvas.height = 64;
        context.drawImage(source, 0, 0, 64, 64);
        context.beginPath();
        context.arc(51, 13, 11, 0, Math.PI * 2);
        context.fillStyle = '#ff4d73';
        context.fill();
        context.lineWidth = 4;
        context.strokeStyle = '#ffffff';
        context.stroke();

        const badgedFavicon = canvas.toDataURL('image/png');
        favicons.forEach(({ favicon }) => {
            favicon.href = badgedFavicon;
        });
    }, { once: true });
    source.src = '/sourcefolk-mark.svg';
};

const refreshNotificationFavicon = () => {
    const indicator = document.querySelector(indicatorSelector);
    const count = indicator instanceof HTMLElement
        ? Number.parseInt(indicator.dataset.unreadCount ?? '0', 10)
        : 0;

    renderFavicon(Number.isNaN(count) ? 0 : Math.max(0, count));
};

refreshNotificationFavicon();

new MutationObserver(refreshNotificationFavicon).observe(document.documentElement, {
    attributeFilter: ['data-unread-count'],
    attributes: true,
    childList: true,
    subtree: true,
});
