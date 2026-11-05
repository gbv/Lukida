<template>
  <dialog ref="dialog" class="dialog" aria-labelledby="vollanzeige-titel" @close="onClose" @cancel="onCancel">
    <div v-if="item" class="flex flex-col max-h-[calc(100dvh-1rem)]">
      <div class="flex items-start gap-4 p-5 pb-3">
        <div aria-hidden="true" class="shrink-0 w-20 h-28 bg-base-300 rounded flex items-center justify-center text-xs text-muted text-center">Kein Bild</div>
        <div class="flex-1 min-w-0">
          <div class="flex justify-between items-start gap-2">
            <h2 id="vollanzeige-titel" ref="titel" tabindex="-1" class="font-bold text-lg leading-snug outline-none">
              {{ getField('title') || '(kein Titel)' }}
            </h2>
            <div class="flex gap-1 shrink-0 -mt-1 -mr-2">
              <button type="button" class="btn btn-ghost btn-circle h-11 w-11 min-h-11"
                      :aria-pressed="isFav" aria-label="Favorit"
                      :title="isFav ? 'Aus Favoriten entfernen' : 'Zu Favoriten hinzufügen'" @click="toggleFav">
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :fill="isFav ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
              </button>
              <button type="button" class="btn btn-ghost btn-circle h-11 w-11 min-h-11" aria-label="Schließen" title="Schließen" @click="$emit('close')">
                <span aria-hidden="true" class="text-lg">&#x2715;</span>
              </button>
            </div>
          </div>
          <p v-if="getField('band')" class="text-sm text-muted mt-0.5">{{ getField('band') }}</p>
          <p v-if="getField('in')" class="text-sm text-muted mt-0.5">Enthalten in: {{ getField('in') }}</p>
          <p class="text-sm mt-1 flex flex-wrap gap-x-4">
            <span v-for="a in getAuthors()" :key="a">{{ a }}</span>
          </p>
          <p class="text-sm text-muted mt-2 flex items-center gap-2">
            <span v-if="item.year">{{ item.year }}</span>
            <span v-if="item.format && item.format !== 'unknown'">&#183; {{ item.format }}</span>
          </p>
        </div>
      </div>

      <div role="tablist" aria-label="Ansicht" class="flex gap-1 px-5 border-b border-base-300" @keydown="reiterTaste">
        <button v-for="r in REITER" :key="r.id" :id="'reiter-' + r.id" ref="reiterKnoepfe" type="button" role="tab"
                class="min-h-11 px-3 border-b-4 font-medium text-sm"
                :class="tab === r.id ? 'border-primary text-primary' : 'border-transparent'"
                :aria-selected="tab === r.id" :aria-controls="'tafel-' + r.id" :tabindex="tab === r.id ? 0 : -1"
                @click="waehleReiter(r.id)">{{ r.label }}</button>
      </div>

      <div class="overflow-y-auto flex-1 px-5 py-4" role="tabpanel" :id="'tafel-' + tab" :aria-labelledby="'reiter-' + tab" tabindex="0">
        <div v-if="loading" class="flex justify-center py-8">
          <span class="loading loading-spinner loading-lg text-primary" aria-hidden="true"></span>
          <span class="sr-only">Wird geladen</span>
        </div>

        <div v-else-if="tab === 'titel'">
          <dl>
            <div v-for="group in primaryGroups" :key="group.code" class="flex flex-wrap sm:flex-nowrap gap-x-3 text-sm py-2 border-b border-base-300">
              <dt class="text-muted w-36 shrink-0">{{ group.label }}</dt>
              <dd class="flex-1">{{ group.text }}</dd>
            </div>
          </dl>

          <div v-if="detailGroups.length" class="mt-2">
            <button type="button" class="flex items-center gap-1 text-sm font-medium min-h-11" :aria-expanded="showDetails"
                    aria-controls="vollanzeige-details" @click="showDetails = !showDetails">
              Details
              <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': showDetails }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <dl v-show="showDetails" id="vollanzeige-details" class="border-t border-base-300">
              <div v-for="group in detailGroups" :key="group.code" class="flex flex-wrap sm:flex-nowrap gap-x-3 text-sm py-2 border-b border-base-300">
                <dt class="text-muted w-36 shrink-0">{{ group.label }}</dt>
                <dd class="flex-1">{{ group.text }}</dd>
              </div>
            </dl>
          </div>

          <!-- Online-Links aus additionalinfo -->
          <div v-if="onlineLinks.length" class="mt-3 flex flex-wrap gap-2">
            <a v-for="(lnk, i) in onlineLinks" :key="i" :href="lnk.url" target="_blank" rel="noopener"
               class="btn btn-success btn-sm h-11 min-h-11">
              {{ lnk.label }}<span class="sr-only"> (öffnet in neuem Fenster)</span>
            </a>
          </div>

          <!-- Exemplare -->
          <section v-if="items.length" class="mt-4 border-t border-base-300 pt-3" aria-labelledby="vollanzeige-exemplare">
            <h3 id="vollanzeige-exemplare" class="text-sm font-semibold uppercase mb-2 tracking-wide">Exemplare</h3>
            <ul>
              <li v-for="(it, i) in items" :key="i" class="flex flex-wrap gap-3 text-sm py-2 border-b border-base-300 items-start">
                <div class="flex-1 min-w-40">
                  <div class="font-medium">{{ it.location }}</div>
                  <div v-if="it.callnumber" class="text-muted mt-0.5">{{ it.callnumber }}</div>
                </div>
                <div class="shrink-0 flex flex-col items-end gap-1">
                  <!-- Abzeichen nur ohne Knopf (beide aus short.label) -->
                  <span v-if="!it.action && it.statusText" class="badge" :class="it.badgeClass">{{ it.statusText }}</span>
                  <a v-if="it.action === 'link'" :href="it.url" target="_blank" rel="noopener"
                     class="btn btn-success btn-sm h-11 min-h-11">Link öffnen<span class="sr-only"> (öffnet in neuem Fenster)</span></a>
                  <button v-else-if="it.action === 'request'" type="button" @click="oeffneBestellung(it)"
                          class="btn btn-primary btn-sm h-11 min-h-11">{{ it.knopf }}</button>
                </div>
              </li>
            </ul>
          </section>
        </div>

        <!-- ZUGEHÖRIG-Tab -->
        <div v-else-if="tab === 'zugeh'">
          <div v-if="relatedLoading" class="flex justify-center py-8">
            <span class="loading loading-spinner loading-lg text-primary" aria-hidden="true"></span>
            <span class="sr-only">Wird geladen</span>
          </div>
          <p v-else-if="related.length === 0" class="text-sm text-muted text-center py-8">Keine zugehörigen Titel gefunden</p>
          <ul v-else>
            <li v-for="r in related" :key="r.id" class="border-b border-base-300">
              <button type="button" class="flex w-full items-start gap-3 py-3 text-left hover:bg-base-200 rounded"
                      @click="$emit('openItem', r)">
                <span aria-hidden="true" class="shrink-0 w-10 h-14 bg-base-300 rounded"></span>
                <span class="flex-1 min-w-0">
                  <span class="block text-sm font-medium leading-snug line-clamp-2">{{ r.title }}</span>
                  <span class="block text-sm text-muted mt-0.5">{{ r.author }}<span v-if="r.year"> &#183; {{ r.year }}</span></span>
                </span>
              </button>
            </li>
          </ul>
        </div>
      </div>

      <!-- Fuß -->
      <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 border-t border-base-300">
        <div class="flex gap-1">
          <button type="button" class="btn btn-ghost btn-circle h-11 w-11 min-h-11" :disabled="!hasPrev"
                  aria-label="Vorheriger Treffer" title="Vorheriger Treffer" @click="$emit('navigate', -1)"><span aria-hidden="true" class="text-xl">&#8249;</span></button>
          <button type="button" class="btn btn-ghost btn-circle h-11 w-11 min-h-11" :disabled="!hasNext"
                  aria-label="Nächster Treffer" title="Nächster Treffer" @click="$emit('navigate', 1)"><span aria-hidden="true" class="text-xl">&#8250;</span></button>
        </div>
        <div class="flex items-center gap-2">
          <ExportMenu :ids="[item.id]" />
          <button type="button" class="btn h-11 min-h-11" @click="$emit('close')">Schließen</button>
        </div>
      </div>
    </div>

    <!-- Bestellen -->
    <dialog ref="bestellDialog" class="dialog klein" aria-labelledby="bestellung-titel" @close="nachBestellung">
      <div v-if="orderDialogItem" class="p-6">
        <h3 id="bestellung-titel" class="font-semibold text-lg mb-3">{{ orderDialogItem.knopf }}</h3>
        <p v-if="orderDialogItem.location" class="text-sm mb-1 font-medium">{{ orderDialogItem.location }}</p>
        <p v-if="orderDialogItem.callnumber" class="text-sm mb-1">Signatur: {{ orderDialogItem.callnumber }}</p>
        <p v-if="!user" class="text-sm my-3">Zum Bestellen müssen Sie angemeldet sein.</p>
        <p v-if="bestellGrund" role="status" class="text-sm my-3">{{ bestellGrund }}</p>
        <div class="flex justify-end gap-2 mt-4">
          <button type="button" class="btn btn-ghost h-11 min-h-11" @click="bestellDialog.close()">Abbrechen</button>
          <button type="button" class="btn btn-primary h-11 min-h-11" :disabled="!user || bestellLauft" @click="bestellen">
            <span v-if="bestellLauft" class="loading loading-spinner loading-xs" aria-hidden="true"></span>
            {{ orderDialogItem.knopf }}
          </button>
        </div>
      </div>
    </dialog>
  </dialog>
