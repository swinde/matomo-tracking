
# Changelog

Alle wesentlichen Änderungen an diesem Projekt werden in dieser Datei dokumentiert.

Das Format orientiert sich an [Keep a Changelog](https://keepachangelog.com/de/1.1.0/).

## [1.0.1] - 2026-11-26

### Added

- Deutsche und englische Sprachsnippets für die Matomo-Cookie-Gruppe.
- Konfigurationsoption zur Anonymisierung von IP-Adressen.
- Übergabe der IP-Anonymisierungseinstellung an Matomo.
- Bestelltracking mit Matomo `trackEcommerceOrder`.
- Übermittlung von Bestellnummer, Umsatz, Versandkosten, Steuern und Bestellpositionen.
- Schutz vor doppelter Bestellübertragung mittels LocalStorage.
- Erfassung von E-Commerce-Bestellungen und Produkten.

### Changed

- IP-Anonymisierung ist standardmäßig aktiviert.
- Warenkorbpositionen werden bei jeder Warenkorbaktualisierung vollständig neu an Matomo übermittelt.
- Der Warenkorbwert wird aus Einzelpreis und Menge berechnet; Versandkosten werden nicht eingerechnet.

### Tested

- Matomo wird erst nach erteilter Cookie-Einwilligung geladen.
- Zustimmung und Widerruf der Cookie-Einwilligung.
- Produktansichten mit `setEcommerceView`.
- Hinzufügen, Ändern und Entfernen von Warenkorbpositionen.
- Übermittlung mehrerer Warenkorbpositionen.
- Übermittlung von Bestellungen einschließlich Bestellpositionen.
- Schutz vor doppelter Bestellübertragung.
- IP-Anonymisierung: Übergabe der aktivierten und deaktivierten Einstellung an JavaScript.
- Erkennung von UTM-Kampagnen in Matomo.
- Unterschiedliche Besucherprofile in verschiedenen Browsern.

### Known Limitations

- Die Kategorieübermittlung bei Warenkorb- und Bestellpositionen sollte weiter geprüft werden.
- Die abschließenden Tests im Live-Shop stehen noch aus.

---

## [Unreleased]
