
-- --------------------------------------------------------
-- adminbms
-- --------------------------------------------------------
INSERT INTO "user".adminbms (id, client, bmsid, active, testonly, namecode, type, isil, iln, apitest, apiprod, options, logins) VALUES
(1, 'Demo', 1, 'y', 'n', 'level.start', 'paia', '<isil>', '<iln>',
 '<paia-test-url>', '<paia-prod-url>',
 '{"db":"1","passuser":"1000","passbes":"1","testopaclink":"https:\\/\\/<paia-test-host>","prodopaclink":"https:\\/\\/<paia-prod-host>"}',
 '[{"user":"<testuser>","pass":"<testpass>","comm":"Standard"}]');

-- --------------------------------------------------------
-- adminconfigs
-- --------------------------------------------------------
INSERT INTO "user".adminconfigs (id, client, category, setting, value) VALUES
(7,  'Demo',      'solr',    'port',            '80'),
(8,  'Demo',      'solr',    'wt',              'json'),
(9,  'Demo',      'solr',    'host',            '<solr-host>'),
(10, 'Demo',      'solr',    'path',            '<solr-path>'),
(11, 'Demo',      'solr',    'prot',            'https://'),
(12, 'Demo',      'item',    'ilnlist',         '<iln>'),
(13, 'Demo',      'display', 'items',           'y'),
(14, 'Demo',      'item',    'commentblacklist', NULL);

-- --------------------------------------------------------
-- adminlevels
-- --------------------------------------------------------
INSERT INTO "user".adminlevels (id, client, level, collectionsaddfull, collectionsadd, collectionsrem, holdingilns, holdingisils, holdingshaptic, locations, classlocals, buildings, dois, ipranges) VALUES
(2, 'Demo',      'all', '[]', '[]', '[]', '<gbv-iln>', '', '', '', '', '', '', '[]');

