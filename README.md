# Lukida Essential — Installationsanleitung

> Getestet auf esx-32.gbv.de (Debian/Ubuntu, Apache 2.4, PHP 8.4, PostgreSQL 16)  
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

> **Hinweis zu Pfaden:** Der Installationspfad ist frei wählbar. `configs/`, `logs/` und `caches/` müssen immer eine Ebene **über** dem Repo-Verzeichnis liegen.

---

## 1. Voraussetzungen

| Komponente | Version |
|---|---|
| PHP | 8.4 |
| PostgreSQL | 16 |
| Apache | 2.4 (mit mod_rewrite, mod_proxy, mod_proxy_http) |
| Node.js | 20+ (nur für UI-Build) |
| Git | beliebig |

---

## 2. Repository klonen

```bash
cd /home/<benutzer>
git clone https://github.com/Ali-afk-dot/lukida-essential lukida-essential
```

**Wichtig — Verzeichnisstruktur:** `configs/`, `logs/` und `caches/` müssen immer eine Ebene **über** dem Repo liegen. Die Namen sind fest vorgegeben.

---

## 3. Config-Verzeichnis anlegen

Der `configs/`-Ordner liegt bewusst außerhalb des Repos, damit Zugangsdaten nicht
versehentlich in die Versionskontrolle gelangen.

```bash
mkdir -p /home/<benutzer>/configs/production
```

### 3.1 `application.ini`

Datei: `/home/<benutzer>/configs/application.ini`

> Alle Werte in `<spitzen-klammern>` ersetzen.

**API-Key generieren** — einmalig ausführen, Ausgabe merken:

```bash
openssl rand -hex 32
```

Den generierten Key an **drei Stellen** eintragen:
1. `configs/application.ini` → `apikey = <ausgabe>`
2. `ui/src/components/Navbar.vue` → Zeile mit `apikey:`
3. `ui/src/components/Modal.vue` → Zeile mit `apikey:`
4. `ui/src/App.vue` → Zeile mit `apikey:`

Nach dem Eintragen in den Vue-Dateien: `npm run build` ausführen (siehe Abschnitt 6).

```ini
[routes]
POST /@api/@module/@action=@module->@action

[application]
name         = Lukida Essential
type         = production
apikey       = <32-stelliger-hex-api-key>
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

### 3.2 `production/database.ini`

Datei: `/home/<benutzer>/configs/production/database.ini`

> Alle Werte in `<spitzen-klammern>` ersetzen.

driver=pgsql
database=lukida_essential

[module]

[security]
app=token
user=none
```

### 3.3 `production/user.ini`

Datei: `/home/<benutzer>/configs/production/user.ini`

> Alle Werte in `<spitzen-klammern>` ersetzen. `appdebug` auf `false` lassen —

[db]
driver=pgsql
host=localhost
name=lukida_essential
user=lukida_essential
pass=<passwort>

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

## 4. Logs- und Caches-Verzeichnisse anlegen

Die API schreibt Logs und Caches in die Ordner `logs/` und `caches/` — jeweils
eine Ebene über dem Repo, also auf gleicher Ebene wie `configs/`. Apache läuft als
`www-data` und braucht Schreibrechte:

```bash
mkdir -p /home/<benutzer>/logs
mkdir -p /home/<benutzer>/caches
sudo chown -R www-data:www-data /home/<benutzer>/logs
sudo chown -R www-data:www-data /home/<benutzer>/caches
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

### 5.2 Platzhalter in demo-data.sql befüllen

Vor dem Einspielen von `demo-data.sql` müssen folgende Platzhalter mit echten
Werten befüllt werden:

| Platzhalter | Bedeutung | Woher |
|---|---|---|
| `<solr-host>` | Solr-Server-Hostname | Bei GBV beantragen |
| `<solr-path>` | Solr-Suchpfad | Bei GBV beantragen |
| `<iln>` | Interne Bibliotheksnummer (ILN) | Eigene Bibliothek / GBV |
| `<gbv-iln>` | ILN im GBV-Format, z. B. `GBV_ILN_123` | Eigene Bibliothek / GBV |
| `<isil>` | ISIL der Bibliothek, z. B. `DE-7` | Eigene Bibliothek |
| `<paia-test-url>` | URL des PAIA-Testsystems | Eigene Bibliothek |
| `<paia-prod-url>` | URL des PAIA-Produktivsystems | Eigene Bibliothek |
| `<paia-test-host>` | Hostname PAIA-Test (ohne `https://`) | Eigene Bibliothek |
| `<paia-prod-host>` | Hostname PAIA-Prod (ohne `https://`) | Eigene Bibliothek |
| `<testuser>` | Testnutzer-Ausweisnummer | Eigene Bibliothek |
| `<testpass>` | Testnutzer-Passwort | Eigene Bibliothek |

