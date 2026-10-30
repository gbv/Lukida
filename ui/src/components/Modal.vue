<template>
  <div v-if="item" class="modal modal-open z-50">
    <div class="modal-box max-w-2xl max-h-[90vh] flex flex-col p-0 overflow-hidden">
      <div class="flex items-start gap-4 p-5 pb-3">
        <div class="shrink-0 w-20 h-28 bg-base-300 rounded flex items-center justify-center text-xs opacity-40">Kein Bild</div>
        <div class="flex-1 min-w-0">
          <div class="flex justify-between items-start gap-2">
            <h3 class="font-bold text-base leading-snug">{{ getField('title') || '(kein Titel)' }}</h3>
            <div class="flex gap-1 shrink-0">
              <button class="btn btn-sm btn-circle btn-ghost" :title="isFav ? 'Aus Favoriten entfernen' : 'Zu Favoriten hinzufügen'" @click="toggleFav">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :fill="isFav ? 'currentColor' : 'none'" :class="isFav ? 'text-warning' : ''" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
              </button>
              <button class="btn btn-sm btn-circle btn-ghost" @click="$emit('close')">&#x2715;</button>
            </div>
          </div>
          <div v-if="getField('band')" class="text-sm opacity-60 mt-0.5">{{ getField('band') }}</div>
          <div v-if="getField('in')" class="text-sm opacity-60 mt-0.5">Enthalten in: {{ getField('in') }}</div>
          <div class="text-sm opacity-70 mt-1 flex flex-wrap gap-x-4">
            <span v-for="a in getAuthors()" :key="a">{{ a }}</span>
          </div>
          <div class="text-xs opacity-50 mt-2 flex items-center gap-2">
            <span v-if="item.year">{{ item.year }}</span>
            <span v-if="item.format && item.format !== 'unknown'">&#183; {{ item.format }}</span>
          </div>
        </div>
      </div>

      <div class="tabs tabs-bordered px-5">
        <button class="tab" :class="{ 'tab-active': tab === 'titel' }" @click="tab = 'titel'">TITEL</button>
        <button class="tab" :class="{ 'tab-active': tab === 'zugeh' }" @click="switchToZugeh">ZUGEH&#214;RIG</button>
      </div>

      <div class="overflow-y-auto flex-1 px-5 py-4">
        <div v-if="loading" class="flex justify-center py-8">
          <span class="loading loading-spinner loading-lg text-primary"></span>
        </div>

        <div v-else-if="tab === 'titel'">
          <div class="space-y-0">
            <div v-for="group in primaryGroups" :key="group.code" class="flex gap-3 text-sm py-2 border-b border-base-200">
              <span class="opacity-50 w-36 shrink-0">{{ group.label }}</span>
              <span class="flex-1">{{ group.text }}</span>
            </div>
          </div>

          <div class="mt-2">
            <button class="flex items-center gap-1 text-sm font-medium py-2" @click="showDetails = !showDetails">
              Details
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': showDetails }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div v-if="showDetails" class="space-y-0 border-t border-base-200">
              <div v-for="group in detailGroups" :key="group.code" class="flex gap-3 text-sm py-2 border-b border-base-200">
                <span class="opacity-50 w-36 shrink-0">{{ group.label }}</span>
                <span class="flex-1">{{ group.text }}</span>
              </div>
            </div>
          </div>

          <!-- Online-Links aus additionalinfo -->
          <div v-if="onlineLinks.length" class="mt-3 flex flex-wrap gap-2">
            <a v-for="(lnk, i) in onlineLinks" :key="i" :href="lnk.url" target="_blank"
               class="btn btn-xs text-white" style="background:#7da820;">
              {{ lnk.label }}
            </a>
          </div>

          <!-- Exemplare -->
          <div v-if="items.length" class="mt-4 border-t border-base-200 pt-3">
            <div class="text-xs font-semibold uppercase opacity-40 mb-2 tracking-wide">Exemplare</div>
            <div v-for="(it, i) in items" :key="i" class="flex gap-3 text-sm py-2 border-b border-base-200 items-start">
              <div class="flex-1 min-w-0">
                <div class="font-medium text-sm">{{ it.location }}</div>
                <div v-if="it.subloc" class="text-xs opacity-50 mt-0.5">{{ it.subloc }}</div>
                <div class="text-xs opacity-60 mt-0.5">{{ it.callnumber }}</div>
              </div>
              <div class="shrink-0 flex flex-col items-end gap-1">
                <!-- Abzeichen und Knopf kommen beide aus short.label - deshalb
                     das Abzeichen nur dort, wo es KEINEN Knopf gibt; sonst
                     stuende zweimal dasselbe Wort (29.10.2026). -->
                <span v-if="!it.action" class="badge badge-sm" :class="it.badgeClass">{{ it.statusText }}</span>
                <a v-if="it.action === 'link'" :href="it.url" target="_blank"
                   class="btn btn-xs text-white" style="background:#7da820;">LINK &#214;FFNEN</a>
                <button v-else-if="it.action === 'request'" @click="orderDialogItem = it; bestellGrund = ''"
                        class="btn btn-xs btn-primary">{{ it.knopf }}</button>
              </div>
            </div>
          </div>
        </div>

        <!-- ZUGEHÖRIG-Tab -->
        <div v-else-if="tab === 'zugeh'">
          <div v-if="relatedLoading" class="flex justify-center py-8">
            <span class="loading loading-spinner loading-lg text-primary"></span>
          </div>
          <div v-else-if="related.length === 0" class="text-sm opacity-40 text-center py-8">Keine zugeh&#246;rigen Titel gefunden</div>
          <div v-else class="space-y-0">
            <div v-for="r in related" :key="r.id"
                 class="flex items-start gap-3 py-3 border-b border-base-200 hover:bg-base-100 cursor-pointer"
                 @click="$emit('openItem', r)">
              <div class="shrink-0 w-10 h-14 bg-base-300 rounded"></div>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-medium leading-snug line-clamp-2">{{ r.title }}</div>
                <div class="text-xs opacity-50 mt-0.5">{{ r.author }}<span v-if="r.year"> &#183; {{ r.year }}</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-between px-4 py-3 border-t border-base-200">
        <div class="flex gap-1">
          <button class="btn btn-sm btn-circle btn-ghost" :disabled="!hasPrev" @click="$emit('navigate', -1)">&#8249;</button>
          <button class="btn btn-sm btn-circle btn-ghost" :disabled="!hasNext" @click="$emit('navigate', 1)">&#8250;</button>
        </div>
        <div class="flex items-center gap-2">
          <ExportMenu :ids="[item.id]" />
          <button class="btn btn-sm" @click="$emit('close')">OK</button>
        </div>
      </div>
    </div>

    <!-- Bestellen-Dialog -->
    <div v-if="orderDialogItem" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40" @click.self="orderDialogItem = null">
      <div class="bg-base-100 rounded-lg shadow-xl p-6 w-80 max-w-full">
        <div class="font-semibold text-base mb-3">{{ orderDialogItem.knopf }}</div>
        <div v-if="orderDialogItem.location" class="text-sm mb-1 font-medium">{{ orderDialogItem.location }}</div>
        <div v-if="orderDialogItem.callnumber" class="text-sm mb-1">Signatur: {{ orderDialogItem.callnumber }}</div>
        <div v-if="!user" class="text-sm opacity-70 my-3">Zum Bestellen müssen Sie angemeldet sein.</div>
        <div v-if="bestellGrund" class="text-sm my-3">{{ bestellGrund }}</div>
        <div class="flex justify-end gap-2 mt-4">
          <button class="btn btn-sm btn-ghost" @click="orderDialogItem = null">Abbrechen</button>
          <button class="btn btn-sm btn-primary" :disabled="!user || bestellLauft" @click="bestellen">
            <span v-if="bestellLauft" class="loading loading-spinner loading-xs"></span>
            {{ orderDialogItem.knopf }}
          </button>
        </div>
      </div>
    </div>

    <div class="modal-backdrop" @click="$emit('close')"></div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { api } from '../api.js'
