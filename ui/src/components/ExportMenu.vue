<template>
  <details ref="menu" class="dropdown dropdown-end" :class="{ 'dropdown-top': up }">
    <summary class="btn btn-sm btn-outline gap-1" :class="{ 'btn-disabled': !ids.length || busy }">
      <span v-if="busy" class="loading loading-spinner loading-xs"></span>
      <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
      Export
    </summary>
    <ul class="dropdown-content menu menu-sm bg-base-100 rounded-box shadow z-[70] w-52 p-1 my-1">
      <li v-for="f in EXPORT_FORMATS" :key="f.code"><a @click.prevent="run(f)">{{ f.label }}</a></li>
    </ul>
  </details>
  <div v-if="error" class="text-error text-xs">{{ error }}</div>
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
