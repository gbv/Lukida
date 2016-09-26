# Changelog

**26.09.2016**
1. Corrected "unknown"-DAIA-Date
2. MySQL Driver update
3. Resolved bug: Stored searches
4. Resolved bug: Mail from Adress
5. Login: Trim username & password

**21.09.2016**
1. Fullview Buttons dimmed until form is loaded
2. Journals Online & Print (JOP) Link Resolver improved

**19.09.2016**
1. Assistent-Search for author now searches as well Marc 700-fields
2. Bug Frontpage Search * solved
3. Link resolver switches corrected
4. Bug Exemple 980e=a solved

**15.09.2016**
1. Bug Interne Suche korrigiert

**09.09.2016**
1. Kleine Korrekturen an der Suche
2. Assistent auch für die Frontpage

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