</template>

<script setup>
import { ref, watch, computed, nextTick } from 'vue'
import { api } from '../api.js'
import { isFavorite, toggleFavorite } from '../favorites.js'
import ExportMenu from './ExportMenu.vue'

const props = defineProps(['item', 'hasPrev', 'hasNext', 'user'])
const emit  = defineEmits(['close', 'navigate', 'openItem'])

// Natives Dialogfenster; Fokus auf den Titel
const dialog        = ref(null)
const titel         = ref(null)
const bestellDialog = ref(null)
const reiterKnoepfe = ref([])
const REITER = [{ id: 'titel', label: 'Titel' }, { id: 'zugeh', label: 'Zugehörig' }]

watch(() => props.item, async (item) => {
  await nextTick()
  if (!dialog.value) return
  if (item && !dialog.value.open) dialog.value.showModal()
  if (!item && dialog.value.open) dialog.value.close()
  if (item) titel.value?.focus()
})
function onCancel(e) { e.preventDefault(); emit('close') }
function onClose()   { if (props.item) emit('close') }

function waehleReiter(id) {
  if (id === 'zugeh') switchToZugeh()
  else tab.value = id
}
// Pfeiltasten zwischen den Reitern
function reiterTaste(e) {
  if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(e.key)) return
  e.preventDefault()
  const i = REITER.findIndex(r => r.id === tab.value)
  const n = e.key === 'Home' ? 0 : e.key === 'End' ? REITER.length - 1
          : (i + (e.key === 'ArrowRight' ? 1 : -1) + REITER.length) % REITER.length
  waehleReiter(REITER[n].id)
  nextTick(() => reiterKnoepfe.value[n]?.focus())
}

