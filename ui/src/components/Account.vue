<template>
  <div class="max-w-3xl mx-auto px-4 py-8">
    <!-- Nutzerdaten -->
    <div class="flex flex-col sm:flex-row gap-6 mb-8">
      <div class="flex-1 space-y-3">
        <div>
          <div class="font-semibold">{{ user.name }}</div>
          <div class="text-xs opacity-50">Name</div>
        </div>
        <div>
          <div>{{ user.login }}</div>
          <div class="text-xs opacity-50">Bibliotheksnummer</div>
        </div>
        <div v-if="user.email">
          <div>{{ user.email }}</div>
          <div class="text-xs opacity-50">Mail</div>
        </div>
        <div v-if="user.address">
          <div>{{ user.address }}</div>
          <div class="text-xs opacity-50">Adresse</div>
        </div>
        <div v-if="user.expires">
          <div>{{ user.expires }}</div>
          <div class="text-xs opacity-50">Ablaufdatum</div>
        </div>
      </div>
      <div class="flex flex-col gap-2">
        <button class="btn btn-sm btn-error" @click="$emit('logout')">ABMELDEN</button>
      </div>
    </div>

    <!-- Meldungen -->
    <div class="collapse collapse-arrow bg-base-200 mb-2">
      <input type="checkbox" />
      <div class="collapse-title font-medium flex items-center gap-2">
        <span class="badge badge-sm" :class="notes.length ? 'badge-error' : 'badge-ghost'">{{ notes.length }}</span>
        Meldungen
      </div>
      <div class="collapse-content">
        <div v-if="notes.length" class="space-y-2 pt-2">
          <div v-for="(n, i) in notes" :key="i" class="text-sm">{{ n.label }}</div>
        </div>
        <div v-else class="text-sm opacity-50 pt-2">Keine Meldungen</div>
      </div>
    </div>

    <!-- Ausleihen -->
    <div class="collapse collapse-arrow bg-base-200 mb-2">
      <input type="checkbox" />
      <div class="collapse-title font-medium flex items-center gap-2">
        <span class="badge badge-sm badge-ghost">{{ loans.length }}</span>
        Ausleihen
      </div>
      <div class="collapse-content">
        <div v-if="loans.length" class="space-y-2 pt-2">
          <div v-for="(l, i) in loans" :key="i" class="text-sm border-b pb-2">
            <div class="font-medium">{{ l.about }}</div>
            <div class="opacity-60">Fällig: {{ l.endtime }}</div>
          </div>
        </div>
        <div v-else class="text-sm opacity-50 pt-2">Keine Ausleihen</div>
      </div>
    </div>

    <!-- Bestellungen -->
    <div class="collapse collapse-arrow bg-base-200 mb-2">
      <input type="checkbox" />
      <div class="collapse-title font-medium flex items-center gap-2">
        <span class="badge badge-sm badge-ghost">{{ orders.length }}</span>
        Bestellungen
      </div>
      <div class="collapse-content">
        <div v-if="orders.length" class="space-y-2 pt-2">
          <div v-for="(o, i) in orders" :key="i" class="text-sm border-b pb-2">
            <div class="font-medium">{{ o.about }}</div>
          </div>
        </div>
        <div v-else class="text-sm opacity-50 pt-2">Keine Bestellungen</div>
      </div>
    </div>

    <!-- Gebühren -->
    <div class="collapse collapse-arrow bg-base-200 mb-2">
      <input type="checkbox" />
      <div class="collapse-title font-medium flex items-center gap-2">
        <span class="badge badge-sm" :class="fees?.amount !== '0.00 EUR' ? 'badge-warning' : 'badge-ghost'">
          {{ fees?.amount || '0.00 EUR' }}
        </span>
        Gebühren
      </div>
      <div class="collapse-content">
        <div v-if="fees?.items?.length" class="space-y-2 pt-2">
          <div v-for="(f, i) in fees.items" :key="i" class="text-sm">{{ f.about }} — {{ f.fee }}</div>
        </div>
        <div v-else class="text-sm opacity-50 pt-2">Keine Gebühren</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps(['user', 'accountData'])
defineEmits(['logout'])

const notes  = computed(() => props.user?.notes  || [])
const loans  = computed(() => props.accountData?.items?.filter(i => i.status === 3) || [])
const orders = computed(() => props.accountData?.items?.filter(i => i.status === 1 || i.status === 2) || [])
const fees   = computed(() => props.accountData?.fees || null)
</script>
