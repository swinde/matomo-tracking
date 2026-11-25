# Changelog

Alle wesentlichen Änderungen an diesem Projekt werden in dieser Datei
dokumentiert.

Das Format orientiert sich an [Keep a
Changelog](https://keepachangelog.com/de/1.1.0/). Solange keine
veröffentlichte Plugin-Version festgelegt ist, werden Änderungen unter
`Unreleased` gesammelt.

## \[Unreleased\]

### Added

-   Einbindung einer selbst gehosteten Matomo-Instanz über `matomo.js`
    und `matomo.php`.
-   Konfigurationsmöglichkeiten für Aktivierung, Matomo-Basis-URL und
    Site-ID.
-   Shopware-Cookie-Einwilligung für Matomo über das Cookie
    `matomo-tracking`.
-   Reaktion auf Zustimmung und Widerruf ohne erforderlichen
    Seitenreload.
-   Erfassung von Produktansichten mit Matomo `setEcommerceView`.
-   Warenkorbtracking über das Shopware-Event `offCanvasOpened`.
-   Übermittlung der Warenkorbpositionen mit `addEcommerceItem`.
-   Übermittlung des Warenkorbwerts mit `trackEcommerceCartUpdate`.

### Changed

-   Warenkorbpositionen werden bei jeder Warenkorbaktualisierung
    vollständig neu an Matomo übermittelt.
-   Der Warenkorbwert wird aus Einzelpreis und Menge berechnet;
    Versandkosten werden nicht eingerechnet.

### Tested

-   Produktansicht wird nach erteilter Einwilligung erfasst.
-   Hinzufügen eines Artikels zum Warenkorb.
-   Gemeinsame Übermittlung mehrerer Warenkorbpositionen.
-   Aktualisierung von Artikelmengen.
-   Entfernung von Artikeln aus dem Warenkorb.
-   Keine weiteren Matomo-Trackingrequests nach Widerruf der
    Einwilligung.

### Not yet implemented

-   Bestelltracking mit `trackEcommerceOrder`.
-   Übermittlung von Bestellnummer, Umsatz, Versandkosten, Steuern und
    Bestellpositionen.
-   Schutz vor doppelter Erfassung einer Bestellung.
-   Abschließende Prüfung der Kategorieübermittlung im Warenkorb.
