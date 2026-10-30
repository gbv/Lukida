<template>
  <div class="grid gap-4">
    <div
      v-for="(item, i) in results"
      :key="i"
      class="card bg-base-200 shadow cursor-pointer hover:bg-base-300 transition-colors"
      @click="$emit('open', item)"
    >
      <div class="card-body py-4">
        <div class="flex items-start justify-between gap-2">
          <h2 class="card-title text-base flex-1">{{ getTitle(item) }}</h2>
          <button class="btn btn-xs btn-circle btn-ghost shrink-0 mt-0.5"
                  :title="isFav(item) ? 'Aus Favoriten entfernen' : 'Zu Favoriten hinzufügen'"
                  @click.stop="toggleFav(item)">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                 :fill="isFav(item) ? 'currentColor' : 'none'"
                 :class="isFav(item) ? 'text-warning' : 'opacity-40'"
                 viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
          </button>
        </div>
        <p class="text-sm opacity-70">{{ getAuthor(item) }}</p>
        <p class="text-xs opacity-50">{{ item.year || '' }}{{ item.year && item.format && item.format !== 'unknown' ? ' · ' : '' }}{{ item.format !== 'unknown' ? item.format || '' : '' }}</p>

        <!-- EXEMPLARE AUS DEN REGELN                              (29.10.2026)
             Welche Zeilen und welcher Knopf hier stehen, entscheidet die API
             anhand von admincases/admincasesmedia. Wir zeigen nur an, was sie
             liefert: die Ampel (light), die Zeilen (comments) und den Knopf
             (short mit Code, Beschriftung, Zeichen und Farbe). -->
        <div v-for="(ex, k) in exemplare(item)" :key="k"
             class="mt-2 pt-2 border-t border-base-300 flex items-start gap-2 text-sm">
          <span class="mt-1.5 inline-block w-2 h-2 rounded-full shrink-0"
                :class="ampel(ex.light)" :title="ex.light || ''"></span>
          <div class="flex-1 min-w-0">
            <div v-for="(z, zi) in zeilen(ex)" :key="zi" class="opacity-80 truncate">{{ z }}</div>
          </div>
          <!-- Der Knopf zeigt, was die Regel vorsieht. Eine eigene Aktion hat
               er in der Trefferliste nur selten - die Bestellung selbst
               haengt an der Vollanzeige. Damit er nicht tot wirkt, oeffnet
               er dann den Titel, wie ein Klick auf die Karte. -->
          <button v-if="ex.short && ex.short.label"
                  class="btn btn-xs shrink-0"
                  :class="knopfFarbe(ex.short.color)"
                  :title="ex.meta && ex.meta.case ? ex.meta.case : ''"
                  @click.stop="hatAktion(ex) ? $emit('action', { item, ex }) : $emit('open', item)">
            {{ ex.short.label }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { isFavorite, toggleFavorite } from '../favorites.js'

defineProps(['results'])
defineEmits(['open', 'action'])

// Die API liefert die Exemplare als items[gruppe].groupitems[]. Wir ziehen
// sie flach, damit die Karte sie einfach untereinander zeigen kann. Ist
// "items" leer oder ein leeres Array (Titel ohne Bestand), kommt nichts.
function exemplare(item) {
  const g = item && item.items
  if (!g || Array.isArray(g)) return []
  const aus = []
  for (const gruppe of Object.values(g))
    for (const ex of (gruppe.groupitems || [])) aus.push(ex)
  return aus
}

// Die Zeilen stehen sprachgetrennt unter comments.g (deutsch) als Liste von
// {type, text}. Leere Eintraege und die Markdown-Sternchen der Signatur
// nehmen wir heraus - die Karte ist kein Fliesstext.
function zeilen(ex) {
  const c = ex && ex.comments && ex.comments.g
  if (!Array.isArray(c)) return []
  return c.map(x => String(x && x.text || '').replace(/\*\*/g, '').replace(/^:\s*/, '').trim())
          .filter(Boolean)
}

// Die Ampel kommt als Farbwort vom Server; mehr als diese vier Zustaende
// gibt es in Essential nicht.
function ampel(licht) {
  if (licht === 'green')  return 'bg-success'
  if (licht === 'red')    return 'bg-error'
  if (licht === 'yellow' || licht === 'orange') return 'bg-warning'
  return 'bg-base-content opacity-30'
}
function knopfFarbe(farbe) {
  if (farbe === 'green') return 'btn-success btn-outline'
  if (farbe === 'red')   return 'btn-error btn-outline'
  return 'btn-outline'
}

// Nur ein Knopf mit hinterlegter Aktion ist anklickbar. Steht dort nichts
// (reine Zustandsanzeige wie "Vor Ort nutzbar"), bleibt er als Beschriftung
// stehen und ist abgeschaltet.
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
