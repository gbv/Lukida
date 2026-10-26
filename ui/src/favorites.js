// Favoriten: ein gemeinsamer, reaktiver Speicher für alle Komponenten (lokal im Browser)
import { ref } from 'vue'

const KEY = 'lukida_favs'

function load() {
  try { return JSON.parse(localStorage.getItem(KEY) || '[]') } catch { return [] }
}
function save() {
  try { localStorage.setItem(KEY, JSON.stringify(favorites.value)) } catch {}
}

export const favorites = ref(load())

export function isFavorite(id) {
  return favorites.value.some(f => f.id === id)
}
export function toggleFavorite(entry) {
  const i = favorites.value.findIndex(f => f.id === entry.id)
  if (i >= 0) favorites.value.splice(i, 1)
  else        favorites.value.unshift({ ...entry, savedAt: Date.now() })
  save()
}
export function removeFavorite(id) {
  favorites.value = favorites.value.filter(f => f.id !== id)
  save()
}
export function clearFavorites() {
  favorites.value = []
  save()
}
