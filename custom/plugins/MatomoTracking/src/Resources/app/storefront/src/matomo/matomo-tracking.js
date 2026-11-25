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