function oeffneBestellung(it) {
  orderDialogItem.value = it
  bestellGrund.value = ''
  nextTick(() => bestellDialog.value?.showModal())
}
function nachBestellung() {
  orderDialogItem.value = null
  bestellGrund.value = ''
}

const loading         = ref(false)
const detail          = ref(null)
const itemsData       = ref({})
const tab             = ref('titel')
const showDetails     = ref(false)
const orderDialogItem = ref(null)
const related         = ref([])
const relatedLoading  = ref(false)

// -- Favoriten ----------------------------------------------------------------
const isFav = computed(() => !!props.item?.id && isFavorite(props.item.id))
function toggleFav() {
  if (!props.item?.id) return
  const src = detail.value || props.item
  toggleFavorite({
    id:     props.item.id,
    title:  getField('title') || props.item.title || props.item.id,
    author: getAuthors()[0] || '',
    year:   src?.year || props.item.year || '',
    area:   props.item.area || '',
    format: props.item.format || '',
  })
}

// -- Konstanten ---------------------------------------------------------------
const PRIMARY = ['title','band','author','contributor','associates','corporation','part','series','in','publisher','edition','addtitle']
const HIDDEN  = new Set(['format','details','links','keys','source','id','classification','localsystematic','lokalsystematik','orderid'])

