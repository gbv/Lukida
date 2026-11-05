// Zentrale Einstellungen (aus ui/.env bzw. ui/.env.local) und Zugriff auf die Lukida-API

export const APP_TITLE = import.meta.env.VITE_APP_TITLE || 'Lukida Essential'
export const CLIENT    = import.meta.env.VITE_API_CLIENT || 'Demo'

const BASE = {
  apikey: import.meta.env.VITE_API_KEY,
  client: CLIENT,
  appl:   'production',
}

export async function api(action, params = {}) {
  const res = await fetch('/api/5/user/' + action, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({ ...BASE, ...params }),
  })
  return res.json()
}

// -- Export --------------------------------------------------------------------
export const EXPORT_FORMATS = [
  { code: 'bibtex',  label: 'BibTeX',                ext: 'bib' },
  { code: 'ris',     label: 'RIS (Citavi, Zotero)',  ext: 'ris' },
  { code: 'endnote', label: 'EndNote',               ext: 'enw' },
]

export async function exportRecords(ids, format) {
  const data = await api('export', { area: 'export', format: format.code, ids })
  if (typeof data.export !== 'string' || !data.export) throw new Error(data.code || 'exporterror')

  const url = URL.createObjectURL(new Blob([data.export], { type: 'text/plain;charset=utf-8' }))
  const a   = document.createElement('a')
  a.href     = url
  a.download = 'lukida-export.' + format.ext
  document.body.appendChild(a)
  a.click()
  a.remove()
  setTimeout(() => URL.revokeObjectURL(url), 1000)
}
