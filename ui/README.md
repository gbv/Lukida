# Lukida Essential — UI

Vue-3-Oberfläche (Vite, Tailwind CSS, daisyUI) für die Lukida-Essential-API.

```bash
cp .env.example .env.local   # VITE_API_KEY = apikey aus configs/application.ini
npm install
npm run dev                  # Entwicklung, /api wird an http://localhost:9055 weitergeleitet
npm run build                # Produktion, erzeugt dist/
```

Name und Client der Oberfläche: `VITE_APP_TITLE` und `VITE_API_CLIENT` in `.env`.

Installation und Apache-Konfiguration: siehe [README im Hauptverzeichnis](../README.md).
