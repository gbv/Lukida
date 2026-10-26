<template>
  <div class="flex gap-2 my-8">
    <input
      v-model="query"
      type="text"
      placeholder="Titel, Autor, ISBN suchen..."
      class="input input-bordered flex-1 text-lg"
      @keyup.enter="$emit('search', query)"
    />
    <button class="btn btn-primary" :disabled="loading" @click="$emit('search', query)">
      <span v-if="loading" class="loading loading-spinner loading-sm"></span>
      <span v-else>Suchen</span>
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
defineProps(['loading'])
defineEmits(['search'])
const query = ref('')
</script>