const LABELS = {
  title:'Titel', addtitle:'Zusatztitel', author:'Verfasser',
  contributor:'Weitere Beteiligte', associates:'Weitere Beteiligte',
  corporation:'Körperschaft', band:'Band', part:'Teilwerk',
  series:'Schriftenreihe', in:'Erschienen in', edition:'Ausgabe',
  publisher:'Veröffentlicht', place:'Erscheinungsort', size:'Umfang',
  isbn:'ISBN', issn:'ISSN', doi:'DOI', language:'Sprache',
  languagenotes:'Informationen zu Sprache/Schrift',
  description:'Zusammenfassung', summary:'Zusammenfassung',
  contents:'Inhalt', license:'Lizenzbestimmungen', source:'Quelle',
  identifier:'Sonstige Standardnummern', note:'Anmerkung', notes:'Anmerkungen',
  hint:'Hinweise', hints:'Hinweise', subject:'Schlagwort',
  seealso:'Siehe auch', additionalinfo:'Zusätzliche Informationen',
  genre:'Gattung/Form', classification:'Klassifikation',
  localsystematic:'Lokale Systematik', lokalsystematik:'Lokale Systematik',
  form:'Form', hochschulschrift:'Hochschulschrift', dissertation:'Hochschulschrift',
  appearancehistory:'Erscheinungsverlauf', erscheinungsverlauf:'Erscheinungsverlauf',
  article:'Artikel in', orderid:'Bestell-Id', keys:'Sonstige IDs', id:'ID',
}

// -- API-Aufruf ----------------------------------------------------------------
watch(() => props.item, async (item) => {
  if (!item) return
  detail.value         = null
  itemsData.value      = {}
  loading.value        = true
  tab.value            = 'titel'
  showDetails.value    = false
  related.value        = []
  orderDialogItem.value = null
  if (bestellDialog.value?.open) bestellDialog.value.close()
  try {
    const data     = await api('search', {
      area:    item.area || 'catalog',
      search:  { query: 'id:' + item.id, hits: 1, type: 'lukida' },
      display: { bib: true, items: true },
      bms:     1,
    })
    const firstHit = Array.isArray(data.data) ? data.data[0] : null
    detail.value    = firstHit || item
    itemsData.value = firstHit?.items || {}
  } catch (e) {
    detail.value    = item
    itemsData.value = {}
  } finally {
    loading.value = false
  }
}, { immediate: true })

// -- Zugehörig-Tab -------------------------------------------------------------
async function switchToZugeh() {
  tab.value = 'zugeh'
  if (related.value.length > 0 || relatedLoading.value) return
  relatedLoading.value = true
  try {
    const src     = detail.value || props.item
    const bib     = src?.bibliographic?.groupitems
    const findBib = (code) => bib ? Object.values(bib).find(g => g.code === code) : null
    const subject = findBib('subject')
    const series  = findBib('series')
    const author  = findBib('author')
    let q = ''
    if (subject?.items) {
      const f = Object.values(subject.items)[0]?.text?.split(',')[0]?.trim()
      if (f) q = 'subject:"' + f + '"'
    }
    if (!q && series?.items) {
      const f = Object.values(series.items)[0]?.text?.trim()
      if (f) q = 'series:"' + f + '"'
    }
    if (!q && author?.items) {
      const f = Object.values(author.items)[0]?.text?.trim()
      if (f && f.includes(',')) {
        const ln = f.split(',')[0].trim()
        if (ln) q = 'author:"' + ln + '"'
      }
    }
    if (!q) { relatedLoading.value = false; return }
    const data = await api('search', {
      area:    src?.area || 'catalog',
      search:  { query: q, hits: 20, type: 'lukida' },
      display: { bib: true },
    })
    related.value = (data.data || [])
      .filter(d => d.id !== src?.id)
      .map(d => {
        const g  = d.bibliographic?.groupitems || {}
        const te = Object.values(g).find(e => e.code === 'title')
        const ae = Object.values(g).find(e => e.code === 'author')
        return { id: d.id, title: te ? Object.values(te.items)[0]?.text : d.id,
                 author: ae ? Object.values(ae.items)[0]?.text : '', year: d.year, area: d.area }
      })
      .slice(0, 15)
  } catch (e) {
    console.error(e)
  } finally {
    relatedLoading.value = false
  }
}

// -- Bibliographische Hilfsfunktionen ------------------------------------------
function getBibEntry(code) {
  const src = detail.value || props.item
  const bib = src?.bibliographic?.groupitems
  if (!bib) return null
  return Object.values(bib).find(g => g.code === code) || null
}
function getBibItems(code) {
  const entry = getBibEntry(code)
  if (!entry?.items) return ''
  return Object.values(entry.items).map(i => i.text).filter(Boolean).join('; ')
}
function getField(code) { return getBibItems(code) }
function getAuthors() {
  const entry = getBibEntry('author')
  if (!entry?.items) return []
  return Object.values(entry.items).map(i => i.text).filter(Boolean)
}
function makeBibGroups(codes) {
  const src = detail.value || props.item
  const bib = src?.bibliographic?.groupitems
  if (!bib) return []
  return Object.values(bib)
    .filter(g => g.code && g.items && codes.includes(g.code))
    .map(g => ({ code: g.code, label: LABELS[g.code] || g.code,
                 text: Object.values(g.items).map(i => i.text).filter(Boolean).join('; ') }))
    .filter(g => g.text)
    .sort((a, b) => codes.indexOf(a.code) - codes.indexOf(b.code))
}
const primaryGroups = computed(() => makeBibGroups(PRIMARY))
const detailGroups  = computed(() => {
  const src = detail.value || props.item
  const bib = src?.bibliographic?.groupitems
  if (!bib) return []
  return Object.values(bib)
    .filter(g => g.code && g.items && !PRIMARY.includes(g.code) && !HIDDEN.has(g.code))
    .map(g => ({ code: g.code, label: LABELS[g.code] || g.code,
                 text: Object.values(g.items).map(i => i.text).filter(Boolean).join('; ') }))
    .filter(g => g.text)
})

