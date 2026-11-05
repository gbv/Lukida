<template>
  <form role="search" class="flex flex-wrap gap-2 my-8" @submit.prevent="$emit('search', query)">
    <label for="suchbegriff" class="sr-only">Suchbegriff</label>
    <input
      id="suchbegriff"
      v-model="query"
      type="search"
      placeholder="Titel, Autor, ISBN suchen …"
      class="input flex-1 min-w-48 h-12 text-lg placeholder:text-muted"
      autocomplete="off"
      enterkeyhint="search"
    />
    <button type="submit" class="btn btn-primary h-12 min-h-12 px-6" :disabled="loading">
      <span v-if="loading" class="loading loading-spinner loading-sm" aria-hidden="true"></span>
      Suchen
    </button>
  </form>
</template>

<script setup>
import { ref } from 'vue'
defineProps(['loading'])
defineEmits(['search'])
const query = ref('')
</script>
