# Lukida Essential — Installationsanleitung

> Getestet mit Debian/Ubuntu, Apache 2.4, PHP 8.4 und 8.5, PostgreSQL 16 bis 18
> Stand: November 2026

Lukida Essential ist die kostenlose Open-Source-Version von Lukida Pro. Folgende Features sind nur in der Pro-Version enthalten:

- **Admin-Modul** — Verwaltungsoberfläche für Administratoren
- **Hosting-Modul** — Multi-Mandanten-Betrieb mit mehreren Datenbanken
- **Push-Nachrichten** — Benachrichtigungen nach dem Login
- **Wallet, Bausteine, Fragen** — Erweiterte Benutzer-Module
- **PDF-Export** — Drucken und Exportieren via MPDF
- **Klassifikationssysteme** — Suche in Normdaten und Klassifikationen
- **Server-seitige Favoriten/Suchen/Einstellungen** — In Essential werden diese nur lokal im Browser gespeichert
- **Vereinfachte Authentifizierung** — Essential verwendet API-Key-Auth statt JWT-Tokens
- **Brute-Force-Schutz bei der Anmeldung** — In Essential nicht enthalten

Was Essential mitbringt:

- **Barrierefreiheit** — Die Oberfläche ist auf WCAG 2.2 AA ausgelegt, beim Kontrast und bei
  den Zielgrößen auf AAA: Tastaturbedienung, sichtbarer Fokus, Ansagen für Bildschirmleser,
  Zielgrößen ab 44 px. Automatisch geprüft mit axe-core in allen Ansichten und Farbschemata.
- **20 Farbschemata** — darunter zwei mit hohem Kontrast. Jedes erreicht für Text mindestens 7:1.
  Ohne eigene Wahl richtet sich das Schema nach den Einstellungen des Systems (hell/dunkel,
  erhöhter Kontrast). Eigene Schemata: siehe [ui/README.md](ui/README.md).
- **Schonung des Suchindex** — Ein Solr-Index wird oft von vielen Bibliotheken geteilt. Essential
  stellt je Trefferseite eine Abfrage (plus eine für noch unbekannte übergeordnete Werke), fragt
  nur die nötigen Felder ab, beantwortet gleiche Abfragen aus dem Zwischenspeicher und begrenzt
  die Abfragen der ganzen Installation je Minute (siehe 3.2, Abschnitt `[api]`).

---

> **Hinweis zu Platzhaltern:** Alle Werte in `<spitzen-klammern>` sind Platzhalter und müssen vor der Inbetriebnahme durch eigene Werte ersetzt werden.

> **Hinweis zu Pfaden:** Der Installationspfad ist frei wählbar. `configs/`, `logs/`, `caches/` und `transfers/` müssen immer eine Ebene **über** dem Repo-Verzeichnis liegen. Die Namen sind fest vorgegeben.

---

## 1. Voraussetzungen

| Komponente | Version |
|---|---|
| PHP | 8.4 oder neuer (mit curl, pdo_pgsql, mbstring) |
| PostgreSQL | 16 oder neuer |
| Apache | 2.4 (mit mod_rewrite, mod_proxy, mod_proxy_http) |
| Node.js | 20+ (nur für den UI-Build) |
| Git | beliebig |

---

## 2. Repository klonen

```bash
cd /home/<benutzer>
git clone https://github.com/gbv/Lukida.git lukida-essential
```

---

## 3. Configs anlegen

Der `configs/`-Ordner liegt bewusst außerhalb des Repos, damit Zugangsdaten nicht
versehentlich in die Versionskontrolle gelangen. Vorlagen liegen im Repo unter
`configs.example/`:

```bash
cp -r /home/<benutzer>/lukida-essential/configs.example /home/<benutzer>/configs
```

### 3.1 API-Key erzeugen

Einmalig ausführen, Ausgabe merken:

```bash
openssl rand -hex 32
```

Der Key wird an **zwei Stellen** eingetragen:

1. `configs/application.ini` → `apikey = <ausgabe>` (siehe 3.2)
2. `ui/.env.local` → `VITE_API_KEY=<ausgabe>` (siehe Abschnitt 6)

> Der API-Key ist im ausgelieferten JavaScript der UI sichtbar. Er trennt die
> eigene UI von fremden Aufrufern, ist aber kein Geheimnis gegenüber Endnutzern.

### 3.2 `application.ini`

Datei: `/home/<benutzer>/configs/application.ini`

```ini
[routes]
POST /@api/@module/@action=@module->@action

[application]
name         = Lukida Essential
type         = production
apikey       = <64-stelliger-hex-api-key>
path         = /home/<benutzer>/lukida-essential/5/
versionmain  = 5
startyear    = <startjahr>

[author]
name         = <name>
mail         = <email>
company      = <organisation>
homepage     = <homepage>
pepper       = lukida-essential-2026

[api]
indexcache   = 600
indexlimit   = 60
```

