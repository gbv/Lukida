<template>
  <div class="navbar bg-base-200 shadow-sm px-4">
    <div class="flex-1">
      <span class="text-xl font-bold text-primary cursor-pointer" @click="$emit('show-search')">{{ APP_TITLE }}</span>
    </div>
    <div class="flex-none gap-2">
      <select class="select select-sm select-bordered" :value="currentTheme" @change="onThemeChange($event.target.value)">
        <option v-for="t in themes" :key="t" :value="t">{{ t.charAt(0).toUpperCase() + t.slice(1) }}</option>
      </select>
      <button v-if="!user" class="btn btn-sm btn-primary" @click="showLogin = true">Anmelden</button>
      <button v-else class="btn btn-sm btn-ghost" @click="$emit('logout')">{{ user.name || user.login }} abmelden</button>
    </div>
  </div>

  <!-- Tab-Leiste -->
  <div class="bg-base-200 border-t border-base-300 px-4 flex gap-1 text-sm overflow-x-auto">
    <button class="py-2 px-3 border-b-2 font-medium whitespace-nowrap flex items-center gap-1.5"
      :class="currentView === 'search' ? 'border-primary text-primary' : 'border-transparent opacity-60'"
      @click="$emit('show-search')">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      SUCHE
    </button>
    <button v-if="user" class="py-2 px-3 border-b-2 font-medium whitespace-nowrap flex items-center gap-1.5"
      :class="currentView === 'account' ? 'border-primary text-primary' : 'border-transparent opacity-60'"
      @click="$emit('show-account')">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
      MEIN KONTO
    </button>
    <button class="py-2 px-3 border-b-2 font-medium whitespace-nowrap flex items-center gap-1.5"
      :class="currentView === 'favs' ? 'border-primary text-primary' : 'border-transparent opacity-60'"
      @click="$emit('show-favs')">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" :fill="currentView === 'favs' ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
      FAVORITEN
    </button>
    <button class="py-2 px-3 border-b-2 font-medium whitespace-nowrap flex items-center gap-1.5"
      :class="currentView === 'history' ? 'border-primary text-primary' : 'border-transparent opacity-60'"
      @click="$emit('show-history')">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      SUCHVERLAUF
    </button>
    <button class="py-2 px-3 border-b-2 font-medium whitespace-nowrap flex items-center gap-1.5"
      :class="currentView === 'info' ? 'border-primary text-primary' : 'border-transparent opacity-60'"
      @click="$emit('show-info')">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      INFOSEITE
    </button>
  </div>

  <!-- Login Modal -->
  <div v-if="showLogin" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40" @click.self="showLogin = false">
    <div class="bg-base-100 rounded-lg shadow-xl p-6 w-80 max-w-full">
      <div class="font-semibold text-base mb-4">Bibliothekskonto</div>
      <div class="form-control mb-3">
        <label class="label"><span class="label-text">Bibliotheksnummer</span></label>
        <input v-model="loginForm.user" type="text" class="input input-bordered input-sm" placeholder="z.B. 31000455572" />
      </div>
      <div class="form-control mb-4">
        <label class="label"><span class="label-text">PIN</span></label>
        <input v-model="loginForm.pw" type="password" class="input input-bordered input-sm" placeholder="PIN" @keyup.enter="doLogin" />
      </div>
      <div v-if="loginError" class="text-error text-sm mb-3">{{ loginError }}</div>
      <div class="flex justify-end gap-2">
        <button class="btn btn-sm btn-ghost" @click="showLogin = false">Abbrechen</button>
        <button class="btn btn-sm btn-primary" :disabled="loading" @click="doLogin">
          <span v-if="loading" class="loading loading-spinner loading-xs"></span>
          Anmelden
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { api, APP_TITLE } from '../api.js'

defineProps(['currentTheme', 'user', 'currentView'])
const emit = defineEmits(['theme-change','show-account','show-search','show-favs','show-history','show-info','login','logout'])

function onThemeChange(val) {
  document.documentElement.setAttribute('data-theme', val)
  localStorage.setItem('lukida_theme', val)
  emit('theme-change', val)
}

const themes = [
  'light','dark','cupcake','bumblebee','emerald','corporate','synthwave',
  'retro','cyberpunk','valentine','halloween','garden','forest','aqua',
  'lofi','pastel','fantasy','wireframe','black','luxury','dracula','cmyk',
  'autumn','business','acid','lemonade','night','coffee','winter','nord'
]

const showLogin  = ref(false)
const loading    = ref(false)
const loginError = ref('')
const loginForm  = ref({ user: '', pw: '' })

async function doLogin() {
  loginError.value = ''
  if (!loginForm.value.user || !loginForm.value.pw) {
    loginError.value = 'Bitte Bibliotheksnummer und PIN eingeben.'
    return
  }
  loading.value = true
  try {
    const data = await api('login', {
      area:    'findex',
      login:   loginForm.value.user,
      pw:      loginForm.value.pw,
      display: { user: true, items: true, fees: true }
    })
    if (data.stat === 'success') {
      sessionStorage.setItem('lukida_user', JSON.stringify(data.user))
      emit('login', { ...data.user, _accountData: data })
      showLogin.value = false
      loginForm.value = { user: '', pw: '' }
    } else {
      loginError.value = 'Anmeldung fehlgeschlagen. Bitte prüfen Sie Ihre Zugangsdaten.'
    }
  } catch {
    loginError.value = 'Verbindungsfehler.'
  }
  loading.value = false
}
</script>
