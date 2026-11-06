<template>
  <div class="min-h-screen bg-base-100 text-base-content">
    <a href="#inhalt" class="sprunglink btn btn-primary h-11 min-h-11" @click.prevent="zumInhalt">Zum Inhalt springen</a>

    <Navbar
      :user="currentUser"
      :current-view="currentView"
      @show-account="zeige('account')"
      @show-search="zeige('search')"
      @show-favs="zeige('favs')"
      @show-history="zeige('history')"
      @show-info="zeige('info')"
      @login="onLogin"
      @logout="onLogout"
    />

    <main id="inhalt" ref="inhalt" tabindex="-1" class="outline-none">
      <Account v-if="currentView === 'account' && currentUser"
               :user="currentUser" :account-data="accountData" @logout="onLogout" />
      <FavoritesPage v-else-if="currentView === 'favs'" @open="openModal" />
      <HistoryPage v-else-if="currentView === 'history'" @search="runHistorySearch" />
      <InfoPage v-else-if="currentView === 'info'" />
      <div v-else class="container mx-auto px-4 py-8 max-w-5xl">
        <h1 v-if="results.length || hits > 0 || gesucht" class="sr-only">Suche</h1>
        <SearchBar @search="newSearch" :loading="loading" />
        <p v-if="hits > 0" class="text-sm text-muted mb-4">{{ hits }} Treffer</p>
        <p v-else-if="gesucht && !loading && !fehler" class="mb-4">Keine Treffer für „{{ query }}“.</p>
        <p v-if="fehler" class="text-error mb-4">{{ fehler }}</p>
        <ResultList v-if="results.length" :results="results" @open="openModal" />
        <Welcome v-else-if="!loading && !gesucht" />
        <div v-if="loading" class="flex justify-center mt-8">
          <span class="loading loading-spinner loading-lg text-primary" aria-hidden="true"></span>
        </div>
        <div v-if="results.length && !allLoaded && !loading" class="text-center mt-8">
          <button class="btn btn-outline h-11 min-h-11" @click="doSearch">Weitere Treffer laden</button>
        </div>
        <p v-if="allLoaded && results.length" class="text-center text-sm text-muted mt-8 mb-4">
          Alle {{ hits }} Treffer geladen
        </p>
      </div>
    </main>

    <!-- Ansagen für Bildschirmleser -->
    <p role="status" class="sr-only">{{ ansage }}</p>

    <Modal
      :item="selectedItem"
      :user="currentUser"
      :has-prev="selectedIndex > 0"
      :has-next="selectedIndex >= 0 && selectedIndex < results.length - 1"
      @close="schliesseModal"
      @navigate="navigate"
      @openItem="openModal"
    />
  </div>
</template>

<script setup>
import { ref, watch, computed, nextTick, onMounted, onUnmounted } from 'vue'
import Navbar        from './components/Navbar.vue'
import SearchBar     from './components/SearchBar.vue'
import ResultList    from './components/ResultList.vue'
import Welcome       from './components/Welcome.vue'
import Modal         from './components/Modal.vue'
import Account       from './components/Account.vue'
import FavoritesPage from './components/FavoritesPage.vue'
import HistoryPage   from './components/HistoryPage.vue'
import InfoPage      from './components/InfoPage.vue'
import { api, APP_TITLE, THEME } from './api.js'
import { startTheme, setzeTheme } from './themes.js'

const theme         = ref(startTheme())
const currentView   = ref('search')
const currentUser   = ref((() => { try { return JSON.parse(sessionStorage.getItem('lukida_user')) } catch { return null } })())
const accountData   = ref(null)
const loading       = ref(false)
const results       = ref([])
const hits          = ref(0)
const start         = ref(0)
const allLoaded     = ref(false)
const gesucht       = ref(false)
const fehler        = ref('')
const pageSize      = 25
const query         = ref('')
const selectedItem  = ref(null)
const selectedIndex = ref(-1)
const inhalt        = ref(null)
let   letzterFokus  = null


