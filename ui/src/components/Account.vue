<template>
  <div class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Mein Konto</h1>
    <div class="flex flex-col sm:flex-row gap-6 mb-8">
      <dl class="flex-1 grid grid-cols-[auto_1fr] gap-x-4 gap-y-2">
        <dt class="text-muted">Name</dt><dd class="font-semibold">{{ user.name }}</dd>
        <dt class="text-muted">Bibliotheksnummer</dt><dd>{{ user.login }}</dd>
        <template v-if="user.email"><dt class="text-muted">Mail</dt><dd>{{ user.email }}</dd></template>
        <template v-if="user.address"><dt class="text-muted">Adresse</dt><dd>{{ user.address }}</dd></template>
        <template v-if="user.expires"><dt class="text-muted">Ablaufdatum</dt><dd>{{ user.expires }}</dd></template>
      </dl>
      <div>
        <button type="button" class="btn btn-outline btn-error h-11 min-h-11" @click="$emit('logout')">Abmelden</button>
      </div>
    </div>

    <details class="collapse collapse-arrow bg-base-200 border border-base-300 mb-2">
      <summary class="collapse-title font-medium flex items-center gap-2 min-h-11">
        <span class="badge" :class="notes.length ? 'badge-error' : 'badge-neutral'">{{ notes.length }}</span>
        Meldungen
      </summary>
      <div class="collapse-content">
        <ul v-if="notes.length" class="space-y-2 pt-2">
          <li v-for="(n, i) in notes" :key="i" class="text-sm">{{ n.label }}</li>
        </ul>
        <p v-else class="text-sm text-muted pt-2">Keine Meldungen</p>
      </div>
    </details>

    <details class="collapse collapse-arrow bg-base-200 border border-base-300 mb-2">
      <summary class="collapse-title font-medium flex items-center gap-2 min-h-11">
        <span class="badge badge-neutral">{{ loans.length }}</span>
        Ausleihen
      </summary>
      <div class="collapse-content">
        <ul v-if="loans.length" class="space-y-2 pt-2">
          <li v-for="(l, i) in loans" :key="i" class="text-sm border-b border-base-300 pb-2 flex flex-wrap items-start gap-3">
            <div class="flex-1 min-w-40">
              <div class="font-medium">{{ l.about }}</div>
              <div v-if="l.label" class="text-muted">{{ l.label }}</div>
              <div class="text-muted">Fällig: {{ l.endtime }}</div>
              <p v-if="meldung[l.item]" role="status" class="mt-1" :class="meldung[l.item].ok ? 'text-success' : 'text-error'">
                {{ meldung[l.item].text }}
              </p>
            </div>
            <button v-if="l.canrenew" type="button" class="btn btn-sm btn-outline h-11 min-h-11" :disabled="laeuft === l.item"
                    :aria-label="'Verlängern: ' + l.about" @click="tun('renew', l.item)">
              <span v-if="laeuft === l.item" class="loading loading-spinner loading-xs" aria-hidden="true"></span>
              Verlängern
            </button>
          </li>
        </ul>
        <p v-else class="text-sm text-muted pt-2">Keine Ausleihen</p>
      </div>
    </details>

    <details class="collapse collapse-arrow bg-base-200 border border-base-300 mb-2">
      <summary class="collapse-title font-medium flex items-center gap-2 min-h-11">
        <span class="badge badge-neutral">{{ orders.length }}</span>
        Bestellungen
      </summary>
      <div class="collapse-content">
        <ul v-if="orders.length" class="space-y-2 pt-2">
          <li v-for="(o, i) in orders" :key="i" class="text-sm border-b border-base-300 pb-2 flex flex-wrap items-start gap-3">
            <div class="flex-1 min-w-40">
              <div class="font-medium">{{ o.about }}</div>
              <div v-if="o.label" class="text-muted">{{ o.label }}</div>
              <div v-if="o.status === 1" class="text-muted">Vorgemerkt<span v-if="o.queue"> (Platz {{ o.queue }})</span></div>
              <p v-if="meldung[o.item]" role="status" class="mt-1" :class="meldung[o.item].ok ? 'text-success' : 'text-error'">
                {{ meldung[o.item].text }}
              </p>
            </div>
            <button v-if="o.cancancel" type="button" class="btn btn-sm btn-outline h-11 min-h-11" :disabled="laeuft === o.item"
                    :aria-label="'Stornieren: ' + o.about" @click="tun('cancel', o.item)">
              <span v-if="laeuft === o.item" class="loading loading-spinner loading-xs" aria-hidden="true"></span>
              Stornieren
            </button>
          </li>
        </ul>
        <p v-else class="text-sm text-muted pt-2">Keine Bestellungen</p>
      </div>
    </details>

    <details class="collapse collapse-arrow bg-base-200 border border-base-300 mb-2">
      <summary class="collapse-title font-medium flex items-center gap-2 min-h-11">
        <span class="badge" :class="fees?.amount && fees.amount !== '0.00 EUR' ? 'badge-warning' : 'badge-neutral'">
          {{ fees?.amount || '0.00 EUR' }}
        </span>
        Gebühren
      </summary>
      <div class="collapse-content">
        <ul v-if="fees?.items?.length" class="space-y-2 pt-2">
          <li v-for="(f, i) in fees.items" :key="i" class="text-sm">{{ f.about }} – {{ f.fee }}</li>
        </ul>
        <p v-else class="text-sm text-muted pt-2">Keine Gebühren</p>
      </div>
    </details>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { api } from '../api.js'

const props = defineProps(['user', 'accountData'])
defineEmits(['logout'])

// Verlängern/Stornieren; eine stornierte Zeile verschwindet ohne Neuladen
const laeuft  = ref('')
const meldung = ref({})
const entfernt = ref([])

async function tun(was, id) {
  if (!id || laeuft.value) return
  laeuft.value = id
  delete meldung.value[id]
  try {
    const d = await api('action', {
      type:   was,
      id,
      login:  props.user?.login,
      tokens: { user: props.user?.token },
    })
    const ok = (d.status ?? -1) >= 0 || d.stat === 'success'
    const txt = (Array.isArray(d.mess) ? d.mess.filter(Boolean).join(' ') : '')
             || (Array.isArray(d.more) ? d.more.filter(Boolean).join(' ') : '')
             || (ok ? (was === 'renew' ? 'Verlängert.' : 'Storniert.') : 'Nicht möglich.')
    meldung.value = { ...meldung.value, [id]: { ok, text: txt } }
    if (ok && was === 'cancel') entfernt.value = [...entfernt.value, id]
  } catch {
    meldung.value = { ...meldung.value, [id]: { ok: false, text: 'Verbindungsfehler.' } }
  }
  laeuft.value = ''
}

const notes  = computed(() => props.user?.notes  || [])
const loans  = computed(() => props.accountData?.items?.filter(i => i.status === 3) || [])
const orders = computed(() => (props.accountData?.items || [])
  .filter(i => (i.status === 1 || i.status === 2) && !entfernt.value.includes(i.item)))
const fees   = computed(() => props.accountData?.fees || null)
</script>
