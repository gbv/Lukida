<template>
  <div class="container mx-auto px-4 py-8 max-w-5xl">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-bold">Suchverlauf</h2>
      <button v-if="history.length" class="btn btn-sm btn-ghost opacity-50" @click="clearAll">Alle löschen</button>
    </div>
    <div v-if="history.length === 0" class="text-center opacity-40 py-16">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <p>Noch keine Suchanfragen gespeichert.</p>
    </div>
    <div v-else class="space-y-1">
      <div v-for="(h, i) in history" :key="i"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-base-200 cursor-pointer group"
           @click="$emit('search', h.q)">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-30 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <span class="flex-1 text-sm">{{ h.q }}</span>
        <span class="text-xs opacity-30 shrink-0">{{ formatDate(h.ts) }}</span>
        <button class="btn btn-xs btn-ghost opacity-0 group-hover:opacity-50 shrink-0"
                @click.stop="remove(i)">?</button>
      </div>
    </div>
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