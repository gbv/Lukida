<template>
  <ol class="grid gap-4" aria-label="Treffer">
    <li v-for="(item, i) in results" :key="i">
      <article class="card bg-base-200 border border-base-300 cursor-pointer hover:bg-base-300 transition-colors"
               :aria-labelledby="'treffer-' + i" @click="$emit('open', item)">
        <div class="card-body py-4 gap-1">
          <div class="flex items-start justify-between gap-2">
            <!-- Titel als Knopf für die Tastatur, Karte für die Maus -->
            <h2 class="card-title text-base flex-1">
              <button :id="'treffer-' + i" type="button" class="text-left underline-offset-4 hover:underline"
                      @click.stop="$emit('open', item)">{{ getTitle(item) }}</button>
            </h2>
            <button type="button" class="btn btn-ghost btn-circle h-11 w-11 min-h-11 shrink-0 -mt-2 -mr-2"
                    :aria-pressed="isFav(item)"
                    :aria-label="'Favorit: ' + getTitle(item)"
                    :title="isFav(item) ? 'Aus Favoriten entfernen' : 'Zu Favoriten hinzufügen'"
                    @click.stop="toggleFav(item)">
              <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                   :fill="isFav(item) ? 'currentColor' : 'none'"
                   viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
              </svg>
            </button>
          </div>
          <p v-if="getAuthor(item)" class="text-sm">{{ getAuthor(item) }}</p>
          <p v-if="nebenzeile(item)" class="text-sm text-muted">{{ nebenzeile(item) }}</p>

          <!-- Exemplare: Ampel (mit Wort), Zeilen und Knopf aus den Regeln -->
          <div v-for="(ex, k) in exemplare(item)" :key="k"
               class="mt-2 pt-2 border-t border-base-300 flex items-start gap-2 text-sm">
            <span aria-hidden="true" class="mt-1.5 inline-block w-2.5 h-2.5 rounded-full shrink-0 border border-base-content"
                  :class="ampel(ex.light).farbe"></span>
            <div class="flex-1 min-w-0">
              <span class="sr-only">{{ ampel(ex.light).wort }}: </span>
              <div v-for="(z, zi) in zeilen(ex)" :key="zi" class="truncate">{{ z }}</div>
            </div>
            <button v-if="ex.short && ex.short.label" type="button"
                    class="btn btn-sm h-11 min-h-11 shrink-0"
                    :class="knopfFarbe(ex.short.color)"
                    @click.stop="hatAktion(ex) ? $emit('action', { item, ex }) : $emit('open', item)">
              {{ ex.short.label }}
            </button>
          </div>
        </div>
      </article>
    </li>
  </ol>
</template>

<script setup>
import { isFavorite, toggleFavorite } from '../favorites.js'

defineProps(['results'])
defineEmits(['open', 'action'])

// items[gruppe].groupitems[] als flache Liste
function exemplare(item) {
  const g = item && item.items
  if (!g || Array.isArray(g)) return []
  const aus = []
  for (const gruppe of Object.values(g))
    for (const ex of (gruppe.groupitems || [])) aus.push(ex)
  return aus
}

// Deutsche Zeilen aus comments.g, ohne Markdown-Sternchen
function zeilen(ex) {
  const c = ex && ex.comments && ex.comments.g
  if (!Array.isArray(c)) return []
  return c.map(x => String(x && x.text || '').replace(/\*\*/g, '').replace(/^:\s*/, '').trim())
          .filter(Boolean)
}

function nebenzeile(item) {
  const format = item.format && item.format !== 'unknown' ? item.format : ''
  return [item.year, format].filter(Boolean).join(' · ')
}

function ampel(licht) {
  if (licht === 'green')  return { farbe: 'bg-success', wort: 'Verfügbar' }
  if (licht === 'red')    return { farbe: 'bg-error',   wort: 'Nicht verfügbar' }
  if (licht === 'yellow' || licht === 'orange') return { farbe: 'bg-warning', wort: 'Eingeschränkt verfügbar' }
  return { farbe: 'bg-base-300', wort: 'Status' }
}
function knopfFarbe(farbe) {
  if (farbe === 'green') return 'btn-success btn-outline'
  if (farbe === 'red')   return 'btn-error btn-outline'
  return 'btn-outline'
}

// Ohne Aktion öffnet der Knopf den Titel
function hatAktion(ex) {
  const a = ex && ex.action
  return !!(a && !Array.isArray(a) && a.type)
}

function isFav(item) {
  return isFavorite(item.id)
}
function toggleFav(item) {
  toggleFavorite({
    id:     item.id,
    title:  getTitle(item),
    author: getAuthor(item),
    year:   item.year || '',
    area:   item.area || '',
    format: item.format || '',
  })
}

function getBibField(item, code) {
  const bib = item?.bibliographic?.groupitems
  if (!bib) return ''
  const entry = Object.values(bib).find(g => g.code === code)
  return entry?.items?.item_1?.text || ''
}
function getTitle(item)  { return getBibField(item, 'title') || '(kein Titel)' }
function getAuthor(item) { return getBibField(item, 'author') }
</script>
