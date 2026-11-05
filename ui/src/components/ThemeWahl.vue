<template>
  <div class="flex items-center gap-2">
    <label for="farbschema" class="text-sm font-medium whitespace-nowrap">Farbschema</label>
    <div class="relative flex items-center">
      <!-- Vorschau des gewählten Schemas: Grund, Schrift und Hauptfarbe -->
      <span aria-hidden="true"
            class="pointer-events-none absolute left-3 z-10 flex h-5 w-5 overflow-hidden rounded-full border border-base-content">
        <span class="h-full w-1/2 bg-base-100"></span>
        <span class="h-full w-1/2 bg-primary"></span>
      </span>
      <select id="farbschema" class="select select-sm h-11 min-h-11 w-44 pl-10 text-sm"
              :value="modelValue" @change="$emit('update:modelValue', $event.target.value)">
        <optgroup label="Hell">
          <option v-for="t in THEMES.filter(x => x.art === 'hell')" :key="t.id" :value="t.id">{{ t.name }}</option>
        </optgroup>
        <optgroup label="Dunkel">
          <option v-for="t in THEMES.filter(x => x.art === 'dunkel')" :key="t.id" :value="t.id">{{ t.name }}</option>
        </optgroup>
      </select>
    </div>
  </div>
</template>

<script setup>
import { THEMES } from '../themes.js'

defineProps(['modelValue'])
defineEmits(['update:modelValue'])
</script>
