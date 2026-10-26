<template>
  <div class="min-h-screen bg-base-100">
    <Navbar
      @theme-change="theme = $event"
      :current-theme="theme"
      :user="currentUser"
      :current-view="currentView"
      @show-account="currentView = 'account'"
      @show-search="currentView = 'search'"
      @show-favs="currentView = 'favs'"
      @show-history="currentView = 'history'"
      @show-info="currentView = 'info'"
      @login="onLogin"
      @logout="onLogout"
    />
    <Account v-if="currentView === 'account' && currentUser"
             :user="currentUser" :account-data="accountData" @logout="onLogout" />
    <FavoritesPage v-else-if="currentView === 'favs'" ref="favsPage" @open="openModalDirect" />
    <HistoryPage v-else-if="currentView === 'history'" @search="runHistorySearch" />
    <InfoPage v-else-if="currentView === 'info'" />
    <main v-else class="container mx-auto px-4 py-8 max-w-5xl">
      <SearchBar @search="newSearch" :loading="loading" />
      <div v-if="hits > 0" class="text-sm opacity-50 mb-4">{{ hits }} Treffer</div>
      <ResultList v-if="results.length" :results="results" @open="openModal" />
      <Welcome v-else-if="!loading" />
      <div v-if="loading" class="flex justify-center mt-8">
        <span class="loading loading-spinner loading-lg text-primary"></span>
      </div>
      <div v-if="allLoaded && results.length" class="text-center text-sm opacity-40 mt-8 mb-4">
        Alle {{ hits }} Treffer geladen
      </div>
    </main>
    <Modal
      :item="selectedItem"
      :has-prev="selectedIndex > 0"
      :has-next="selectedIndex < results.length - 1"
      @close="selectedItem = null"
      @navigate="navigate"
      @openItem="openModalDirect"
      @favChanged="onFavChanged"
    />
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import Navbar        from './components/Navbar.vue'
import SearchBar     from './components/SearchBar.vue'
import ResultList    from './components/ResultList.vue'
import Welcome       from './components/Welcome.vue'
import Modal         from './components/Modal.vue'
import Account       from './components/Account.vue'
import FavoritesPage from './components/FavoritesPage.vue'
import HistoryPage   from './components/HistoryPage.vue'
import InfoPage      from './components/InfoPage.vue'

const theme         = ref(localStorage.getItem('lukida_theme') || 'nord')
const currentView   = ref('search')
const currentUser   = ref((() => { try { return JSON.parse(sessionStorage.getItem('lukida_user')) } catch { return null } })())
const accountData   = ref(null)
const loading       = ref(false)
const results       = ref([])
const hits          = ref(0)
const start         = ref(0)
const allLoaded     = ref(false)
const pageSize      = 25
const query         = ref('')
const selectedItem  = ref(null)
const selectedIndex = ref(-1)
const favsPage      = ref(null)

watch(theme, val => {
  document.documentElement.setAttribute('data-theme', val)
  localStorage.setItem('lukida_theme', val)
})

function openModal(item) {
  selectedIndex.value = results.value.indexOf(item)
  selectedItem.value  = item
}
function openModalDirect(item) {
  selectedIndex.value = results.value.indexOf(item)
  selectedItem.value  = item
}
function onFavChanged() { favsPage.value?.refresh() }

function onLogin(user) {
  const { _accountData, ...userData } = user
  currentUser.value = userData
  accountData.value = _accountData || null
  currentView.value = 'account'
}
function onLogout() {
  currentUser.value = null
  accountData.value = null
  currentView.value = 'search'
  sessionStorage.removeItem('lukida_user')
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
  query.value = q; start.value = 0; results.value = []
  hits.value = 0; allLoaded.value = false
  currentView.value = 'search'
  saveToHistory(q)
  doSearch()
}
function runHistorySearch(q) { currentView.value = 'search'; newSearch(q) }

async function doSearch() {
  if (!query.value.trim() || loading.value || allLoaded.value) return
  loading.value = true
  try {
    const res = await fetch('/api/5/user/search', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        apikey:  import.meta.env.VITE_API_KEY,
        client:  'Demo', appl: 'production', area: 'findex',
        search:  { query: query.value },
        display: { bib: true },
        start:   start.value, limit: pageSize
      })
    })
    const data     = await res.json()
    const newItems = data.data || []
    hits.value     = data.hits || 0
    results.value.push(...newItems)
    start.value += newItems.length
    if (newItems.length < pageSize || start.value >= hits.value) allLoaded.value = true
  } catch (e) { console.error(e) } finally { loading.value = false }
}
function onScroll() {
  const nearBottom = window.innerHeight + window.scrollY >= document.body.offsetHeight - 300
  if (nearBottom && !loading.value && !allLoaded.value && currentView.value === 'search') doSearch()
}
onMounted(() => {
  window.addEventListener('scroll', onScroll)
  document.documentElement.setAttribute('data-theme', theme.value)
})
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>