const TITEL = { search: 'Suche', account: 'Mein Konto', favs: 'Favoriten', history: 'Suchverlauf', info: 'Infoseite' }
watch(currentView, v => { document.title = TITEL[v] + ' – ' + APP_TITLE }, { immediate: true })

const ansage = computed(() => {
  if (loading.value) return 'Suche läuft.'
  if (fehler.value) return fehler.value
  if (!gesucht.value) return ''
  return hits.value ? hits.value + ' Treffer, ' + results.value.length + ' angezeigt.' : 'Keine Treffer.'
})

// Ansicht wechseln, Fokus an den Inhaltsanfang
function zeige(view) {
  currentView.value = view
  nextTick(() => zumInhalt())
}
function zumInhalt() {
  inhalt.value?.focus()
}

// Blättern nur innerhalb der Trefferliste
function openModal(item) {
  if (!selectedItem.value) letzterFokus = document.activeElement
  selectedIndex.value = results.value.indexOf(item)
  selectedItem.value  = item
}
function schliesseModal() {
  selectedItem.value = null
  const ziel = letzterFokus
  letzterFokus = null
  nextTick(() => { if (ziel && document.contains(ziel)) ziel.focus() })
}

function onLogin(user) {
  const { _accountData, ...userData } = user
  currentUser.value = userData
  accountData.value = _accountData || null
  zeige('account')
}
function onLogout() {
  currentUser.value = null
  accountData.value = null
  sessionStorage.removeItem('lukida_user')
  zeige('search')
}
function navigate(delta) {
  const newIndex = selectedIndex.value + delta
  if (newIndex < 0 || newIndex >= results.value.length) return
  selectedIndex.value = newIndex
  selectedItem.value  = results.value[newIndex]
}
function saveToHistory(q) {
  if (!q?.trim()) return
  try {
    const h = JSON.parse(localStorage.getItem('lukida_history') || '[]')
    const filtered = h.filter(e => e.q !== q.trim()).slice(0, 49)
    filtered.unshift({ q: q.trim(), ts: Date.now() })
    localStorage.setItem('lukida_history', JSON.stringify(filtered))
  } catch {}
}
function newSearch(q) {
  if (!q?.trim()) return
  query.value = q; start.value = 0; results.value = []
  hits.value = 0; allLoaded.value = false; fehler.value = ''
  currentView.value = 'search'
  saveToHistory(q)
  doSearch()
}
function runHistorySearch(q) { newSearch(q) }

async function doSearch() {
  if (!query.value.trim() || loading.value || allLoaded.value) return
  loading.value = true
  try {
    const data     = await api('search', {
      area:    'catalog',
      search:  { query: query.value, type: 'lukida' },
      display: { bib: true, items: true },
      start:   start.value, limit: pageSize
    })
    if (data.code === 'indexbusy') {
      fehler.value = 'Gerade suchen sehr viele Menschen gleichzeitig. Bitte versuchen Sie es in einer Minute noch einmal.'
      return
    }
    if (data.code === 'toomanywords') {
      fehler.value = 'Die Suche enthält zu viele Wörter. Bitte kürzen Sie sie.'
      return
    }
    const newItems = data.data || []
    hits.value     = data.hits || 0
    results.value.push(...newItems)
    start.value += newItems.length
    if (newItems.length < pageSize || start.value >= hits.value) allLoaded.value = true
  } catch (e) {
    console.error(e)
    fehler.value = 'Die Suche ist gerade nicht möglich. Bitte versuchen Sie es später noch einmal.'
  } finally {
    loading.value = false
    gesucht.value = true
  }
}

// Nachladen beim Scrollen, nach einem Fehler nur noch per Knopf
function onScroll() {
  const nearBottom = window.innerHeight + window.scrollY >= document.body.offsetHeight - 300
  if (nearBottom && !loading.value && !allLoaded.value && !fehler.value && currentView.value === 'search') doSearch()
}
onMounted(() => {
  if (THEME) { theme.value = THEME; setzeTheme(THEME) }
  window.addEventListener('scroll', onScroll, { passive: true })
})
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>