// -- Online-Links aus additionalinfo -------------------------------------------
const onlineLinks = computed(() => {
  const src = detail.value || props.item
  const bib = src?.bibliographic?.groupitems
  if (!bib) return []
  const entry = Object.values(bib).find(g => g.code === 'additionalinfo')
  if (!entry?.items) return []
  return Object.values(entry.items)
    .filter(i => i.action?.type === 'link' && i.action?.link?.url)
    .map(i => ({ label: i.text || 'Link öffnen', url: i.action.link.url }))
})

// -- Exemplare -----------------------------------------------------------------
const items = computed(() => {
  const raw = itemsData.value
  if (!raw || typeof raw !== 'object' || Array.isArray(raw)) return []
  const result = []
  for (const group of Object.values(raw)) {
    if (!Array.isArray(group.groupitems)) continue
    const groupLocation = group.text?.g?.[0]?.text || (group.label !== 'default' ? group.label : '') || ''
    for (const gi of group.groupitems) {
      // Beschriftung und Aktion kommen aus den Regeln (admincases/admincasesmedia)
      const callnumber = gi.meta?.signature?.[0] || ''
      const light      = gi.light || 'neutral'
      const url        = gi.action?.link?.url || ''
      const zeilen     = (gi.comments?.g || [])
        .map(c => String(c?.text || '').replace(/\*\*/g, '').replace(/^:\s*/, '').trim())
        .filter(Boolean)
      const statusText = gi.short?.label
        || zeilen.find(t => !t.startsWith('Signatur:') && !t.startsWith('Signature:'))
        || ''
      const badgeClass = light === 'green'  ? 'badge-success'
                       : light === 'red'    ? 'badge-error'
                       : light === 'yellow' ? 'badge-warning'
                       : light === 'blue'   ? 'badge-info' : 'badge-neutral'
      const akt  = (gi.action && !Array.isArray(gi.action)) ? gi.action : null
      const art  = akt?.type === 'request' ? 'request' : (url ? 'link' : '')
      result.push({
        location: groupLocation || gi.meta?.location?.[0] || '',
        subloc: '', callnumber, url, statusText, badgeClass,
        action: art,
        // short.label ist bereits übersetzt, action.button nur der Code
        knopf:  gi.short?.label || 'Bestellen',
        anfrage: akt?.request || null,
        regel:  gi.meta?.case || '',
      })
    }
  }
  return result
})

// Bestellen: Kennung und Art aus action.request, nur angemeldet
const bestellLauft = ref(false)
const bestellGrund = ref('')
async function bestellen() {
  const it = orderDialogItem.value
  if (!it || !it.anfrage || bestellLauft.value) return
  if (!props.user?.token || !props.user?.login) {
    bestellGrund.value = 'Bitte melden Sie sich zuerst an.'
    return
  }
  bestellLauft.value = true
  bestellGrund.value = ''
  try {
    const d = await api('action', {
      type:        'request',
      id:          it.anfrage.id,
      requesttype: it.anfrage.type || 'order',
      login:       props.user.login,
      tokens:      { user: props.user.token },
    })
    const ok = (d.status ?? -1) >= 0 || d.stat === 'success'
    bestellGrund.value = (Array.isArray(d.mess) ? d.mess.filter(Boolean).join(' ') : '')
      || (Array.isArray(d.more) ? d.more.filter(Boolean).join(' ') : '')
      || (ok ? 'Bestellung aufgegeben.' : 'Bestellung nicht möglich.')
    if (ok) setTimeout(() => bestellDialog.value?.close(), 2500)
  } catch {
    bestellGrund.value = 'Verbindungsfehler.'
  }
  bestellLauft.value = false
}
</script>
