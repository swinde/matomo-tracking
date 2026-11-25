import {
    COOKIE_CONFIGURATION_UPDATE
} from 'src/plugin/cookie/cookie-configuration.plugin';

const COOKIE_NAME = 'matomo-tracking';

function readCookie(name) {
    const prefix = `${encodeURIComponent(name)}=`;

    const entry = document.cookie
        .split('; ')
        .find(item => item.startsWith(prefix));

    if (!entry) {
        return null;
    }

    return decodeURIComponent(entry.substring(prefix.length));
}

function bootMatomo(element) {
    const baseUrl = element.dataset.matomoUrl;
    const siteId = element.dataset.matomoSiteId;

    if (!baseUrl || !siteId) {
        return;
    }

    const trackerBase = `${baseUrl.replace(/\/+$/, '')}/`;

    let scriptRequested = false;
    let matomoReady = false;
    let consentGranted = false;

    function initializeMatomo() {
        window._paq = window._paq || [];

        window._paq.push(['requireConsent']);
        window._paq.push([
            'setTrackerUrl',
            `${trackerBase}matomo.php`
        ]);
        window._paq.push(['setSiteId', String(siteId)]);
        window._paq.push(['enableLinkTracking']);

        const script = document.createElement('script');

        script.async = true;
        script.src = `${trackerBase}matomo.js`;

        script.onload = () => {
            matomoReady = true;

            // Nur tracken, wenn aktuell eine Zustimmung vorliegt.
            if (consentGranted) {
                activateTracking();
            }
        };

        document.head.appendChild(script);
        scriptRequested = true;
    }


    function activateTracking() {
        if (
            element.dataset.matomoEcommerce === 'true'
            && element.dataset.matomoProductSku
        ) {
            const productPrice = element.dataset.matomoProductPrice;

            window._paq.push([
                'setEcommerceView',
                element.dataset.matomoProductSku,
                element.dataset.matomoProductName || false,
                element.dataset.matomoProductCategory || false,
                productPrice !== undefined && productPrice !== ''
                    ? Number(productPrice)
                    : false
            ]);
        }

        window._paq.push(['rememberConsentGiven']);
        window._paq.push(['trackPageView']);
    }


    function grantTracking() {
        if (consentGranted) {
            return;
        }

        consentGranted = true;

        if (!scriptRequested) {
            initializeMatomo();
            return;
        }

        // Matomo ist bereits geladen.
        if (matomoReady) {
            activateTracking();
        }
    }

    function revokeTracking() {

        if (!consentGranted) {
            return;
        }

        consentGranted = false;

        if (window._paq) {
            window._paq.push(['forgetConsentGiven']);
        }
    }

    function trackCartFromOffcanvas(response) {
        // Ohne Einwilligung niemals Warenkorbdaten übertragen.
        if (!consentGranted || !matomoReady || !window._paq) {
            return;
        }

        if (typeof response !== 'string') {
            return;
        }

        const parser = new DOMParser();
        const doc = parser.parseFromString(response, 'text/html');

        const metadata = doc.querySelector(
            '.hidden-line-items-information'
        );

        if (!metadata) {
            return;
        }

        const items = [...metadata.querySelectorAll('.hidden-line-item')]
            .map(item => ({
                sku: item.dataset.sku,
                name: item.dataset.name || '',
                category: item.dataset.category1 || '',
                price: Number(item.dataset.price),
                quantity: Number(item.dataset.quantity)
            }))
            .filter(item =>
                item.sku
                && Number.isFinite(item.price)
                && Number.isFinite(item.quantity)
                && item.quantity > 0
            );

        // Alten Warenkorb im Matomo-Tracker zurücksetzen.
        window._paq.push(['clearEcommerceCart']);

        let cartTotal = 0;

        items.forEach(item => {
            window._paq.push([
                'addEcommerceItem',
                item.sku,
                item.name,
                item.category,
                item.price,
                item.quantity
            ]);

            cartTotal += item.price * item.quantity;
        });

        // Warenkorbwert ohne Versandkosten übermitteln.
        window._paq.push([
            'trackEcommerceCartUpdate',
            cartTotal
        ]);
    }

    // Offcanvas-Listener erst nach Initialisierung der Storefront registrieren.
    function registerCartTracking() {
        const offCanvasCart = window.PluginManager
            ?.getPluginInstances('OffCanvasCart')?.[0];

        if (!offCanvasCart?.$emitter) {
            console.warn('Matomo: OffCanvasCart nicht gefunden.');
            return;
        }

        offCanvasCart.$emitter.subscribe(
            'offCanvasOpened',
            event => {
                console.log('Matomo: Offcanvas-Event empfangen');

                trackCartFromOffcanvas(event.detail?.response);
            }
        );

        console.log('Matomo: Offcanvas-Listener registriert');
    }

    if (document.readyState === 'complete') {
        registerCartTracking();
    } else {
        window.addEventListener('load', registerCartTracking, {
            once: true
        });
    }

    // Bereits vorhandene Zustimmung prüfen.
    if (readCookie(COOKIE_NAME) === '1') {
        grantTracking();
    }

    // Änderungen der Shopware-Cookie-Einstellungen beobachten.
    if (document.$emitter) {
        document.$emitter.subscribe(
            COOKIE_CONFIGURATION_UPDATE,
            event => {

                if (
                    !Object.prototype.hasOwnProperty.call(
                        event.detail || {},
                        COOKIE_NAME
                    )
                ) {
                    return;
                }

                const enabled = event.detail[COOKIE_NAME];

                if (enabled) {
                    grantTracking();
                } else {
                    revokeTracking();
                }
            }
        );
    }
}

document
    .querySelectorAll('[data-matomo-tracking]')
    .forEach(bootMatomo);