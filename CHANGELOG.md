# Changelog
**01.03.2017**
* Search
  * Improved search for terme containing special characteds like -,#, etc.
  * Better simular publications

**28.02.2017**
* New user module
  * User rentals redesigned (new layout, multiple renews)
  * Separate collectables tab
  * Integrated Change Password

**24.02.2017**
* Search
  * Keyword suggestion without AutoSelect

* Updates
  * Chart.js 2.5.0
  * Typeahead 4.0.2

**15.02.2017**
* Link Resolver
  * Resolved links will be refreshed every 3 months

* Simular pubs 
  * Can be switched off (display & search)

* MARC
  * Fullmarc Command added, after new search will display full marc record

* Frontpage search
  * Special characters have been removed

* Resolved bugs
  * Year period repaired 
  
**13.02.2017**
* Resolved bugs
  * Datapool facet is translated 

**10.02.2017**
* Link Resolver
  * Already cleared links will be displayed directly when fullview is opened

**08.02.2017**
* Resolved bugs
  * Frontpage trim searched values
  * Local class seach corrected

* Cleanup
  * Removed CSS file from ini

**04.02.2017**
* New Library Module

**03.02.2017**
* Library Module
    * Two new charts created
  
**28.01.2017**
* Search
  * Text marks also available for searches phrases less than 3 characters
  
* Resolved bugs
  * Search phrases like 'und' do not scamble output anymore
  * Searches with colon character enabled

**26.01.2017**
* Configuration & Cleanup
  * Multiple Modules enabled with dynamic loaded libraries
  * Producer.ini removed
  * bootstrap-theme.min.css removed
  * Config.ini enhanced

**23.01.2017**
* Mail
  * Now supporting multiple mails per library containing different subjects & adresses

**20.01.2017**
* Configuration
  * System reinitialized after timeout
  * Sitelinks Searchbox added

* Updates
  * NoUISLider 9.2.0

* Search
  * Class-Searches limited to local classes (library classes)

**18.01.2017**
* Configuration
  * Added "Drop Table If Exists" entries in MySQL-Import-Scripts

* Resolved bugs
  * Added Auto_Increment to database tables
  * Solr Driver reads correct index_system

**13.01.2017**
* Interfaces
    * New PAIA2_DAIA2 driver

* System
    * Cleanup development mode

* Resolved bugs
    * User reservations

**11.01.2017**
* System
    * Cleanup database

**10.01.2017**
* Updates
    * CodeIgniter 3.1.3

* System
    * Interfaces removed
    * Vagrant removed

**09.01.2017**
* Search
    * Marking of searched text more flexible
    * Focus on buttons is removed after click 

* PAIA
    * Login form now closeable by pressing enter
    * Readable PAIA tab page 

* Updates
    * Platform 1.3.3

* Resolved bugs
    * Internal commands 

**13.12.2016**
* Updates
    * NoUISLider 9.1.0

**09.12.2016**
* System
    * Memory usage reduced

**06.12.2016**
* Search
    * Support for GBV discovery added

**04.12.2016**
* Resolved bugs
    * English Volltext-Button added - full text

**31.11.2016**
* Recommendation mails
    * Added english language
* Flexibility
    * Added possibility for library specific php files
* Resolved bugs
    * System reinitialized after session timeout
    * Message displayed after mail recommendation

**15.11.2016**
* New Lukida Version with three elements x.y.z and without leading zeros
    * x Lukida Main Version
    * y Lukida Sub Version (new!)
    * z Lukida Library Version
* Search
    * Increased performance
    * Auto Grid Systems now supports 3 columns again
* Compatibility
    * IOs Safari support 
    * Added robots.txt
    * Updated .htaccess
* Link Resolver
    * Increased performance 
    * Added database storage
* Resolved bugs
    * URL shortened
    * Link resolver called more often
* Database
    * Added table link_resolver_library

**31.10.2016**
* Updates
    * CodeIgniter 3.1.2
* Resolved bugs
    * Yearrange dragable
    * Frontpage-Assistant corrected
* Search
    * Automatically added a space after a colon ',' -> ', '

**25.10.2016**
* Cleanup
    * Removed Theme-Cookie 
    * Removed Theme Button
    * Removed Bootstrap Themes
* Search
    * Added dynamic datapool (see discover.ini)
* Updates
    * CodeIgniter 3.1.1
    * FontAwesome 4.7.0

**17.10.2016**
* Stats
    * Old Table Search_Log redesigned to stats_monthly_library
    * Now Search phrases are stored on a daily basis.
    * Facets clicks are now counted as well
* Updates
    * NoUISlider 9.0.0
* Scripts
    * MySQL Import script changed to compact mode

**30.09.2016**
* Assistant
    * Title search improved
    * Year search improved
    * ISN search improved

**29.09.2016**
* Assistant
    * Quotations marks added
    * Spaces removed
* Search
    * Added quotation marks
    * Series search improved
* Updates
    * jQuery 3.1.1
    * Dialog 1.35.3

**28.09.2016**
* New features
    * Speedy LinkResolver added
* Stats
    * External Links added
    * Clientsize / OS added
    * Yearly Stat added
* Resolved bugs
    * Automatic search removed when starting the assistant

###V41

**26.09.2016**
* Updates
    * MySQL Driver  - Improved speed
    * LBS Driver    - Corrected "unknown"-DAIA-Date* 
