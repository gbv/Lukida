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
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps(['results'])
defineEmits(['open'])

// Reaktiver Zähler damit computed neu ausgewertet wird nach toggleFav
const favTick = ref(0)

function loadFavs() {
  try { return JSON.parse(localStorage.getItem('lukida_favs') || '[]') } catch { return [] }
}
function saveFavs(arr) {
  try { localStorage.setItem('lukida_favs', JSON.stringify(arr)) } catch {}
}

function isFav(item) {
  favTick.value // dependency
  return loadFavs().some(f => f.id === item.id)
}

function toggleFav(item) {
  const favs = loadFavs()
  const idx  = favs.findIndex(f => f.id === item.id)
  if (idx >= 0) {
    favs.splice(idx, 1)
  } else {
    favs.unshift({
      id:      item.id,
      title:   getTitle(item),
      author:  getAuthor(item),
      year:    item.year || '',
      area:    item.area || '',
      format:  item.format || '',
      savedAt: Date.now(),
    })
  }
  saveFavs(favs)
  favTick.value++
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