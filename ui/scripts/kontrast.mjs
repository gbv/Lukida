// Prüft die Schemata in src/style.css auf 7:1 (WCAG AAA); Rückgabewert 1 bei Fehlern
import { readFileSync } from 'node:fs'

const css = readFileSync(new URL('../src/style.css', import.meta.url), 'utf8')
const themes = [...css.matchAll(/@plugin "daisyui\/theme" \{([\s\S]*?)\n\}/g)].map(([, body]) => {
  const t = { name: body.match(/name:\s*"([^"]+)"/)[1] }
  for (const [, k, v] of body.matchAll(/--([a-z0-9-]+):\s*(#[0-9a-f]{6})/gi)) t[k.replace(/^color-/, '')] = v
  return t
})

const lin = (c) => { c /= 255; return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4 }
const lum = (hex) => { const n = parseInt(hex.slice(1), 16); return 0.2126 * lin(n >> 16) + 0.7152 * lin((n >> 8) & 255) + 0.0722 * lin(n & 255) }
const ratio = (a, b) => { const [x, y] = [lum(a), lum(b)].sort((p, q) => q - p); return (x + 0.05) / (y + 0.05) }

const STATUS = ['primary', 'secondary', 'accent', 'neutral', 'info', 'success', 'warning', 'error']
let fehler = 0
for (const t of themes) {
  const paare = []
  for (const bg of ['base-100', 'base-200', 'base-300']) {
    paare.push(['base-content', bg, 7], ['muted', bg, 7])
  }
  for (const s of STATUS) {
    paare.push([s + '-content', s, 7])                       // Text auf farbiger Fläche
    paare.push([s, 'base-100', 7], [s, 'base-200', 7])         // farbiger Text, Umrandungen
  }
  const schlecht = paare.map(([v, h, min]) => [v, h, min, ratio(t[v], t[h])]).filter(([, , min, r]) => r < min)
  const kleinster = Math.min(...paare.map(([v, h]) => ratio(t[v], t[h])))
  console.log((schlecht.length ? 'FEHLER ' : 'ok     ') + t.name.padEnd(16) + ' kleinster Kontrast ' + kleinster.toFixed(2) + ':1')
  for (const [v, h, min, r] of schlecht) console.log('         ' + v + ' auf ' + h + ': ' + r.toFixed(2) + ' (verlangt ' + min + ')')
  fehler += schlecht.length
}
if (!themes.length) { console.log('Keine Schemata gefunden - die Messung hat nichts gesehen.'); process.exit(1) }
process.exit(fehler ? 1 : 0)
