// Auswahlliste; die Farben stehen in style.css (prüfen: node scripts/kontrast.mjs)

export const THEMES = [
  { id: 'hell',            name: 'Hell',            art: 'hell' },
  { id: 'kontrast-hell',   name: 'Kontrast hell',   art: 'hell' },
  { id: 'nord',            name: 'Nord',            art: 'hell' },
  { id: 'herbst',          name: 'Herbst',          art: 'hell' },
  { id: 'wald',            name: 'Wald',            art: 'hell' },
  { id: 'sand',            name: 'Sand',            art: 'hell' },
  { id: 'meer',            name: 'Meer',            art: 'hell' },
  { id: 'lavendel',        name: 'Lavendel',        art: 'hell' },
  { id: 'rose',            name: 'Rose',            art: 'hell' },
  { id: 'salbei',          name: 'Salbei',          art: 'hell' },
  { id: 'papier',          name: 'Papier',          art: 'hell' },
  { id: 'dunkel',          name: 'Dunkel',          art: 'dunkel' },
  { id: 'kontrast-dunkel', name: 'Kontrast dunkel', art: 'dunkel' },
  { id: 'kaffee',          name: 'Kaffee',          art: 'dunkel' },
  { id: 'nacht',           name: 'Nacht',           art: 'dunkel' },
  { id: 'tanne',           name: 'Tanne',           art: 'dunkel' },
  { id: 'aubergine',       name: 'Aubergine',       art: 'dunkel' },
  { id: 'graphit',         name: 'Graphit',         art: 'dunkel' },
  { id: 'tiefsee',         name: 'Tiefsee',         art: 'dunkel' },
  { id: 'glut',            name: 'Glut',            art: 'dunkel' },
]

const KEY = 'lukida_theme'

// Gespeicherte Wahl, sonst nach Systemeinstellung
export function startTheme() {
  const mq = (q) => window.matchMedia && window.matchMedia(q).matches
  const dunkel = mq('(prefers-color-scheme: dark)')
  if (mq('(prefers-contrast: more)')) return dunkel ? 'kontrast-dunkel' : 'kontrast-hell'
  return dunkel ? 'dunkel' : 'hell'
}

export function setzeTheme(id) {
  document.documentElement.setAttribute('data-theme', id)
}