Fehler (z. B. der Datenbank oder des Suchindex) schreibt die API nach `logs/`.

**Abschnitt `[api]` — Schonung des Suchindex:**

- `indexcache` — Sekunden, die eine gleiche Abfrage aus dem Zwischenspeicher beantwortet wird
  (`0` schaltet ihn ab). Gleiche Suchen verschiedener Nutzer kosten den Index dann nichts.
- `indexlimit` — Abfragen der ganzen Installation je Minute. Viele Indexbetreiber begrenzen die
  Abfragen je Absender; den Wert bitte deutlich unter dieser Grenze halten. Ist das Kontingent
  erschöpft, antwortet die API mit `indexbusy`, und die Oberfläche bittet um etwas Geduld.
  Eine Suche kostet höchstens zwei Abfragen; Blättern in der Vollanzeige und Wiederholungen
  kosten keine.

### 3.3 `production/user.ini`

Datei: `/home/<benutzer>/configs/production/user.ini`

Enthält Datenbankzugang und Debug-Schalter.
`appdebug` auf `false` lassen — sonst schreibt die API SQL-Abfragen und -Daten ins Log.

```ini
[db]
driver=pgsql
host=localhost
port=5432
name=lukida_essential
user=lukida_essential
pass=<passwort>

[appdebug]
sqq=false
sqd=false
```

---

## 4. Logs-, Caches- und Transfers-Verzeichnisse anlegen

Die API schreibt Logs, Caches und Protokolldateien in `logs/`, `caches/` und
`transfers/` — jeweils eine Ebene über dem Repo, also auf gleicher Ebene wie
`configs/`. Apache läuft als `www-data` und braucht Schreibrechte:

```bash
mkdir -p /home/<benutzer>/logs /home/<benutzer>/caches /home/<benutzer>/transfers
sudo chown -R www-data:www-data /home/<benutzer>/logs /home/<benutzer>/caches /home/<benutzer>/transfers
```

`caches/` legt je Tag einen Ordner an (`caches/<JJJJMMTT>/`). Alte Tage lassen sich gefahrlos
löschen, etwa nachts per Cron (als `root` oder `www-data`):

```bash
find /home/<benutzer>/caches -maxdepth 1 -type d -name '20*' -mtime +7 -exec rm -rf {} +
```

Der Ordner `caches/ids/` hält die vollständigen Datensätze; er spart dem Index die meisten
Abfragen und sollte nur geleert werden, wenn Platz fehlt.

---

## 5. Datenbank anlegen

### 5.1 DB und User anlegen

```bash
sudo -u postgres psql
```

```sql
CREATE USER lukida_essential WITH PASSWORD '<passwort>';
CREATE DATABASE lukida_essential OWNER lukida_essential;
\q
```

Das Passwort muss mit `pass` in `user.ini` übereinstimmen.

### 5.2 Platzhalter in `demo-data.sql` befüllen

Vor dem Einspielen von `sql/demo-data.sql` müssen folgende Platzhalter mit echten
Werten befüllt werden:

| Platzhalter | Bedeutung | Woher |
|---|---|---|
| `<solr-host>` | Hostname des Solr-Index | Betreiber des Index |
| `<solr-path>` | Suchpfad im Solr-Index | Betreiber des Index |
| `<iln>` | Interne Bibliotheksnummer (ILN) | Eigene Bibliothek |
| `<sammlung>` | Sammlungskennung der Bibliothek im Index (Feld `collection_details`) | Betreiber des Index |
| `<isil>` | ISIL der Bibliothek, z. B. `DE-123` | Eigene Bibliothek |
| `<paia-test-url>` | URL des PAIA-Testsystems | Eigene Bibliothek |
| `<paia-prod-url>` | URL des PAIA-Produktivsystems | Eigene Bibliothek |
| `<paia-test-host>` | Hostname PAIA-Test (ohne `https://`) | Eigene Bibliothek |
| `<paia-prod-host>` | Hostname PAIA-Prod (ohne `https://`) | Eigene Bibliothek |
| `<testuser>` | Testnutzer-Ausweisnummer | Eigene Bibliothek |
| `<testpass>` | Testnutzer-Passwort | Eigene Bibliothek |

Den Solr-Zugang (Host + Pfad) stellt der Betreiber des Index bereit. Ob er funktioniert, lässt sich vorab prüfen:

```bash
curl 'https://<solr-host>/<solr-path>/select?q=test&rows=1&wt=json'
```

Eine Antwort mit `"response"` bedeutet: Zugang ok. `403 Forbidden` bedeutet: kein Zugriff für diese Adresse.
PAIA-Zugangsdaten erhältst du von deinem lokalen Bibliothekssystem.

