<template>
  <div class="container mx-auto px-4 py-10 max-w-3xl">
    <div class="alert alert-info mb-8 flex gap-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <div>
        <div class="font-semibold">{{ APP_TITLE }}</div>
        <div class="text-sm">Dies ist eine Installation von Lukida Essential. Die Daten stammen aus dem K10plus-Verbundkatalog.</div>
      </div>
    </div>

    <h1 class="text-2xl font-bold mb-2">Über dieses System</h1>
    <p class="opacity-70 mb-8 text-sm">{{ APP_TITLE }} · basiert auf Lukida Essential</p>

    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3">Allgemeines</h2>
      <p class="text-sm leading-relaxed opacity-80">
        {{ APP_TITLE }} basiert auf <strong>Lukida Essential</strong>, der freien Open-Source-Version
        des Recherche-Portals Lukida für Bibliotheken. Sie finden damit schnell wissenschaftliche
        Literatur – Bücher, E-Medien, Zeitschriften und Aufsätze – aus dem Datenbestand des
        K10plus-Verbundkatalogs und sehen direkt, wo ein Titel verfügbar ist.
      </p>
    </section>

    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3">Funktionen</h2>
      <ul class="text-sm space-y-2 opacity-80">
        <li v-for="f in FEATURES" :key="f" class="flex gap-2"><span class="text-success font-bold">✓</span> {{ f }}</li>
      </ul>
    </section>

    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3">Benutzerkonto</h2>
      <p class="text-sm leading-relaxed opacity-80">
        Über <strong>„Anmelden“</strong> melden Sie sich mit Ihrer Bibliotheksnummer an. Unter
        <strong>„Mein Konto“</strong> sehen Sie anschließend Ihre Ausleihen, abholbereiten Bestellungen,
        Vormerkungen und eventuelle Gebühren.
      </p>
    </section>

    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3">Technische Informationen</h2>
      <div class="overflow-x-auto">
        <table class="table table-sm text-sm">
          <tbody>
            <tr><td class="opacity-50 w-40">System</td><td>Lukida Essential</td></tr>
            <tr><td class="opacity-50">Entwickler</td><td>VZG – Verbundzentrale des GBV, Göttingen</td></tr>
            <tr><td class="opacity-50">Datenbasis</td><td>K10plus-Verbundkatalog</td></tr>
            <tr><td class="opacity-50">API</td><td>Lukida Essential API v5</td></tr>
            <tr><td class="opacity-50">Client</td><td>{{ CLIENT }}</td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <section>
      <h2 class="text-lg font-semibold mb-3">Fragen oder Probleme?</h2>
      <p class="text-sm leading-relaxed opacity-80">
        Bei Fragen zu Beständen, Ausleihe oder Ihrem Konto wenden Sie sich bitte an Ihre Bibliothek.
      </p>
    </section>
  </div>
</template>

<script setup>
import { APP_TITLE, CLIENT } from '../api.js'

const FEATURES = [
  'Einfache Suche über alle Medienarten',
  'Bücher, E-Medien, Aufsätze und Zeitschriften in einer Trefferliste',
  'Standorte, Signaturen und Verfügbarkeit der Exemplare',
  'Favoriten und Suchverlauf, gespeichert nur in Ihrem Browser',
  'Export in BibTeX, RIS (Citavi, Zotero) und EndNote – einzeln oder alle Favoriten',
]
</script>
