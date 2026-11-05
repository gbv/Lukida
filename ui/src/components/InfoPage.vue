<template>
  <div class="container mx-auto px-4 py-10 max-w-3xl">
    <div class="alert alert-info mb-8 flex gap-3" role="note">
      <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <div>
        <div class="font-semibold">{{ APP_TITLE }}</div>
        <div class="text-sm">Dies ist eine Installation von Lukida Essential.</div>
      </div>
    </div>

    <h1 class="text-2xl font-bold mb-2">Über dieses System</h1>
    <p class="text-muted mb-8">{{ APP_TITLE }} · basiert auf Lukida Essential</p>

    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3">Allgemeines</h2>
      <p class="leading-relaxed">
        {{ APP_TITLE }} basiert auf <strong>Lukida Essential</strong>, der freien Open-Source-Version
        des Recherche-Portals Lukida für Bibliotheken. Sie finden damit schnell wissenschaftliche
        Literatur – Bücher, E-Medien, Zeitschriften und Aufsätze – und sehen direkt, wo ein Titel
        verfügbar ist.
      </p>
    </section>

    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3">Funktionen</h2>
      <ul class="space-y-2">
        <li v-for="f in FEATURES" :key="f" class="flex gap-2"><span aria-hidden="true" class="text-success font-bold">✓</span> {{ f }}</li>
      </ul>
    </section>

    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3">Benutzerkonto</h2>
      <p class="leading-relaxed">
        Über <strong>„Anmelden“</strong> melden Sie sich mit Ihrer Bibliotheksnummer an. Unter
        <strong>„Mein Konto“</strong> sehen Sie anschließend Ihre Ausleihen, abholbereiten Bestellungen,
        Vormerkungen und eventuelle Gebühren.
      </p>
    </section>

    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3">Technische Informationen</h2>
      <div class="overflow-x-auto">
        <table class="table">
          <tbody>
            <tr><th scope="row" class="font-normal text-muted w-40">System</th><td>Lukida Essential</td></tr>
            <tr><th scope="row" class="font-normal text-muted">API</th><td>Lukida Essential API v5</td></tr>
            <tr><th scope="row" class="font-normal text-muted">Client</th><td>{{ CLIENT }}</td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <section>
      <h2 class="text-lg font-semibold mb-3">Fragen oder Probleme?</h2>
      <p class="leading-relaxed">
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