### 5.3 Schema und Demo-Daten einspielen

Die SQL-Dateien liegen im Repo unter `sql/`:

- `schema.sql` — erstellt alle Tabellen
- `demo-data.sql` — füllt die DB mit Beispieldaten für den Client `Demo`

**Wichtig:** Beide Dateien als DB-User der Anwendung einspielen (wie unten), **nicht** als
`postgres`. Sonst gehören die Tabellen `postgres`, und die API darf nicht darauf zugreifen
(`permission denied`).

```bash
psql -h localhost -U lukida_essential -d lukida_essential \
  -f /home/<benutzer>/lukida-essential/sql/schema.sql
psql -h localhost -U lukida_essential -d lukida_essential \
  -f /home/<benutzer>/lukida-essential/sql/demo-data.sql
```

---

## 6. Vue-UI bauen

```bash
cd /home/<benutzer>/lukida-essential/ui
cp .env.example .env.local
```

In `ui/.env.local` den API-Key aus Abschnitt 3.1 eintragen (gleicher Wert wie
`apikey` in `application.ini`):

```ini
VITE_API_KEY=<64-stelliger-hex-api-key>
```

`.env.local` wird nicht versioniert.

### 6.1 Name und Client anpassen

Name und Client der Oberfläche stehen in `ui/.env`:

```ini
VITE_APP_TITLE=Lukida Essentials Demo
VITE_API_CLIENT=Demo
```

- `VITE_APP_TITLE` — erscheint im Browser-Titel, in der Kopfzeile, auf der Startseite und
  auf der Infoseite. Einfach durch den eigenen Namen ersetzen, z. B. `Bibliothek Musterstadt`.
- `VITE_API_CLIENT` — muss mit der Spalte `client` in der Datenbank übereinstimmen
  (in `sql/demo-data.sql` ist das `Demo`).

Eigene Werte können auch in `ui/.env.local` stehen. Diese Datei hat Vorrang und wird nicht versioniert.

### 6.2 Bauen


```bash
npm install
npm run build
```

Das erzeugt `ui/dist/` — das Verzeichnis, das Apache für die UI ausliefert.
Nach jeder Änderung an `.env` oder `.env.local` muss `npm run build` erneut ausgeführt werden.

Für die Entwicklung startet `npm run dev` einen Vite-Server, der `/api` an
`http://localhost:9055` weiterleitet.

---

## 7. Apache konfigurieren

### 7.1 Apache-Module aktivieren

```bash
sudo a2enmod rewrite proxy proxy_http
sudo systemctl restart apache2
```

`mod_proxy` und `mod_proxy_http` sind zwingend erforderlich, damit die UI
API-Requests weiterleiten kann.

### 7.2 VirtualHosts hinzufügen

Datei `/etc/apache2/sites-available/lukida.conf` anlegen (Ports anpassen, falls belegt)
und mit `sudo a2ensite lukida` aktivieren:

```apache
Listen 9055
<VirtualHost *:9055>
    ServerName    "LukidaEssential"
    SetEnv MODULE "user"
    SetEnv CLIENT "Essential"
    SetEnv MODE   "production"
    DocumentRoot  "/home/<benutzer>/lukida-essential/"
    <Directory    "/home/<benutzer>/lukida-essential/">
        DirectoryIndex index.php
        AllowOverride All
        Require all granted
        RewriteEngine On
        RewriteBase /
        RewriteRule ^\.htaccess$ - [F]
        RewriteRule ^.*\.ini$ - [F]
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_URI} !robots.txt
        RewriteRule ^(.*)$ index.php?/$1 [L]
    </Directory>
</VirtualHost>

Listen 9056
<VirtualHost *:9056>
    ServerName    "LukidaEssentialUI"
    DocumentRoot  "/home/<benutzer>/lukida-essential/ui/dist/"
    <Directory    "/home/<benutzer>/lukida-essential/ui/dist/">
        Options -Indexes +FollowSymLinks
        AllowOverride None
        Require all granted
        FallbackResource /index.html
    </Directory>
    ProxyPass        /api http://localhost:9055
    ProxyPassReverse /api http://localhost:9055
</VirtualHost>
```

**Warum zwei VirtualHosts?**

- Port **9055** — PHP-API. `SetEnv` setzt MODE/MODULE/CLIENT — funktioniert nur
  über Apache, nicht mit `php -S`.
- Port **9056** — Vue-UI (statische Dateien). `ProxyPass /api` → 9055 leitet
  API-Requests weiter. Im Dev-Modus (`npm run dev`) übernimmt das Vite,
  im Build muss Apache es übernehmen.

