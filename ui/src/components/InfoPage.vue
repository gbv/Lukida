<template>
  <div class="container mx-auto px-4 py-10 max-w-3xl">
    <div class="alert alert-info mb-8 flex gap-3">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <div>
        <div class="font-semibold">Demo-Version</div>
        <div class="text-sm">Dies ist eine Demo-Installation von Lukida Light. Die Daten stammen aus dem K10Plus-Verbundkatalog und dienen ausschließlich zu Demonstrationszwecken.</div>
      </div>
    </div>

    <h1 class="text-2xl font-bold mb-2">Über dieses System</h1>
    <p class="opacity-70 mb-8 text-sm">Lukida Light · Demo-Instanz</p>

    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3">Allgemeines</h2>
      <p class="text-sm leading-relaxed opacity-80">
        Dieses Portal basiert auf <strong>Lukida Light</strong>, einem kompakten Such- und Rechercheportal
        für Bibliotheken. Mit Lukida finden Sie schnell und unkompliziert wissenschaftliche Literatur 
        egal ob Bücher, E-Medien, Zeitschriften, Aufsätze oder Datenbanken. Das System durchsucht den
        umfangreichen Datenbestand des K10Plus-Verbundkatalogs und zeigt Ihnen direkt verfügbare Zugänge.
        Treffer aus anderen Bibliotheken lassen sich bequem per Fernleihe bestellen.
      </p>
    </section>

    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3">Vorteile</h2>
      <ul class="text-sm space-y-2 opacity-80">
        <li class="flex gap-2"><span class="text-success font-bold">?</span> Einfache Suche für alle Medienarten</li>
        <li class="flex gap-2"><span class="text-success font-bold">?</span> Umfassender Zugriff auf Bücher, E-Medien, Aufsätze, Zeitschriften, Datenbanken</li>
        <li class="flex gap-2"><span class="text-success font-bold">?</span> Viele E-Ressourcen auch außerhalb des Campus nutzbar</li>
        <li class="flex gap-2"><span class="text-success font-bold">?</span> Gezielte Filtermöglichkeiten (Erscheinungsjahr, Dokumenttyp, Fachgebiet, Sprache)</li>
        <li class="flex gap-2"><span class="text-success font-bold">?</span> Favoriten und Suchverlauf direkt im Browser gespeichert</li>
        <li class="flex gap-2"><span class="text-success font-bold">?</span> Export in BibTeX, RIS/EndNote und Citavi</li>
      </ul>
    </section>

    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3">Inhalte</h2>
      <ul class="text-sm space-y-1 opacity-80 list-disc list-inside">
        <li>Gedruckte und digitale Bestände aus dem K10Plus-Verbund</li>
        <li>Lizenzierte E-Ressourcen (E-Books, E-Journals, Datenbanken)</li>
        <li>Frei zugängliche Open-Access-Inhalte</li>
        <li>Nachweise wissenschaftlicher Artikel aus Fachverlagen</li>
        <li>Bestände von über 400 Partnerbibliotheken (GBV-Verbund)</li>
      </ul>
    </section>

    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3">Benutzerkonto</h2>
      <p class="text-sm leading-relaxed opacity-80">
        Über den Menüpunkt <strong>Mein Konto"</strong> können Sie sich anmelden.
        Nach der Anmeldung sehen Sie Ihre aktuellen Entleihungen, abholbereiten Bestellungen,
        Vormerkungen und eventuelle Gebühren. Außerdem können Sie Leihfristen verlängern,
        Vormerkungen stornieren und Ihr Passwort ändern.
      </p>
    </section>

    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3">Technische Informationen</h2>
      <div class="overflow-x-auto">
        <table class="table table-sm text-sm">
          <tbody>
            <tr><td class="opacity-50 w-40">System</td><td>Lukida Light</td></tr>
            <tr><td class="opacity-50">Entwickler</td><td>VZG  Verbundzentrale des GBV, Göttingen</td></tr>
            <tr><td class="opacity-50">Datenbasis</td><td>K10Plus-Verbundkatalog</td></tr>
            <tr><td class="opacity-50">API</td><td>Lukida Essential API v5</td></tr>
            <tr><td class="opacity-50">Client</td><td>Demo</td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <section>
      <h2 class="text-lg font-semibold mb-3">Fragen oder Probleme?</h2>
      <p class="text-sm leading-relaxed opacity-80">
        Lukida wird von der VZG stetig weiterentwickelt. Sollten Ihnen Fehler auffallen oder
        Sie Fragen haben, wenden Sie sich bitte an Ihre Bibliothek oder an
        <a href="mailto:info@gbv.de" class="link link-primary">info@gbv.de</a>.
      </p>
    </section>
  </div>
</template>

<script setup></script>