* Resolved bugs
    * Stored searches
    * Mail from Adress
    * Trim username & password during login

**21.09.2016**
* New features
    * Fullview Buttons dimmed until form is loaded
    * Journals Online & Print (JOP) Link Resolver improved

**19.09.2016**
* New features
    * Assistent-Search for author now searches as well Marc 700-fields
* Resolved Bugs
    * Bug Frontpage Search * solved
    * Link resolver switches corrected
    * Bug Exemple 980e=a solved

**15.09.2016**
* Resolved Bugs
    * Internal search improved

**09.09.2016**
* New features
    * Search improvements
    * Assistent also available for frontpage

###V 40
1. Erweiterte Such-Logik Paket 1
Die Suchmöglichkeiten aufgrund von Solr-Datenbanken bzw. dem GBV Zentral sind immens. Mit Lukida 40 wird dies nun erweitert. Zusätzlich zu den bisherigen Suchen, die unverändert sind, gibt es nun zusätzlich die Möglichkeit Gruppen-Suchen durchzuführen. Die Syntax dafür ist: <Gruppe>(Wert1, Wert2)
Beispiel:  title(Grimm's Märchen,Gulliver's Reisen)
Somit werden innerhalb der Gruppe ODER-Verknüpfungen gebildet. Somit ist es machbar nach mehreren Autoren, oder Sachgebieten zu suchen. Sie kannten bereits die die Gruppensuche für IDs / PPNs.
Beispiel:  id(22054137X, 254789293, 340176911)
Dies funktioniert für alle bisher bekannten Gruppen.

2. Erweiterte Such-Logik Paket 2
Sie können nun Gruppen mit einander kombinieren. Dabei wird eine UND-Verknüpfung ausgelöst.
Beispiel:  title(Grimm's Märchen,Gulliver's Reisen)  author(Grimm,Swift,Solms)
Wir haben dabei versucht, eine möglichst einfache Syntax zu verwenden. Anstelle der englischen Gruppenbezeichnungen sind auch deutsche Gruppen möglich:
Beispiel:  Verlag(Rowohlt)  Jahr(1978, 2011)
Nun ist dies für die Anwender ggf. nicht so einfach solche Gruppenbezeichnungen sich auszudenken. Daher gibt es einen neuen „Assistenten“.

3. Neuer Assistent
Der Assistent wird über den Button rechts von der Lupe aufgerufen und ist nichts anderes als eine Erfassungshilfe für den Nutzer.
Hier kann der Nutzer in mehreren Gruppen-Reitern jeweils pro Zeile einen Suchbegriff erfassen. Nach Auslösen einer Suche wird der Assistent offen gelassen und die Treffermenge angezeigt. Wir haben dies als Recherche-Oberfläche konzipiert, so dass nun der Nutzer ggf. seine Suche noch präzisieren oder erweitern kann, bevor er sich der Treffer-Menge widmet.
Hier sind wir sehr auf Ihre Reaktionen gespannt. Evtl. finden Sie es sinnvoller sofort zu den Treffern zu gelangen und sonst einfach den Assistenten wieder zu öffnen.
Der Assistent wurde für Sie alle aktiviert. Bitte melden Sie sich, wenn Sie diesen nicht benötigen oder Verbesserungsvorschläge haben.

4. Neuer URL-Aufbau
Aufgrund der neuen Suchmöglichkeiten und der Browser-Kompatibilität mussten wir den Aufbau der Adressen für Favoriten leicht anpassen. Evtl. vorhandene alte Favoriten könnten damit nicht mehr funktionieren.

5. Statistik-Modul (Teil 1 von 2)
Fortan werden die Benutzer-Aktivitäten anonym gezählt. Es geht darum, Ihnen in einem der nächsten Pakete eine Statistikoberfläche anzubieten, in der Sie beispielsweise sehen können:
•	Wie viele Suchen werden pro Tag ausgeführt
•	Wie oft wird die Spaltenanzahl 2 (usw.) verwendet
•	Wie oft wird die Sprache Englisch (Deutsch) verwendet
•	Usw.
Die Daten werden pro Stunde gezählt, so dass auch chronologische Betrachtungen pro Tag möglich sind. Es werden nur Aktivitäten gezählt.
Der Statistik wurde für Sie alle aktiviert. Bitte melden Sie sich, wenn Sie diese nicht benötigen oder Verbesserungsvorschläge haben.

6) Zotero-Unterstützung
Die Unterstützung von Zotero-Plugins wurde eingebaut.

7. Dynamische Buttons
In der großen Kachel (FullView) können die Buttons nach Ihren Wünschen angezeigt werden.
Der Mail-Button erscheint nur, wenn ein LBS-System konfiguriert wurde und zur Spam-Protection eingesetzt werden kann. 

8. Druck
Ein neuer Druck-Button steht Ihnen allen zur Verfügung. Er ermöglicht das Ausdrucken der bibliografischen Daten eines Werkes. Der Button steht auch in der Merkliste zur Verfügung kann somit auch für das Ausdrucker der Merkliste verwendet werden.

###V 39
1. W3C Konform
Seit Version 039 ist Lukida W3C () konform. Hier können Sie dies selber überprüfen und gerne auch mal andere Webseiten zum Vergleich heranziehen 