Statt zweier Ports können auch zwei Hostnamen auf Port 80 genutzt werden
(z. B. `lukida-api.example` und `lukida.example`). Dann muss `ProxyPass` auf den
API-Hostnamen zeigen, nicht auf einen Port:

```apache
    ProxyPass        /api http://lukida-api.example
    ProxyPassReverse /api http://lukida-api.example
```

### 7.3 Apache neu laden

```bash
sudo apache2ctl configtest
sudo systemctl reload apache2
```

---

## 8. Testen

Die API nimmt nur POST-Requests mit JSON an. Die URL hat die Form
`/<api-version>/<modul>/<aktion>`, also z. B. `/5/user/search`:

```bash
curl -X POST http://<server>:9055/5/user/search \
  -H 'Content-Type: application/json' \
  -d '{"apikey":"<api-key>","client":"Demo","appl":"production","area":"catalog",
       "search":{"query":"test","type":"lukida"},"display":{"bib":true},"start":0,"limit":5}'
```

| URL | Erwartung |
|---|---|
| `http://<server>:9055/` (GET im Browser) | Begrüßungsseite der API |
| `POST http://<server>:9055/5/user/search` | JSON mit `"code":"dataloaded"` und Treffern |
| `http://<server>:9056` | Vue-UI — Suchbegriff eingeben → Treffer erscheinen |

---

## 9. Verzeichnisstruktur (Zusammenfassung)

```
/home/<benutzer>/
├── lukida-essential/            ← Git-Repo (Clone von GitHub)
│   ├── 5/                       ← PHP-API (appl/ + base/)
│   ├── configs.example/         ← Vorlagen für configs/
│   ├── sql/                     ← schema.sql + demo-data.sql
│   ├── system/                  ← Fat-Free Framework (nur Kern + SQL)
│   ├── ui/                      ← Vue-3-Frontend
│   │   ├── scripts/kontrast.mjs ← prüft die Farbschemata auf AAA
│   │   ├── .env                 ← Name und Client der Oberfläche
│   │   ├── .env.local           ← API-Key für den Build (nicht versioniert)
│   │   └── dist/                ← Build-Output (nach npm run build)
│   ├── index.php
│   └── main.php
│
├── configs/                     ← Configs (außerhalb des Repos, Name ist fest!)
│   ├── application.ini
│   └── production/
│       └── user.ini
│
├── logs/                        ← Eigentümer: www-data
├── caches/                      ← Eigentümer: www-data
└── transfers/                   ← Eigentümer: www-data
```

---

## 10. Häufige Fehler

| Symptom | Ursache | Lösung |
|---|---|---|
| `configs directory missing!` | Configs-Ordner heißt nicht `configs` oder liegt falsch | Ordner muss exakt `configs` heißen, eine Ebene über dem Repo |
| `MODE` undefined / leere API-Antwort | `php -S` statt Apache | Apache-VirtualHost mit `SetEnv` verwenden |
| `{"code":"apikeyinvalid"}` | Key in UI und `application.ini` verschieden | `VITE_API_KEY` in `ui/.env.local` prüfen, danach `npm run build` |
| `{"code":"localdatabaseerror"}` | DB nicht erreichbar oder Login falsch | `[db]` in `user.ini` mit DB-User/Passwort aus 5.1 abgleichen |
| Log: `permission denied for schema/table user` | Schema als `postgres` eingespielt | Tabellen dem DB-User übertragen: `ALTER TABLE "user".<tabelle> OWNER TO lukida_essential` (je Tabelle, als `postgres`) |
| `{"code":"levelmissing"}` | Keine Level-Konfiguration für den Client | Demo-Daten einspielen bzw. Eintrag `all` in `"user".adminlevels` anlegen; danach `caches/` leeren |
| 500 bei `/api/...` aus der UI | `mod_proxy_http` nicht aktiv | `sudo a2enmod proxy proxy_http` |
| `Failed to open stream: Keine Berechtigung` | logs/caches/transfers gehören nicht www-data | `sudo chown -R www-data:www-data` auf die drei Ordner (siehe 4.) |
| UI lädt, Suche liefert nichts | ProxyPass fehlt | `ProxyPass /api http://localhost:9055` im Port-9056-Block |
| `{"code":"indexbusy"}` | Kontingent `indexlimit` je Minute erschöpft | Kurz warten; dauerhaft: `indexcache` erhöhen, `indexlimit` nur unterhalb der Grenze des Indexbetreibers anheben |
| `{"code":"Action not found!"}` | Aufruf eines Einstiegs, den Essential nicht anbietet | Erlaubt sind `search`, `login`, `action`, `export` |
| Log: `403 Forbidden` vom Index | Kein Zugang für diese Adresse oder Sperre wegen zu vieler Abfragen | Zugang beim Indexbetreiber klären; `indexlimit` prüfen |
