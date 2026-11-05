<template>
  <details ref="menu" class="dropdown dropdown-end" :class="{ 'dropdown-top': up }" @keydown.esc.stop="schliessen">
    <summary class="btn btn-outline h-11 min-h-11 gap-1" :class="{ 'btn-disabled': !ids.length || busy }"
             :aria-disabled="!ids.length || busy || undefined">
      <span v-if="busy" class="loading loading-spinner loading-xs" aria-hidden="true"></span>
      <svg v-else aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
      Export
    </summary>
    <ul class="dropdown-content menu bg-base-100 border border-base-300 rounded-box shadow z-[70] w-60 p-1 my-1">
      <li v-for="f in EXPORT_FORMATS" :key="f.code">
        <button type="button" class="min-h-11" @click="run(f)">{{ f.label }}</button>
      </li>
    </ul>
  </details>
  <p v-if="error" role="alert" class="text-error text-sm">{{ error }}</p>
</template>

<script setup>
import { ref } from 'vue'
import { EXPORT_FORMATS, exportRecords } from '../api.js'

const props = defineProps({
  ids: { type: Array,   required: true },
  up:  { type: Boolean, default: true },
})
const menu  = ref(null)
const busy  = ref(false)
const error = ref('')

// Escape schließt nur das Menü, nicht den Dialog darum herum.
function schliessen() {
  if (!menu.value?.open) return
  menu.value.open = false
  menu.value.querySelector('summary')?.focus()
}

async function run(format) {
  menu.value.open = false
  error.value     = ''
  busy.value      = true
  try {
    await exportRecords(props.ids, format)
  } catch (e) {
    error.value = 'Export fehlgeschlagen.'
    console.error(e)
  } finally {
    busy.value = false
  }
}
</script>
