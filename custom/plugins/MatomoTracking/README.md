# MatomoTracking für Shopware 6

Shopware-Storefront-Plugin zur datenschutzbewussten Einbindung einer
selbst gehosteten Matomo-Instanz.

## Projektstatus

**In Entwicklung.** Die Einbindung und das Tracking von Produktansichten
sowie Warenkorbänderungen wurden in einer Shopware-6.7.15.1-Umgebung
getestet. Das Bestelltracking ist noch nicht implementiert.

## Funktionen

### Bereits umgesetzt und getestet

-   Einbindung einer selbst gehosteten Matomo-Instanz über `matomo.js`
    und `matomo.php`.
-   Konfiguration der Matomo-URL und der Site-ID über die
    Plugin-Konfiguration.
-   Aktivierung über die Plugin-Einstellung `active`.
-   Einwilligungsabhängiges Tracking über die
    Shopware-Cookie-Einwilligung.
-   Verwendung des Cookies `matomo-tracking`.
-   Sofortige Reaktion auf Änderungen der Cookie-Einwilligung:
    -   Bei Zustimmung wird Matomo aktiviert und ein Seitenaufruf
        erfasst.
    -   Bei Widerruf wird `forgetConsentGiven` an Matomo übermittelt.
-   Erfassung von Produktansichten über `setEcommerceView`.
-   Erfassung des aktuellen Warenkorbs über das Shopware-Event
    `offCanvasOpened`.
-   Warenkorbpositionen werden vor jeder Aktualisierung vollständig neu
    an Matomo übermittelt. Dadurch werden hinzugefügte, geänderte und
    entfernte Positionen berücksichtigt.

### Warenkorbtracking

Für die Warenkorbübermittlung werden die Metadaten aus der HTML-Antwort
des Offcanvas-Warenkorbs gelesen. Die getesteten Daten umfassen:

-   Produktnummer / SKU
-   Produktname
-   Einzelpreis
-   Menge
-   Warenkorbwert

Matomo erhält die Positionen über `addEcommerceItem` und den
Warenkorbwert über `trackEcommerceCartUpdate`.

Der Warenkorbwert wird aus Einzelpreis × Menge berechnet. Versandkosten
werden dabei nicht zum Warenkorbwert addiert.

**Hinweis:** Die Kategorie wird aus den Offcanvas-Metadaten gelesen. In
den bisher geprüften Matomo-Requests war dieses Feld leer; die
Übermittlung der Kategorie sollte daher bei Bedarf separat überprüft
werden.

## Noch offen

-   Bestelltracking über Matomo `trackEcommerceOrder`.
-   Übermittlung von Bestellnummer, Bestellsumme, Versandkosten, Steuern
    und Bestellpositionen.
-   Schutz vor doppelter Übermittlung einer Bestellung, beispielsweise
    bei erneutem Aufruf der Bestellabschlussseite.
-   Abschließende Tests im Live-Shop.

Matomo kann später dabei helfen, Warenkörbe ohne zugehörige Bestellung
auszuwerten. Das Plugin versendet selbst keine Erinnerungen und führt
keine automatische Warenkorbrückgewinnung durch.

## Voraussetzungen

-   Shopware 6.7.x (getestet mit Shopware 6.7.15.1)
-   Eine erreichbare Matomo-Installation
-   Eine in Matomo angelegte Website mit gültiger Site-ID
-   Shopware-Storefront mit aktivierter Cookie-Einwilligung

## Installation

1.  Plugin-Verzeichnis `MatomoTracking` nach `custom/plugins/` kopieren.
2.  Plugin installieren und aktivieren.
3.  Im Shopware-Admin die Plugin-Konfiguration öffnen.
4.  Matomo-URL und Site-ID eintragen und das Tracking aktivieren.
5.  Die Matomo-Cookie-Gruppe in der Shopware-Cookie-Einwilligung prüfen.
6.  Storefront-Assets neu bauen und Cache leeren.

Beispielbefehle für eine DDEV-Umgebung:

``` bash
ddev exec bin/console plugin:refresh
ddev exec bin/console plugin:install --activate MatomoTracking
ddev exec bin/console cache:clear
ddev exec bin/console theme:compile
```

Falls das Plugin bereits installiert ist, ist `plugin:install` nicht
erneut erforderlich.

## Konfiguration

Die bisher verwendeten Konfigurationsschlüssel sind:

  ----------------------------------------------------------------------------
  Einstellung                              Bedeutung
  ---------------------------------------- -----------------------------------
  `MatomoTracking.config.active`           Aktiviert oder deaktiviert das
                                           Tracking

  `MatomoTracking.config.baseUrl`          Basis-URL der Matomo-Installation

  `MatomoTracking.config.siteId`           Matomo-Site-ID

  `MatomoTracking.config.trackEcommerce`   Aktiviert die E-Commerce-Funktionen
  ----------------------------------------------------------------------------

Beispiel:

-   Matomo-URL: `https://matomo.example.de/`
-   Site-ID: `1`

Die URL muss auf die eigene Matomo-Installation zeigen. Die
Beispielwerte sind durch die tatsächlichen Installationsdaten zu
ersetzen.

## Datenschutz und Einwilligung

Das Tracking ist an die Shopware-Cookie-Einwilligung gebunden. Ohne
Zustimmung sollen keine Matomo-Trackingdaten übertragen werden. Bei
Widerruf wird Matomos `forgetConsentGiven` aufgerufen.

Bitte vor dem produktiven Einsatz die konkrete Cookie-Konfiguration, die
Datenschutzerklärung und die datenschutzrechtlichen Anforderungen des
eigenen Shops prüfen.

## Entwicklung und Tests

Bisher erfolgreich geprüfte Szenarien:

-   Matomo wird nach Zustimmung geladen.
-   Produktdetailseiten werden mit `setEcommerceView` erfasst.
-   Ein Artikel wird zum Warenkorb hinzugefügt.
-   Mehrere Artikel werden gemeinsam übertragen.
-   Eine Artikelmenge wird geändert.
-   Ein Artikel wird entfernt und ist anschließend nicht mehr im
    Matomo-Warenkorb enthalten.
-   Nach Widerruf der Einwilligung werden keine weiteren
    Matomo-Trackingrequests ausgelöst.

Die Tests wurden lokal mit DDEV durchgeführt. Vor einem Live-Einsatz
sind die Szenarien auch in der Produktionsumgebung zu prüfen.

## Bekannte Einschränkungen

-   Das Bestelltracking ist noch nicht Bestandteil des aktuellen
    Entwicklungsstands.
-   Die Kategorie wurde in den bisher geprüften Warenkorb-Requests leer
    übertragen.
-   Die Erkennung nicht abgeschlossener Warenkörbe erfolgt später über
    die Matomo-Auswertung und ist keine automatische Wiederherstellungs-
    oder Erinnerungsfunktion.

## Lizenz

Bitte die für dieses Plugin vorgesehene Lizenz ergänzen.
