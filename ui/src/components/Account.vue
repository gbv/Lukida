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
          <div v-for="(l, i) in loans" :key="i" class="text-sm border-b pb-2 flex items-start gap-3">
            <div class="flex-1">
              <div class="font-medium">{{ l.about }}</div>
              <div v-if="l.label" class="opacity-60">{{ l.label }}</div>
              <div class="opacity-60">Fällig: {{ l.endtime }}</div>
              <div v-if="meldung[l.item]" class="mt-1" :class="meldung[l.item].ok ? 'text-success' : 'text-error'">
                {{ meldung[l.item].text }}
              </div>
            </div>
            <button v-if="l.canrenew" class="btn btn-xs btn-outline" :disabled="laeuft === l.item"
                    @click="tun('renew', l.item)">
              <span v-if="laeuft === l.item" class="loading loading-spinner loading-xs"></span>
              Verlängern
            </button>
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
          <div v-for="(o, i) in orders" :key="i" class="text-sm border-b pb-2 flex items-start gap-3">
            <div class="flex-1">
              <div class="font-medium">{{ o.about }}</div>
              <div v-if="o.label" class="opacity-60">{{ o.label }}</div>
              <div v-if="o.status === 1" class="opacity-60">Vorgemerkt<span v-if="o.queue"> (Platz {{ o.queue }})</span></div>
              <div v-if="meldung[o.item]" class="mt-1" :class="meldung[o.item].ok ? 'text-success' : 'text-error'">
                {{ meldung[o.item].text }}
              </div>
            </div>
            <button v-if="o.cancancel" class="btn btn-xs btn-outline" :disabled="laeuft === o.item"
                    @click="tun('cancel', o.item)">
              <span v-if="laeuft === o.item" class="loading loading-spinner loading-xs"></span>
              Stornieren
            </button>
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
import { computed, ref } from 'vue'
import { api } from '../api.js'

const props = defineProps(['user', 'accountData'])
defineEmits(['logout'])

// Verlaengern und Stornieren.
//
// Beides geht ueber denselben Einstieg "action" (type = renew | cancel) und
// braucht Token, Bibliotheksnummer und die Exemplarkennung ("item" aus der
// PAIA-Antwort). Mehr bietet Essential bewusst nicht an - kein Passwort
// aendern, keine Mailadresse, keine Vormerkung ueber das Konto.
//
// Nach dem Erfolg wird NICHT neu geladen: dafuer gaebe es keinen Einstieg
// ohne erneute Anmeldung. Stattdessen verschwindet eine stornierte Zeile aus
// der Liste, und bei einer Verlaengerung steht die Rueckmeldung des Servers
// darunter - sie nennt das neue Faelligkeitsdatum.
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
