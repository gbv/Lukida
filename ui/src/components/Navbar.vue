<template>
  <header class="bg-base-200 border-b border-base-300">
    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 px-4 py-2">
      <a href="#" class="text-xl font-bold text-primary flex-1 min-w-0 py-2 rounded"
         @click.prevent="$emit('show-search')">{{ APP_TITLE }}</a>
      <div class="flex flex-wrap items-center gap-2">
        <button v-if="!user" ref="anmeldeKnopf" class="btn btn-primary h-11 min-h-11" @click="oeffneAnmeldung">
          Anmelden
        </button>
        <button v-else class="btn btn-outline h-11 min-h-11" @click="$emit('logout')">
          {{ user.name || user.login }} abmelden
        </button>
      </div>
    </div>

    <nav aria-label="Hauptnavigation" class="border-t border-base-300 px-2">
      <ul class="flex flex-wrap gap-x-1 text-sm">
        <li v-for="r in reiter" :key="r.view">
          <button type="button"
                  class="min-h-11 py-2 px-3 border-b-4 font-medium whitespace-nowrap flex items-center gap-1.5"
                  :class="currentView === r.view ? 'border-primary text-primary' : 'border-transparent text-base-content'"
                  :aria-current="currentView === r.view ? 'page' : undefined"
                  @click="$emit(r.event)">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="r.icon"/>
            </svg>
            {{ r.label }}
          </button>
        </li>
      </ul>
    </nav>
  </header>

  <!-- Anmeldung -->
  <dialog ref="dialog" class="dialog klein" aria-labelledby="anmeldung-titel" @close="nachSchliessen">
    <form class="p-6" @submit.prevent="doLogin">
      <h2 id="anmeldung-titel" class="font-semibold text-lg mb-4">Bibliothekskonto</h2>
      <div class="mb-3">
        <label for="anmeldung-nummer" class="block text-sm font-medium mb-1">Bibliotheksnummer</label>
        <input id="anmeldung-nummer" v-model="loginForm.user" type="text" autocomplete="username"
               class="input w-full h-11" :aria-invalid="!!loginError || undefined"
               :aria-describedby="loginError ? 'anmeldung-fehler' : undefined" />
      </div>
      <div class="mb-4">
        <label for="anmeldung-pin" class="block text-sm font-medium mb-1">PIN</label>
        <input id="anmeldung-pin" v-model="loginForm.pw" type="password" autocomplete="current-password"
               class="input w-full h-11" :aria-invalid="!!loginError || undefined"
               :aria-describedby="loginError ? 'anmeldung-fehler' : undefined" />
      </div>
      <p v-if="loginError" id="anmeldung-fehler" role="alert" class="text-error text-sm mb-3">{{ loginError }}</p>
      <div class="flex justify-end gap-2">
        <button type="button" class="btn btn-ghost h-11 min-h-11" @click="dialog.close()">Abbrechen</button>
        <button type="submit" class="btn btn-primary h-11 min-h-11" :disabled="loading">
          <span v-if="loading" class="loading loading-spinner loading-xs" aria-hidden="true"></span>
          Anmelden
        </button>
      </div>
    </form>
  </dialog>
</template>

<script setup>
import { ref, computed } from 'vue'
import { api, APP_TITLE } from '../api.js'

const props = defineProps(['currentTheme', 'user', 'currentView'])
const emit  = defineEmits(['show-account','show-search','show-favs','show-history','show-info','login','logout'])

const ICON = {
  suche:   'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
  konto:   'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
  stern:   'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
  uhr:     'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
  info:    'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
}

const reiter = computed(() => [
  { view: 'search',  event: 'show-search',  label: 'Suche',       icon: ICON.suche },
  ...(props.user ? [{ view: 'account', event: 'show-account', label: 'Mein Konto', icon: ICON.konto }] : []),
  { view: 'favs',    event: 'show-favs',    label: 'Favoriten',   icon: ICON.stern },
  { view: 'history', event: 'show-history', label: 'Suchverlauf', icon: ICON.uhr },
  { view: 'info',    event: 'show-info',    label: 'Infoseite',   icon: ICON.info },
])

const dialog       = ref(null)
const anmeldeKnopf = ref(null)
const loading      = ref(false)
const loginError   = ref('')
const loginForm    = ref({ user: '', pw: '' })

function oeffneAnmeldung() {
  loginError.value = ''
  dialog.value.showModal()
}
// Fokus zurück auf den Knopf
function nachSchliessen() {
  loginForm.value = { user: '', pw: '' }
  loginError.value = ''
  anmeldeKnopf.value?.focus()
}

async function doLogin() {
  loginError.value = ''
  if (!loginForm.value.user || !loginForm.value.pw) {
    loginError.value = 'Bitte Bibliotheksnummer und PIN eingeben.'
    return
  }
  loading.value = true
  try {
    const data = await api('login', {
      area:    'catalog',
      login:   loginForm.value.user,
      pw:      loginForm.value.pw,
      display: { user: true, items: true, fees: true }
    })
    if (data.stat === 'success') {
      sessionStorage.setItem('lukida_user', JSON.stringify(data.user))
      emit('login', { ...data.user, _accountData: data })
      dialog.value.close()
    } else {
      loginError.value = 'Anmeldung fehlgeschlagen. Bitte prüfen Sie Ihre Zugangsdaten.'
    }
  } catch {
    loginError.value = 'Verbindungsfehler.'
  }
  loading.value = false
}
</script>
