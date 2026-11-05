# Lukida Essential — UI

Vue-3-Oberfläche (Vite, Tailwind CSS, daisyUI) für die Lukida-Essential-API.

```bash
cp .env.example .env.local   # VITE_API_KEY = apikey aus configs/application.ini
npm install
npm run dev                  # Entwicklung, /api wird an http://localhost:9055 weitergeleitet
npm run build                # Produktion, erzeugt dist/
```

Name und Client der Oberfläche: `VITE_APP_TITLE` und `VITE_API_CLIENT` in `.env`.

## Farbschemata

Die Schemata stehen in `src/style.css` (Farben) und `src/themes.js` (Name in der Auswahl,
Einordnung unter Hell oder Dunkel). Jedes ist auf WCAG 2.2 AAA ausgelegt: Text mindestens 7:1
gegen jeden Hintergrund, auf dem er vorkommt. Nach jeder Änderung prüfen:

```bash
node scripts/kontrast.mjs
```

Das Skript nennt jedes Farbpaar unter der Grenze und endet dann mit Rückgabewert 1.

Installation und Apache-Konfiguration: siehe [README im Hauptverzeichnis](../README.md).
