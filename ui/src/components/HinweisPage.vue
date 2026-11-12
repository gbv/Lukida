<template>
  <div class="min-h-screen flex items-center justify-center bg-base-100">
    <div class="max-w-lg text-center px-6">
      <h1 class="text-2xl font-semibold mb-4">Bibliothek nicht gefunden</h1>
      <p class="text-base-content/70 mb-6">
        Für die angegebene Bibliotheks-ID
        (<code class="font-mono bg-base-200 px-1 rounded">{{ isil }}</code>)
        ist kein Katalog eingerichtet.
      </p>
      <p class="text-base-content/70 text-sm">
        Bitte wenden Sie sich an Ihren Bibliotheksverbund.
      </p>
    </div>
  </div>
</template>

<script setup>
import { CLIENT } from '../api.js'
const isil = CLIENT
</script>
