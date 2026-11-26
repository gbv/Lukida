# Changelog

## [Unreleased]

### Added
- Hinweisseite (`HinweisPage`) für unbekannte ISIL — neutrale Infoseite mit Link zur GBV-Kontaktseite statt leerem UI
- Neuer API-Endpunkt `config` in `user.php` — liefert Theme und Titel aus der DB, erkennt unbekannte Clients anhand fehlender DB-Einträge (`item`/`ui`)
- README: `config`-Endpunkt in Fehlertabelle dokumentiert

### Changed
- Theme wird jetzt per URL-Parameter (`?theme=`) übergeben und aus der DB geladen, nicht mehr aus `localStorage`
- `App.vue`: `onMounted` ruft `config`-Endpunkt ab und zeigt `HinweisPage` bei `unknown_isil`
- Mandantenfähigkeit: ISIL per URL-Parameter (`?isil=`), Theme und Titel pro Client aus DB
- `ThemeWahl`-Switcher entfernt (Theme wird serverseitig gesetzt)

### Fixed
- `empty($this->configs)` griff nie für unbekannte Clients — Fix: Prüfung auf DB-spezifische Schlüssel (`item`, `ui`)
- Apache VHost bereinigt: ungenutzte Ports (9055–9062) entfernt, `lukida-essential.conf` und `lukida.conf.bak` deaktiviert und gelöscht

### Infrastructure
- VHost-Pfade auf `/home/ali/lukida/` aktualisiert (war: `lukida-light`)
- Solr-10-Kompatibilitätstest gestartet (`solr10test_lukida`-Index)