Den Solr-Zugang (Host + Pfad) stellt GBV auf Anfrage bereit.
PAIA-Zugangsdaten erhältst du von deinem lokalen Bibliothekssystem.

### 5.3 Schema und Demo-Daten einspielen

Die SQL-Dateien liegen im Repo unter `sql/`:
- `schema.sql` — erstellt alle Tabellen
- `demo-data.sql` — füllt die DB mit Beispieldaten

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
npm install
npm run build
```

Das erzeugt `ui/dist/` — das Verzeichnis, das Apache für die UI ausliefert.

**Hinweis:** In `ui/src/` muss der API-Key an zwei Stellen eingetragen werden
(gleicher Wert wie `apikey` in `application.ini`):

- `ui/src/components/Navbar.vue` — Zeile mit `apikey:`
- `ui/src/components/Modal.vue` — Zeile mit `apikey:`
- `ui/src/App.vue` — Zeile mit `apikey:`

Nach dem Eintragen erneut `npm run build` ausführen.

---

## 7. Apache konfigurieren

### 7.1 Apache-Module aktivieren

```bash
sudo a2enmod rewrite proxy proxy_http
sudo systemctl restart apache2
```

`mod_proxy` und `mod_proxy_http` sind zwingend erforderlich damit die UI
API-Requests weiterleiten kann.

### 7.2 VirtualHosts hinzufügen

In `/etc/apache2/sites-enabled/lukida.conf` anhängen (Ports anpassen falls belegt):

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
- Port **9056** — Vue-UI (statische Dateien). `ProxyPass /api ? 9055` leitet
  API-Requests weiter. Im Dev-Modus (`npm run dev`) macht das Vite selbst;
  im Build muss Apache es übernehmen.

### 7.3 Apache neu laden

```bash
sudo apache2ctl configtest
sudo systemctl reload apache2
```

---

## 8. Testen

| URL | Was |
|---|---|
| `http://<server>:9055/api/user/search?q=test` | API direkt (GET ? Fehlermeldung normal, POST erwartet) |
| `http://<server>:9056` | Vue-UI |

Im Browser Port 9056 öffnen ? Suchbegriff eingeben ? Treffer erscheinen.

---

## 9. Verzeichnisstruktur (Zusammenfassung)

```
/home/<benutzer>/
+-- lukida-essential/            ? Git-Repo (Clone von GitHub)
|   +-- 5/                       ? PHP-API (appl/ + base/)
|   +-- ui/                      ? Vue 3 Frontend
|   |   +-- dist/                ? Build-Output (nach npm run build)
|   +-- sql/                     ? schema.sql + demo-data.sql
|   +-- index.php
|   +-- main.php
|
+-- configs/                     ? Configs (AUSSERHALB des Repos, Name ist fest!)
|   +-- application.ini
|   +-- production/
|       +-- database.ini
|       +-- user.ini
|
+-- logs/                        ? Eigentümer: www-data
+-- caches/                      ? Eigentümer: www-data
```

---

## 10. Häufige Fehler

| Symptom | Ursache | Lösung |
|---|---|---|
| `configs directory missing!` | Configs-Ordner heißt nicht `configs` | Ordner muss exakt `configs` heißen, eine Ebene über dem Repo |
| `MODE` undefined / leere API-Antwort | `php -S` statt Apache | Apache VirtualHost mit `SetEnv` verwenden |
| 500 bei `/api/...` aus der UI | `mod_proxy_http` nicht aktiv | `sudo a2enmod proxy proxy_http` |
| `Failed to open stream: Keine Berechtigung` | logs/caches nicht www-data | `sudo chown -R www-data:www-data /home/<benutzer>/logs /home/<benutzer>/caches` |
| UI lädt, Suche liefert nichts | ProxyPass fehlt | `ProxyPass /api http://localhost:9055` im Port-9056-Block |
| `5/` leer nach git clone | Submodule-Problem | `git rm --cached 5`, `.git` in `5/` entfernen, neu hinzufügen |
