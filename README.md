# Lukida Essential — Installationsanleitung

> Getestet mit Debian/Ubuntu, Apache 2.4, PHP 8.4, PostgreSQL 16
> Stand: Oktober 2026

Lukida Essential ist die kostenlose Open-Source-Version von Lukida Pro. Folgende Features sind nur in der Pro-Version enthalten:

- **Admin-Modul** — Verwaltungsoberfläche für Administratoren
- **Hosting-Modul** — Multi-Mandanten-Betrieb mit mehreren Datenbanken
- **Push-Nachrichten** — Benachrichtigungen nach dem Login
- **Wallet, Bausteine, Fragen** — Erweiterte Benutzer-Module
- **PDF-Export** — Drucken und Exportieren via MPDF
- **Klassifikationssysteme** — Suche in Normdaten und Klassifikationen
- **Server-seitige Favoriten/Suchen/Einstellungen** — In Essential werden diese nur lokal im Browser gespeichert
- **Vereinfachte Authentifizierung** — Essential verwendet API-Key-Auth statt JWT-Tokens
- **Rate-Limiting / Brute-Force-Schutz** — In Essential nicht enthalten

---

> **Hinweis zu Platzhaltern:** Alle Werte in `<spitzen-klammern>` sind Platzhalter und müssen vor der Inbetriebnahme durch eigene Werte ersetzt werden.

> **Hinweis zu Pfaden:** Der Installationspfad ist frei wählbar. `configs/`, `logs/`, `caches/` und `transfers/` müssen immer eine Ebene **über** dem Repo-Verzeichnis liegen. Die Namen sind fest vorgegeben.

---

## 1. Voraussetzungen

| Komponente | Version |
|---|---|
| PHP | 8.4 |
| PostgreSQL | 16 |
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
```

An `author.mail` gehen Fehlermeldungen der API (z. B. Datenbank- oder Solr-Fehler).

### 3.3 `production/user.ini`

Datei: `/home/<benutzer>/configs/production/user.ini`

Enthält Datenbankzugang, Sicherheitseinstellungen, Mailversand und Debug-Schalter.
`appdebug` auf `false` lassen — sonst schreibt die API SQL-Abfragen und -Daten ins Log.

```ini
[db]
driver=pgsql
host=localhost
name=lukida_essential
user=lukida_essential
pass=<passwort>

[security]
app=token
user=none

[smtp]
host=
auth=false
user=
pass=
port=587
from=<absender-email>
fromname=Lukida Essential

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

Das Passwort muss mit `pass` in `user.ini` übereinstimmen. Die Namen `lukida_essential` für
DB und User sind auch in `sql/schema.sql` (Rechtevergabe) hinterlegt — bei anderen Namen
dort ebenfalls anpassen.

### 5.2 Platzhalter in `demo-data.sql` befüllen

Vor dem Einspielen von `sql/demo-data.sql` müssen folgende Platzhalter mit echten
Werten befüllt werden:

| Platzhalter | Bedeutung | Woher |
|---|---|---|
| `<solr-host>` | Solr-Server-Hostname, in der Regel `findex.gbv.de` | GBV |
| `<solr-path>` | Solr-Suchpfad, z. B. `index/<iln>` | Bei GBV beantragen |
| `<iln>` | Interne Bibliotheksnummer (ILN) | Eigene Bibliothek / GBV |
| `<gbv-iln>` | ILN im GBV-Format, z. B. `GBV_ILN_123` | Eigene Bibliothek / GBV |
| `<isil>` | ISIL der Bibliothek, z. B. `DE-7` | Eigene Bibliothek |
| `<paia-test-url>` | URL des PAIA-Testsystems | Eigene Bibliothek |
| `<paia-prod-url>` | URL des PAIA-Produktivsystems | Eigene Bibliothek |
| `<paia-test-host>` | Hostname PAIA-Test (ohne `https://`) | Eigene Bibliothek |
| `<paia-prod-host>` | Hostname PAIA-Prod (ohne `https://`) | Eigene Bibliothek |
| `<testuser>` | Testnutzer-Ausweisnummer | Eigene Bibliothek |
| `<testpass>` | Testnutzer-Passwort | Eigene Bibliothek |

Den Solr-Zugang (Host + Pfad) stellt GBV auf Anfrage bereit. Ob er funktioniert, lässt sich vorab prüfen:

```bash
curl 'https://<solr-host>/<solr-path>/select?q=test&rows=1&wt=json'
```

Eine Antwort mit `"response"` bedeutet: Zugang ok. `403 Forbidden` bedeutet: kein Zugriff für diese Adresse.
PAIA-Zugangsdaten erhältst du von deinem lokalen Bibliothekssystem.

### 5.3 Schema und Demo-Daten einspielen

Die SQL-Dateien liegen im Repo unter `sql/`:

- `schema.sql` — erstellt alle Tabellen
- `demo-data.sql` — füllt die DB mit Beispieldaten für den Client `Demo`

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

`.env.local` wird nicht versioniert. Anschließend bauen:

```bash
npm install
npm run build
```

Das erzeugt `ui/dist/` — das Verzeichnis, das Apache für die UI ausliefert.
Nach jeder Änderung des Keys muss `npm run build` erneut ausgeführt werden.

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
  -d '{"apikey":"<api-key>","client":"Demo","appl":"production","area":"findex",
       "search":{"query":"test"},"display":{"bib":true},"start":0,"limit":5}'
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
│   ├── system/                  ← Fat-Free Framework
│   ├── ui/                      ← Vue-3-Frontend
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
| 500 bei `/api/...` aus der UI | `mod_proxy_http` nicht aktiv | `sudo a2enmod proxy proxy_http` |
| `Failed to open stream: Keine Berechtigung` | logs/caches/transfers gehören nicht www-data | `sudo chown -R www-data:www-data` auf die drei Ordner (siehe 4.) |
| UI lädt, Suche liefert nichts | ProxyPass fehlt | `ProxyPass /api http://localhost:9055` im Port-9056-Block |