import { isFavorite, toggleFavorite } from '../favorites.js'
import ExportMenu from './ExportMenu.vue'

const props = defineProps(['item', 'hasPrev', 'hasNext', 'user'])
defineEmits(['close', 'navigate', 'openItem'])

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
  try {
    const data     = await api('search', {
      area:    item.area || 'findex',
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
      area:    src?.area || 'findex',
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
      // BESCHRIFTUNG UND AKTION KOMMEN AUS DER REGEL         (29.10.2026)
      //
      // Hier wurde der Knopf frueher aus der AMPELFARBE geraten (gruen =
      // ausleihen, rot = bestellen) und oeffnete einen Hinweistext ohne
      // Funktion. Was an einem Exemplar moeglich ist, entscheiden aber
      // admincases/admincasesmedia: die API legt die Beschriftung in
      // short.label und die Aktion in action ab (p.order -> type
      // "request"). Genau das wird jetzt angezeigt und ausgefuehrt.
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
        // short.label ist die AUFGELOESTE Beschriftung aus admintexts;
        // action.button enthaelt nur den Code (z.B. "detailbutton.order"),
        // den die API nicht aufloest. Darum short zuerst (29.10.2026).
        knopf:  gi.short?.label || 'Bestellen',
        anfrage: akt?.request || null,
        regel:  gi.meta?.case || '',
      })
    }
  }
  return result
})

// Bestellen aus der Vollanzeige.
//
// Die Exemplarkennung und die Bestellart stehen in action.request, das die
// API aus der Regel gebaut hat (p.order -> {id, type:"order"}). Ohne
// Anmeldung geht nichts - dann sagt der Dialog das und mehr nicht.
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
    if (ok) setTimeout(() => { orderDialogItem.value = null; bestellGrund.value = '' }, 2500)
  } catch {
    bestellGrund.value = 'Verbindungsfehler.'
  }
  bestellLauft.value = false
}
</script>