-- --------------------------------------------------------
-- admincases
-- --------------------------------------------------------
INSERT INTO "user".admincases (id, client, seq, name, active, filters, light, itemcomments, itembutton, itemicon, detailwindow, detailinfos, detailbutton, detailaction, detailaction5, ids, comment) VALUES
(158755,'Demo',100,'MARC Online - MARC OpenAccess','y','["m.online","m.openaccess"]','lime','["m.comment","t.freelyavailable","t.openinanewtab"]','itembutton.directopen','mdi-link','n','[]',NULL,'m.openaccess','m.openaccess',NULL,NULL),
(158756,'Demo',110,'MARC Online - Eigene Kollektion','y','["m.online","m.owncollection"]','lime','["m.comment","t.freelyavailable","t.openinanewtab"]','itembutton.directopen','mdi-link','n','[]',NULL,'m.openaccess','m.openaccess',NULL,NULL),
(158757,'Demo',120,'MARC Online - MARC Besitznachweis - MARC LokalLink','y','["m.online","m.owner","m.locallink"]','lime','["m.comment","t.freelyavailable","t.openinanewtab"]','itembutton.directopen','mdi-link','n','[]',NULL,'m.openaccess','m.openaccess',NULL,NULL),
(158735,'Demo',130,'MARC Online - Eigene IP','y','["m.online","o.ip"]','lime','["m.comment","t.freelyavailable","t.openinanewtab"]','itembutton.directopen','mdi-link','n','[]',NULL,'m.openaccess','m.openaccess',NULL,NULL),
(158729,'Demo',150,'MARC Online - MARC Remote','y','["m.online","m.remote"]','amber','["m.comment","t.chargepossible","t.openinanewtab"]','itembutton.directopen','mdi-link','n','[]',NULL,'m.remote','m.remote',NULL,NULL),
(158884,'Demo',300,'MARC Haptisch - PAIA Keine Vormerkung Option 1 - Info','y','["m.physical","p.expected","p.queuefull"]','red','["p.signature","p.about"]','itembutton.availablesoon','mdi-clock-outline','y','["p.signature","p.about","p.storage","p.chronology","t.maximumreservationsreached"]',NULL,NULL,NULL,NULL,NULL),
(158731,'Demo',310,'MARC Haptisch - PAIA Keine Vormerkung Option 2 - Anschaffungsvorschlag','n','["m.physical","p.expected","p.queuefull"]','orange','["p.signature","p.about"]','itembutton.suggest','mdi-bookshelf','y','["p.signature","p.about","p.storage","p.chronology","t.maximumreservationsreached","t.suggestaquisition"]','detailbutton.suggest','o.mailsuggest','o.dialog.suggest',NULL,NULL),
(158732,'Demo',320,'MARC Haptisch - PAIA Keine Vormerkung Option 3 - Mailvormerkung','n','["m.physical","p.expected","p.queuefull"]','orange','["p.signature","p.about"]','itembutton.reserve','mdi-clock-time-eight-outline','y','["p.signature","p.about","p.storage","p.chronology","t.orderedorinuse","t.reserveitem"]','detailbutton.reserve','o.mailreserve','o.dialog.reserve',NULL,NULL),
(158733,'Demo',330,'MARC Haptisch - PAIA Vormerkung','y','["m.physical","p.expected"]','orange','["p.signature","p.about","p.expected","p.queue"]','itembutton.reserve','mdi-clock-time-eight-outline','y','["p.signature","p.about","p.storage","p.chronology","p.expected","p.queue","t.reserveitem"]','detailbutton.reserve','p.reserve','p.reserve',NULL,NULL),
(158758,'Demo',340,'MARC Haptisch - PAIA Magazin Präsenz Bestellung an die Theke','y','["m.physical","p.pres","p.action","p.noloan"]','green','["p.signature","p.about","t.useonlyinlibrary"]','itembutton.order','mdi-arrow-right-bold-box-outline','y','["p.signature","p.about","p.storage","p.chronology","t.order2desk","t.useonlyinlibrary"]','detailbutton.order','p.order','p.order',NULL,NULL),
(158734,'Demo',350,'MARC Haptisch - PAIA Magazin ausleihbar Bestellung an die Theke','y','["m.physical","p.action","p.loan"]','green','["p.signature","p.about","t.lendable"]','itembutton.order','mdi-arrow-right-bold-box-outline','y','["p.signature","p.about","p.storage","p.chronology","t.order2desk","t.lendable"]','detailbutton.order','p.order','p.order',NULL,NULL),
(158862,'Demo',400,'MARC Haptisch - PAIA Freihand Präsenz','y','["m.physical","p.pres","p.noaction","p.noloan"]','green','["p.signature","p.about","t.useonlyinlibrary"]','itembutton.see','mdi-eye-outline','y','["p.signature","p.about","p.storage","p.chronology","t.pleasetakeitemfromshelf","t.useonlyinlibrary"]',NULL,NULL,NULL,NULL,NULL),
(158874,'Demo',410,'MARC Haptisch - PAIA Freihand ausleihbar','y','["m.physical","p.pres","p.noaction","p.loan"]','green','["p.signature","p.about","t.lendable"]','itembutton.seeloan','mdi-eye-outline','y','["p.signature","p.about","p.storage","p.chronology","t.pleasetakeitemfromshelf","t.lendable"]',NULL,NULL,NULL,NULL,NULL),
(158876,'Demo',500,'MARC Haptisch - Bestellt (ALI = o)','y','["m.physical","m.owner","m.ali.o"]','red','["m.signature","m.comment","m.stock","t.ordered"]','itembutton.availablesoon','mdi-clock-outline','y','["m.signature","m.comment","t.ordered"]',NULL,NULL,NULL,NULL,NULL),
(158877,'Demo',510,'MARC Haptisch - Geschäftsgang (ALI = a)','y','["m.physical","m.owner","m.ali.a"]','red','["m.signature","m.comment","m.stock","t.inprogress"]','itembutton.availablesoon','mdi-clock-outline','y','["m.signature","m.comment","t.inprogress"]',NULL,NULL,NULL,NULL,NULL),
(158863,'Demo',520,'MARC Haptisch - Verlust (ALI = z)','y','["m.physical","m.owner","m.ali.z"]','red','["m.signature","m.comment","m.stock","t.lost"]','itembutton.lost','mdi-clock-outline','y','["m.signature","m.comment","t.lost"]',NULL,NULL,NULL,NULL,NULL),
(158878,'Demo',530,'MARC Haptisch - Gesperrt (ALI = g)','y','["m.physical","m.owner","m.ali.g"]','red','["m.signature","m.comment","m.stock","t.locked"]','itembutton.locked','mdi-clock-outline','y','["m.signature","m.comment","t.locked"]',NULL,NULL,NULL,NULL,NULL),
(158879,'Demo',540,'MARC Haptisch - Ausleihbar (ALI = u)','y','["m.physical","m.owner","m.ali.u"]','green','["m.signature","m.comment","m.stock","t.loanable"]','itembutton.see','mdi-eye-outline','y','["m.signature","m.comment","t.loanable"]',NULL,NULL,NULL,NULL,NULL),
(158880,'Demo',550,'MARC Haptisch - Verkürzt ausleihbar (ALI = b)','y','["m.physical","m.owner","m.ali.b"]','green','["m.signature","m.comment","m.stock","t.shortloan"]','itembutton.see','mdi-eye-outline','y','["m.signature","m.comment","t.shortloan"]',NULL,NULL,NULL,NULL,NULL),
(158881,'Demo',560,'MARC Haptisch - Ausleihbar (ALI = c)','y','["m.physical","m.owner","m.ali.c"]','green','["m.signature","m.comment","m.stock","t.loanable"]','itembutton.see','mdi-eye-outline','y','["m.signature","m.comment","t.loanable"]',NULL,NULL,NULL,NULL,NULL),
(158856,'Demo',570,'MARC Haptisch - mit Zustimmung ausleihbar (ALI = s)','y','["m.physical","m.owner","m.ali.s"]','green','["m.signature","m.comment","m.stock","t.loanable"]','itembutton.see','mdi-eye-outline','y','["m.signature","m.comment","t.loanable"]',NULL,NULL,NULL,NULL,NULL),
(158859,'Demo',580,'MARC Haptisch - mit Zustimmung ausleihbar (ALI = d)','y','["m.physical","m.owner","m.ali.d"]','green','["m.signature","m.comment","m.stock","t.loanable"]','itembutton.see','mdi-eye-outline','y','["m.signature","m.comment","t.loanable"]',NULL,NULL,NULL,NULL,NULL),
(158885,'Demo',590,'MARC Haptisch - Lesesaalausleihe (ALI = i)','y','["m.physical","m.owner","m.ali.i"]','green','["m.signature","m.comment","m.stock","t.readingroom","t.loanable"]','itembutton.see','mdi-eye-outline','y','["m.signature","m.comment","t.readingroom","t.loanable"]',NULL,NULL,NULL,NULL,NULL),
(158882,'Demo',600,'MARC Haptisch - Lesesaalausleihe (ALI = f)','y','["m.physical","m.owner","m.ali.f"]','green','["m.signature","m.comment","m.stock","t.readingroom","t.loanable"]','itembutton.see','mdi-eye-outline','y','["m.signature","m.comment","t.readingroom","t.loanable"]',NULL,NULL,NULL,NULL,NULL);

