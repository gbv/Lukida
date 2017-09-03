# Changelog
**23.08.2017**
* Full view
  * ID / PPN added to bibliographic data
  * Siblings improved

**23.08.2017**
* Full view
  * Mobile Buttons adjusted

**17.08.2017**
* Library Module
    * New chart created: device resolutions

**09.08.2017**
* Actions
  * MailOrders will be written to a logfile

* Library Module
  * Add features logs to view and filter the logfile
  * Add search and filter components for log file

**31.07.2017**
* Settings
  * Load/Save Buttons only visible with configured lbs

* Updates
  * NoUISLider 10.1.0

**28.07.2017**
* Full view
  * Search, filter & sort in related medias improved

**25.07.2017**
* General
  * Comfirmed HTML Markup Validation by validator.w3.org
  * Cross-Browser-Tests

* Full view
  * Added cross links from print to online media and backwards
  
**19.07.2017**
* Optical
  * Minor corrections based on new layout

* Full view
  * Search, filter & sort in related medias

* Link Resolver
  * Interfaces improved  

**27.06.2017**
* General
  * .htaccess removed, rewrite lines added to VirtualHost
  * Mobile devices: Focus on search field removed
  * Mobile devices: New layout
  * Usage of URL with facettes improved

**23.06.2017**
* Updates
  * NoUISLider 10.0.0
  * Chart.js 2.6.0

**22.06.2017**
* General
  * https support recognized by HTTP_X_FORWARDED_PROTO variable

**20.06.2017**
* Updates
  * Code Igniter 3.1.5

**16.06.2017**
* Interfaces
  * PAIA2_DAIA2 supports response storage
  * MySQL user settings storage with hashed userid

**06.06.2017**
* Resolved bugs
  * Search based on ID using shards

**30.05.2017**
* Search
  * Improved word suggestions
  * JSON-LD updated

**26.05.2017**
* Search
  * Access collection / collection_details

* Full view
  * Original language / characters added

* General
  * Prevent unauthorized ini access

**20.05.2017**
* Search
  * Storing and loading basic search added
  
**16.05.2017**
* Search
  * Yearrange adjusted

**11.05.2017**
* General
  * Search options group togehter and moved to toggable option area
  * New navigation bar layout
  * New material switches
  * Improved usage for mobile devices
  
* Search
  * Two phased search added
  * Phonetic search added
  * Added sharding support

* Full view
  * Language code only taken from M041a (M040b removed)
  * Original language added
  * Multiple languages added

* Link Resolver
  * Journals Online & Print (JOP) interface improved

* Updates
  * Editable 1.5.1 added

**12.04.2017**
* Updates
  * jQuery 3.2.1
  * Daterangepicker correctly named & minimized

* General
  * Readme update

* Link Resolver
  * Improved JOP linking

**11.04.2017**
* Cleanup
  * Database configuration moved from kernel to library/general.ini

**10.04.2017**
* Updates
  * jQuery 3.2.0
  * Dialog 1.35.4
  * Platform 1.3.4
  * Moment 2.18.1
  
* Full view
  * Removed wrong ISMNs by checking indicators
  * MARC view enhanced by indicators

* Cleanup
  * Removed pear folder
    (Pear class File_MARC is still required and will be accessed from php central pear repository)

**24.03.2017**
* User module
  * User stored search removed (will be replaced by new automatic search module)

* Full view
  * Added ISMN

* General
  * Added QR-Code module to kernel
  
* Assistant
  * Optimized for smaller displays

**22.03.2017**
* Search
  * Enhanced Mouse usage

**21.03.2017**
* Search
  * Direct search - even if keyword suggestion window is open

* Updates
  * CodeIgniter 3.1.4

**20.03.2017**
Library extensions
* Added qrcode permanent link
* Added OpenStreetMap

**15.03.2017**
* Resolved bugs
  * URL update during fullview scrolling

**14.03.2017**
* General
  * New url transports layout columns, speech & facets filter

**09.03.2017**
* User module
  * Fee view updated

* General
  * URL has new syntax (shortened)
  * Permanet URL is shown on fullview

* Resolved bugs
  * Reservations & Orders error messages

**07.03.2017**
* User module
  * Improved MaxRenewals with paia1&2 drivers
  * New Library switch for renewals after end of period

* Internal
  * Added driver- and hostname to DAIA and PAIA page.

* Search
  * Included media optimized

* Resolved Bugs
  * Improved link to upper media

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

