<template>
  <div class="container mx-auto px-4 py-8 max-w-5xl">
    <div class="flex items-center justify-between gap-2 mb-6">
      <h2 class="text-xl font-bold">Favoriten</h2>
      <div v-if="favorites.length" class="flex items-center gap-2">
        <ExportMenu :ids="favorites.map(f => f.id)" :up="false" />
        <button class="btn btn-sm btn-ghost opacity-50" @click="clearAll">Alle entfernen</button>
      </div>
    </div>
    <div v-if="favorites.length === 0" class="text-center opacity-40 py-16">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
      <p>Noch keine Favoriten gespeichert.</p>
      <p class="text-sm mt-1">Klicken Sie auf den Stern bei einem Treffer, um ihn zu merken.</p>
    </div>
    <div v-else class="divide-y divide-base-200">
      <div v-for="f in favorites" :key="f.id"
           class="flex items-start gap-4 py-3 px-2 rounded hover:bg-base-200 cursor-pointer group"
           @click="$emit('open', f)">
        <div class="shrink-0 w-10 h-14 bg-base-300 rounded flex items-center justify-center opacity-40">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13M12 6.253C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        <div class="flex-1 min-w-0">
          <div class="font-medium text-sm leading-snug line-clamp-2">{{ f.title }}</div>
          <div class="text-xs opacity-50 mt-0.5">
            <span v-if="f.author">{{ f.author }}</span>
            <span v-if="f.year"> · {{ f.year }}</span>
            <span v-if="f.format && f.format !== 'unknown'"> · {{ f.format }}</span>
          </div>
        </div>
        <button class="btn btn-xs btn-ghost opacity-40 group-hover:opacity-80 shrink-0"
                title="Aus Favoriten entfernen" @click.stop="removeFavorite(f.id)">✕</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { favorites, removeFavorite, clearFavorites } from '../favorites.js'
import ExportMenu from './ExportMenu.vue'

defineEmits(['open'])

function clearAll() {
  if (confirm('Alle Favoriten entfernen?')) clearFavorites()
}
</script>