-- --------------------------------------------------------
-- admincasesmedia
-- --------------------------------------------------------
INSERT INTO "user".admincasesmedia (id, client, seq, name, active, filters, light, itemcomments, itembutton, itemicon, detailwindow, detailinfos, detailbutton, detailaction, detailaction5, ids, comment) VALUES
(59956,'Demo',800,'MARC Haptisch - Zeitschrift Mailbestellung','y','["m.haptic","m.format.journal"]','green','["o.signatures","t.requestit"]','itembutton.order','mdi-arrow-right-bold-box-outline','y','["o.signaturestockcomments","t.requestit"]','detailbutton.order','o.mailorder','o.dialog.order',NULL,NULL),
(59966,'Demo',800,'Campuslieferdienst','n','["m.haptic","m.owner","m.noseries"]','green','["t.location.delivery","t.items.unimembersonly"]','itembutton.order','mdi-arrow-right-bold-box-outline','y','["o.signaturestockcomments"]','detailbutton.order','o.delivery','o.dialog.delivery',NULL,NULL),
(59967,'Demo',800,'Fernleihe andere Verbünde','n','["m.haptic","m.notowner"]','green','["t.location.externlibrary"]','itembutton.directopen','mdi-link','n','[]',NULL,'o.externlibrary','o.externlibrary',NULL,NULL),
(59968,'Demo',800,'Fernleihe im Verbund','y','["o.col.kxp","m.haptic","m.notowner"]','green','["t.location.interlibrary"]','itembutton.directopen','mdi-link','n','[]',NULL,'o.interlibrary','o.interlibrary',NULL,NULL),
(59983,'Demo',800,'MARC Haptisch - Artikel Verweis Sammelwerk / Überordnung','y','["m.haptic","m.format.article","m.articles"]','indigo','["o.signaturestockcomments","t.seeparent"]','itembutton.change','mdi-magnify','n','[]',NULL,'o.search','o.search',NULL,NULL),
(59994,'Demo',800,'MARC Haptisch - Reihe Verweis zugehörig','y','["m.series"]','purple','["o.signaturestockcomments","t.seerelated"]','itembutton.seerelated','mdi-arrow-right-bold-circle-outline','n','[]',NULL,'o.related','o.related',NULL,NULL),
(59995,'Demo',800,'MARC Haptisch - Artikel Mailbestellung','y','["m.haptic","m.format.article","m.articlez"]','green','["o.signaturecomments","t.requestit"]','itembutton.order','mdi-arrow-right-bold-box-outline','y','["o.signaturestockcomments","t.requestit"]','detailbutton.order','o.mailorder','o.dialog.order',NULL,NULL),
(59964,'Demo',900,'Ende','y','["m.haptic","m.nophysicalitem"]','red','["t.askdesk"]','itembutton.contact','mdi-account-box-outline','y','["t.contactourstaff"]','detailbutton.contact','o.mailelse','o.dialog.else',NULL,NULL);

