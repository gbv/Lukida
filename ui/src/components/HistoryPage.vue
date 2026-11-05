<template>
  <div class="container mx-auto px-4 py-8 max-w-5xl">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-6">
      <h1 class="text-2xl font-bold">Suchverlauf</h1>
      <button v-if="history.length" type="button" class="btn btn-ghost h-11 min-h-11" @click="clearAll">Alle löschen</button>
    </div>
    <div v-if="history.length === 0" class="text-center py-16">
      <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <p>Noch keine Suchanfragen gespeichert.</p>
    </div>
    <ul v-else class="space-y-1">
      <li v-for="(h, i) in history" :key="i" class="flex items-center gap-2">
        <button type="button" class="flex flex-1 min-w-0 items-center gap-3 px-3 py-2.5 min-h-11 rounded-lg text-left hover:bg-base-200"
                @click="$emit('search', h.q)">
          <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-muted shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <span class="flex-1">{{ h.q }}</span>
          <span class="text-sm text-muted shrink-0"><span class="sr-only">gesucht am </span>{{ formatDate(h.ts) }}</span>
        </button>
        <button type="button" class="btn btn-ghost btn-circle h-11 w-11 min-h-11 shrink-0"
                :aria-label="'Aus dem Verlauf entfernen: ' + h.q" title="Aus dem Verlauf entfernen" @click="remove(i)">
          <span aria-hidden="true">✕</span>
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
defineEmits(['search'])

const history = ref([])
function load() {
  try { return JSON.parse(localStorage.getItem('lukida_history') || '[]') } catch { return [] }
}
function save(arr) {
  try { localStorage.setItem('lukida_history', JSON.stringify(arr.slice(0, 50))) } catch {}
  history.value = arr.slice(0, 50)
}
onMounted(() => { history.value = load() })

function remove(i) {
  const h = load(); h.splice(i, 1); save(h)
}
function clearAll() {
  if (confirm('Suchverlauf löschen?')) save([])
}
function formatDate(ts) {
  if (!ts) return ''
  return new Date(ts).toLocaleDateString('de-DE', { day:'2-digit', month:'2-digit', year:'2-digit', hour:'2-digit', minute:'2-digit' })
}
</script>
