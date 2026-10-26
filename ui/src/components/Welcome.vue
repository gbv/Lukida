<template>
  <div class="hero min-h-64 mt-8">
    <div class="hero-content text-center">
      <div>
        <h1 class="text-4xl font-bold text-primary mb-4">{{ APP_TITLE }}</h1>
        <p class="text-lg opacity-70 mb-2">Open-Source Bibliotheks-Discovery</p>
        <p class="opacity-50">Suche nach Titeln, Autoren oder ISBN</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { APP_TITLE } from '../api.js'
</script>