-- --------------------------------------------------------
-- Sequenzen auf den nächsten freien Wert setzen
-- --------------------------------------------------------
SELECT setval('"user".bms_id_seq',             (SELECT COALESCE(MAX(id),0)+1 FROM "user".adminbms),         false);
SELECT setval('"user".adminconfigs_id_seq',    (SELECT COALESCE(MAX(id),0)+1 FROM "user".adminconfigs),     false);
SELECT setval('"user".adminlevels_id_seq',     (SELECT COALESCE(MAX(id),0)+1 FROM "user".adminlevels),      false);
SELECT setval('"user".admincases_id_seq',      (SELECT COALESCE(MAX(id),0)+1 FROM "user".admincases),       false);
SELECT setval('"user".admincasesmedia_id_seq', (SELECT COALESCE(MAX(id),0)+1 FROM "user".admincasesmedia),  false);
-- --------------------------------------------------------
-- TEXTE: Beschriftungen der Exemplarknoepfe
--
-- Die Regeln in admincases/admincasesmedia nennen nur den CODE eines
-- Knopfes (z.B. "itembutton.order"); die Beschriftung steht hier. Ohne
-- diese Zeilen bleibt der Knopf in der Trefferliste ohne Text.
-- Welche Codes gebraucht werden, sagen die Spalten itembutton und
-- detailbutton der beiden Regeltabellen.
-- --------------------------------------------------------

INSERT INTO "user".admintexts (client, shortcut, german, english) VALUES
('Demo', 'itembutton.directopen',    'Online lesen',        'Read online'),
('Demo', 'itembutton.availablesoon', 'Bald verfügbar',      'Available soon'),
('Demo', 'itembutton.reserve',       'Vormerken',           'Reserve'),
('Demo', 'detailbutton.reserve',     'Vormerken',           'Reserve'),
('Demo', 'itembutton.order',         'Bestellen',           'Order'),
('Demo', 'detailbutton.order',       'Bestellen',           'Order'),
('Demo', 'itembutton.see',           'Vor Ort nutzbar',     'Available on site'),
('Demo', 'itembutton.seeloan',       'Ausleihbar',          'Available for loan'),
('Demo', 'itembutton.lost',          'Verlust gemeldet',    'Reported lost'),
('Demo', 'itembutton.locked',        'Nicht verfügbar',     'Not available'),
('Demo', 'itembutton.change',        'Zum Sammelwerk',      'To the collected work'),
('Demo', 'itembutton.seerelated',    'Zugehörige Titel',    'Related titles'),
('Demo', 'itembutton.contact',       'Bitte nachfragen',    'Please ask at the desk');
