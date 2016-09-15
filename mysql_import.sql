-- MySQL dump 10.16  Distrib 10.1.13-MariaDB, for Win32 (AMD64)
--
-- Host: localhost    Database: lukida
-- ------------------------------------------------------
-- Server version	10.1.13-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `code_country`
--

DROP TABLE IF EXISTS `code_country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `code_country` (
  `code_639-2` varchar(20) NOT NULL,
  `code_639-1` varchar(20) DEFAULT NULL,
  `english` varchar(100) DEFAULT NULL,
  `german` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`code_639-2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `code_country`
--

LOCK TABLES `code_country` WRITE;
/*!40000 ALTER TABLE `code_country` DISABLE KEYS */;
INSERT INTO `code_country` VALUES ('aar','aa','Afar','Danakil-Sprache'),('abk','ab','Abkhazian','Abchasisch'),('ace','','Achinese','Aceh-Sprache'),('ach','','Acoli','Acholi-Sprache'),('ada','','Adangme','Adangme-Sprache'),('ady','','Adyghe; Adygei','Adygisch'),('afa','','Afro-Asiatic languages','Hamitosemitische Sprachen (Andere)'),('afh','','Afrihili','Afrihili'),('afr','af','Afrikaans','Afrikaans'),('ain','','Ainu','Ainu-Sprache'),('aka','ak','Akan','Akan-Sprache'),('akk','','Akkadian','Akkadisch'),('alb/sqi','sq','Albanian','Albanisch'),('ale','','Aleut','Aleutisch'),('alg','','Algonquian languages','Algonkin-Sprachen (Andere)'),('alt','','Southern Altai','Altaisch'),('amh','am','Amharic','Amharisch'),('ang','','English, Old (ca.450-1100)','Altenglisch'),('anp','','Angika','Anga-Sprache'),('apa','','Apache languages','Apachen-Sprachen'),('ara','ar','Arabic','Arabisch'),('arc','','Official Aramaic (700-300 BCE); Imperial Aramaic (700-300 BCE)','Aramäisch'),('arg','an','Aragonese','Aragonesisch'),('arm/hye','hy','Armenian','Armenisch'),('arn','','Mapudungun; Mapuche','Arauka-Sprachen'),('arp','','Arapaho','Arapaho-Sprache'),('art','','Artificial languages','Kunstsprachen (Andere)'),('arw','','Arawak','Arawak-Sprachen'),('asm','as','Assamese','Assamesisch'),('ast','','Asturian; Bable; Leonese; Asturleonese','Asturisch'),('ath','','Athapascan languages','Athapaskische Sprachen (Andere)'),('aus','','Australian languages','Australische Sprachen'),('ava','av','Avaric','Awarisch'),('ave','ae','Avestan','Avestisch'),('awa','','Awadhi','Awadhi'),('aym','ay','Aymara','Aymará-Sprache'),('aze','az','Azerbaijani','Aserbeidschanisch'),('bad','','Banda languages','Banda-Sprachen (Ubangi-Sprachen)'),('bai','','Bamileke languages','Bamileke-Sprachen'),('bak','ba','Bashkir','Baschkirisch'),('bal','','Baluchi','Belutschisch'),('bam','bm','Bambara','Bambara-Sprache'),('ban','','Balinese','Balinesisch'),('baq/eus','eu','Basque','Baskisch'),('bas','','Basa','Basaa-Sprache'),('bat','','Baltic languages','Baltische Sprachen (Andere)'),('bej','','Beja; Bedawiyet','Bedauye'),('bel','be','Belarusian','Weißrussisch'),('bem','','Bemba','Bemba-Sprache'),('ben','bn','Bengali','Bengali'),('ber','','Berber languages','Berbersprachen (Andere)'),('bho','','Bhojpuri','Bhojpuri'),('bih','bh','Bihari languages','Bihari (Andere)'),('bik','','Bikol','Bikol-Sprache'),('bin','','Bini; Edo','Edo-Sprache'),('bis','bi','Bislama','Beach-la-mar'),('bla','','Siksika','Blackfoot-Sprache'),('bnt','','Bantu languages','Bantusprachen (Andere)'),('bos','bs','Bosnian','Bosnisch'),('bra','','Braj','Braj-Bhakha'),('bre','br','Breton','Bretonisch'),('btk','','Batak languages','Batak-Sprache'),('bua','','Buriat','Burjatisch'),('bug','','Buginese','Bugi-Sprache'),('bul','bg','Bulgarian','Bulgarisch'),('bur/mya','my','Burmese','Birmanisch'),('byn','','Blin; Bilin','Bilin-Sprache'),('cad','','Caddo','Caddo-Sprachen'),('cai','','Central American Indian languages','Indianersprachen, Zentralamerika (Andere)'),('car','','Galibi Carib','Karibische Sprachen'),('cat','ca','Catalan; Valencian','Katalanisch'),('cau','','Caucasian languages','Kaukasische Sprachen (Andere)'),('ceb','','Cebuano','Cebuano'),('cel','','Celtic languages','Keltische Sprachen (Andere)'),('cha','ch','Chamorro','Chamorro-Sprache'),('chb','','Chibcha','Chibcha-Sprachen'),('che','ce','Chechen','Tschetschenisch'),('chg','','Chagatai','Tschagataisch'),('chi/zho','zh','Chinese','Chinesisch'),('chk','','Chuukese','Trukesisch'),('chm','','Mari','Tscheremissisch'),('chn','','Chinook jargon','Chinook-Jargon'),('cho','','Choctaw','Choctaw-Sprache'),('chp','','Chipewyan; Dene Suline','Chipewyan-Sprache'),('chr','','Cherokee','Cherokee-Sprache'),('chu','cu','Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic','Kirchenslawisch'),('chv','cv','Chuvash','Tschuwaschisch'),('chy','','Cheyenne','Cheyenne-Sprache'),('cmc','','Chamic languages','Cham-Sprachen'),('cop','','Coptic','Koptisch'),('cor','kw','Cornish','Kornisch'),('cos','co','Corsican','Korsisch'),('cpe','','Creoles and pidgins, English based','Kreolisch-Englisch (Andere)'),('cpf','','Creoles and pidgins, French-based','Kreolisch-Französisch (Andere)'),('cpp','','Creoles and pidgins, Portuguese-based','Kreolisch-Portugiesisch (Andere)'),('cre','cr','Cree','Cree-Sprache'),('crh','','Crimean Tatar; Crimean Turkish','Krimtatarisch'),('crp','','Creoles and pidgins','Kreolische Sprachen; Pidginsprachen (Andere)'),('csb','','Kashubian','Kaschubisch'),('cus','','Cushitic languages','Kuschitische Sprachen (Andere)'),('cze/ces','cs','Czech','Tschechisch'),('dak','','Dakota','Dakota-Sprache'),('dan','da','Danish','Dänisch'),('dar','','Dargwa','Darginisch'),('day','','Land Dayak languages','Dajakisch'),('del','','Delaware','Delaware-Sprache'),('den','','Slave (Athapascan)','Slave-Sprache'),('dgr','','Dogrib','Dogrib-Sprache'),('din','','Dinka','Dinka-Sprache'),('div','dv','Divehi; Dhivehi; Maldivian','Maledivisch'),('doi','','Dogri','Dogri'),('dra','','Dravidian languages','Drawidische Sprachen (Andere)'),('dsb','','Lower Sorbian','Niedersorbisch'),('dua','','Duala','Duala-Sprachen'),('dum','','Dutch, Middle (ca.1050-1350)','Mittelniederländisch'),('dut/nld','nl','Dutch; Flemish','Niederländisch'),('dyu','','Dyula','Dyula-Sprache'),('dzo','dz','Dzongkha','Dzongkha'),('efi','','Efik','Efik'),('egy','','Egyptian (Ancient)','Ägyptisch'),('eka','','Ekajuk','Ekajuk'),('elx','','Elamite','Elamisch'),('eng','en','English','Englisch'),('enm','','English, Middle (1100-1500)','Mittelenglisch'),('epo','eo','Esperanto','Esperanto'),('est','et','Estonian','Estnisch'),('ewe','ee','Ewe','Ewe-Sprache'),('ewo','','Ewondo','Ewondo'),('fan','','Fang','Pangwe-Sprache'),('fao','fo','Faroese','Färöisch'),('fat','','Fanti','Fante-Sprache'),('fij','fj','Fijian','Fidschi-Sprache'),('fil','','Filipino; Pilipino','Pilipino'),('fin','fi','Finnish','Finnisch'),('fiu','','Finno-Ugrian languages','Finnougrische Sprachen (Andere)'),('fon','','Fon','Fon-Sprache'),('fre/fra','fr','French','Französisch'),('frm','','French, Middle (ca.1400-1600)','Mittelfranzösisch'),('fro','','French, Old (842-ca.1400)','Altfranzösisch'),('frr','','Northern Frisian','Nordfriesisch'),('frs','','Eastern Frisian','Ostfriesisch'),('fry','fy','Western Frisian','Friesisch'),('ful','ff','Fulah','Ful'),('fur','','Friulian','Friulisch'),('gaa','','Ga','Ga-Sprache'),('gay','','Gayo','Gayo-Sprache'),('gba','','Gbaya','Gbaya-Sprache'),('gem','','Germanic languages','Germanische Sprachen (Andere)'),('geo/kat','ka','Georgian','Georgisch'),('ger/deu','de','German','Deutsch'),('gez','','Geez','Altäthiopisch'),('gil','','Gilbertese','Gilbertesisch'),('gla','gd','Gaelic; Scottish Gaelic','Gälisch-Schottisch'),('gle','ga','Irish','Irisch'),('glg','gl','Galician','Galicisch'),('glv','gv','Manx','Manx'),('gmh','','German, Middle High (ca.1050-1500)','Mittelhochdeutsch'),('goh','','German, Old High (ca.750-1050)','Althochdeutsch'),('gon','','Gondi','Gondi-Sprache'),('gor','','Gorontalo','Gorontalesisch'),('got','','Gothic','Gotisch'),('grb','','Grebo','Grebo-Sprache'),('grc','','Greek, Ancient (to 1453)','Griechisch'),('gre/ell','el','Greek, Modern (1453-)','Neugriechisch'),('grn','gn','Guarani','Guaraní-Sprache'),('gsw','','Swiss German; Alemannic; Alsatian','Schweizerdeutsch'),('guj','gu','Gujarati','Gujarati-Sprache'),('gwi','','Gwich\'in','Kutchin-Sprache'),('hai','','Haida','Haida-Sprache'),('hat','ht','Haitian; Haitian Creole','Haïtien (Haiti-Kreolisch)'),('hau','ha','Hausa','Haussa-Sprache'),('haw','','Hawaiian','Hawaiisch'),('heb','he','Hebrew','Hebräisch'),('her','hz','Herero','Herero-Sprache'),('hil','','Hiligaynon','Hiligaynon-Sprache'),('him','','Himachali languages; Western Pahari languages','Himachali'),('hin','hi','Hindi','Hindi'),('hit','','Hittite','Hethitisch'),('hmn','','Hmong; Mong','Miao-Sprachen'),('hmo','ho','Hiri Motu','Hiri-Motu'),('hrv','hr','Croatian','Kroatisch'),('hsb','','Upper Sorbian','Obersorbisch'),('hun','hu','Hungarian','Ungarisch'),('hup','','Hupa','Hupa-Sprache'),('iba','','Iban','Iban-Sprache'),('ibo','ig','Igbo','Ibo-Sprache'),('ice/isl','is','Icelandic','Isländisch'),('ido','io','Ido','Ido'),('iii','ii','Sichuan Yi; Nuosu','Lalo-Sprache'),('ijo','','Ijo languages','Ijo-Sprache'),('iku','iu','Inuktitut','Inuktitut'),('ile','ie','Interlingue; Occidental','Interlingue'),('ilo','','Iloko','Ilokano-Sprache'),('ina','ia','Interlingua (International Auxiliary Language Association)','Interlingua'),('inc','','Indic languages','Indoarische Sprachen (Andere)'),('ind','id','Indonesian','Bahasa Indonesia'),('ine','','Indo-European languages','Indogermanische Sprachen (Andere)'),('inh','','Ingush','Inguschisch'),('ipk','ik','Inupiaq','Inupik'),('ira','','Iranian languages','Iranische Sprachen (Andere)'),('iro','','Iroquoian languages','Irokesische Sprachen'),('ita','it','Italian','Italienisch'),('jav','jv','Javanese','Javanisch'),('jbo','','Lojban','Lojban'),('jpn','ja','Japanese','Japanisch'),('jpr','','Judeo-Persian','Jüdisch-Persisch'),('jrb','','Judeo-Arabic','Jüdisch-Arabisch'),('kaa','','Kara-Kalpak','Karakalpakisch'),('kab','','Kabyle','Kabylisch'),('kac','','Kachin; Jingpho','Kachin-Sprache'),('kal','kl','Kalaallisut; Greenlandic','Grönländisch'),('kam','','Kamba','Kamba-Sprache'),('kan','kn','Kannada','Kannada'),('kar','','Karen languages','Karenisch'),('kas','ks','Kashmiri','Kaschmiri'),('kau','kr','Kanuri','Kanuri-Sprache'),('kaw','','Kawi','Kawi'),('kaz','kk','Kazakh','Kasachisch'),('kbd','','Kabardian','Kabardinisch'),('kha','','Khasi','Khasi-Sprache'),('khi','','Khoisan languages','Khoisan-Sprachen (Andere)'),('khm','km','Central Khmer','Kambodschanisch'),('kho','','Khotanese; Sakan','Sakisch'),('kik','ki','Kikuyu; Gikuyu','Kikuyu-Sprache'),('kin','rw','Kinyarwanda','Rwanda-Sprache'),('kir','ky','Kirghiz; Kyrgyz','Kirgisisch'),('kmb','','Kimbundu','Kimbundu-Sprache'),('kok','','Konkani','Konkani'),('kom','kv','Komi','Komi-Sprache'),('kon','kg','Kongo','Kongo-Sprache'),('kor','ko','Korean','Koreanisch'),('kos','','Kosraean','Kosraeanisch'),('kpe','','Kpelle','Kpelle-Sprache'),('krc','','Karachay-Balkar','Karatschaiisch-Balkarisch'),('krl','','Karelian','Karelisch'),('kro','','Kru languages','Kru-Sprachen (Andere)'),('kru','','Kurukh','Oraon-Sprache'),('kua','kj','Kuanyama; Kwanyama','Kwanyama-Sprache'),('kum','','Kumyk','Kumükisch'),('kur','ku','Kurdish','Kurdisch'),('kut','','Kutenai','Kutenai-Sprache'),('lad','','Ladino','Judenspanisch'),('lah','','Lahnda','Lahnda'),('lam','','Lamba','Lamba-Sprache (Bantusprache)'),('lao','lo','Lao','Laotisch'),('lat','la','Latin','Latein'),('lav','lv','Latvian','Lettisch'),('lez','','Lezghian','Lesgisch'),('lim','li','Limburgan; Limburger; Limburgish','Limburgisch'),('lin','ln','Lingala','Lingala'),('lit','lt','Lithuanian','Litauisch'),('lol','','Mongo','Mongo-Sprache'),('loz','','Lozi','Rotse-Sprache'),('ltz','lb','Luxembourgish; Letzeburgesch','Luxemburgisch'),('lua','','Luba-Lulua','Lulua-Sprache'),('lub','lu','Luba-Katanga','Luba-Katanga-Sprache'),('lug','lg','Ganda','Ganda-Sprache'),('lui','','Luiseno','Luiseño-Sprache'),('lun','','Lunda','Lunda-Sprache'),('luo','','Luo (Kenya and Tanzania)','Luo-Sprache'),('lus','','Lushai','Lushai-Sprache'),('mac/mkd','mk','Macedonian','Makedonisch'),('mad','','Madurese','Maduresisch'),('mag','','Magahi','Khotta'),('mah','mh','Marshallese','Marschallesisch'),('mai','','Maithili','Maithili'),('mak','','Makasar','Makassarisch'),('mal','ml','Malayalam','Malayalam'),('man','','Mandingo','Malinke-Sprache'),('mao/mri','mi','Maori','Maori-Sprache'),('map','','Austronesian languages','Austronesische Sprachen (Andere)'),('mar','mr','Marathi','Marathi'),('mas','','Masai','Massai-Sprache'),('may/msa','ms','Malay','Malaiisch'),('mdf','','Moksha','Mokscha-Sprache'),('mdr','','Mandar','Mandaresisch'),('men','','Mende','Mende-Sprache'),('mga','','Irish, Middle (900-1200)','Mittelirisch'),('mic','','Mi\'kmaq; Micmac','Micmac-Sprache'),('min','','Minangkabau','Minangkabau-Sprache'),('mis','','Uncoded languages','Einzelne andere Sprachen'),('mkh','','Mon-Khmer languages','Mon-Khmer-Sprachen (Andere)'),('mlg','mg','Malagasy','Malagassi-Sprache'),('mlt','mt','Maltese','Maltesisch'),('mnc','','Manchu','Mandschurisch'),('mni','','Manipuri','Meithei-Sprache'),('mno','','Manobo languages','Manobo-Sprachen'),('moh','','Mohawk','Mohawk-Sprache'),('mon','mn','Mongolian','Mongolisch'),('mos','','Mossi','Mossi-Sprache'),('mul','','Multiple languages','Mehrere Sprachen'),('mun','','Munda languages','Mundasprachen (Andere)'),('mus','','Creek','Muskogisch'),('mwl','','Mirandese','Mirandesisch'),('mwr','','Marwari','Marwari'),('myn','','Mayan languages','Maya-Sprachen'),('myv','','Erzya','Erza-Mordwinisch'),('nah','','Nahuatl languages','Nahuatl'),('nai','','North American Indian languages','Indianersprachen, Nordamerika (Andere)'),('nap','','Neapolitan','Neapel / Mundart'),('nau','na','Nauru','Nauruanisch'),('nav','nv','Navajo; Navaho','Navajo-Sprache'),('nbl','nr','Ndebele, South; South Ndebele','Ndebele-Sprache (Transvaal)'),('nde','nd','Ndebele, North; North Ndebele','Ndebele-Sprache (Simbabwe)'),('ndo','ng','Ndonga','Ndonga'),('nds','','Low German; Low Saxon; German, Low; Saxon, Low','Niederdeutsch'),('nep','ne','Nepali','Nepali'),('new','','Nepal Bhasa; Newari','Newari'),('nia','','Nias','Nias-Sprache'),('nic','','Niger-Kordofanian languages','Nigerkordofanische Sprachen (Andere)'),('niu','','Niuean','Niue-Sprache'),('nno','nn','Norwegian Nynorsk; Nynorsk, Norwegian','Nynorsk'),('nob','nb','Bokmål, Norwegian; Norwegian Bokmål','Bokmål'),('nog','','Nogai','Nogaisch'),('non','','Norse, Old','Altnorwegisch'),('nor','no','Norwegian','Norwegisch'),('nqo','','N\'Ko','N\'Ko'),('nso','','Pedi; Sepedi; Northern Sotho','Pedi-Sprache'),('nub','','Nubian languages','Nubische Sprachen'),('nwc','','Classical Newari; Old Newari; Classical Nepal Bhasa','Alt-Newari'),('nya','ny','Chichewa; Chewa; Nyanja','Nyanja-Sprache'),('nym','','Nyamwezi','Nyamwezi-Sprache'),('nyn','','Nyankole','Nkole-Sprache'),('nyo','','Nyoro','Nyoro-Sprache'),('nzi','','Nzima','Nzima-Sprache'),('oci','oc','Occitan (post 1500)','Okzitanisch'),('oji','oj','Ojibwa','Ojibwa-Sprache'),('ori','or','Oriya','Oriya-Sprache'),('orm','om','Oromo','Galla-Sprache'),('osa','','Osage','Osage-Sprache'),('oss','os','Ossetian; Ossetic','Ossetisch'),('ota','','Turkish, Ottoman (1500-1928)','Osmanisch'),('oto','','Otomian languages','Otomangue-Sprachen'),('paa','','Papuan languages','Papuasprachen (Andere)'),('pag','','Pangasinan','Pangasinan-Sprache'),('pal','','Pahlavi','Mittelpersisch'),('pam','','Pampanga; Kapampangan','Pampanggan-Sprache'),('pan','pa','Panjabi; Punjabi','Pandschabi-Sprache'),('pap','','Papiamento','Papiamento'),('pau','','Palauan','Palau-Sprache'),('peo','','Persian, Old (ca.600-400 B.C.)','Altpersisch'),('per/fas','fa','Persian','Persisch'),('phi','','Philippine languages','Philippinisch-Austronesisch (Andere)'),('phn','','Phoenician','Phönikisch'),('pli','pi','Pali','Pali'),('pol','pl','Polish','Polnisch'),('pon','','Pohnpeian','Ponapeanisch'),('por','pt','Portuguese','Portugiesisch'),('pra','','Prakrit languages','Prakrit'),('pro','','Provençal, Old (to 1500);Occitan, Old (to 1500)','Altokzitanisch'),('pus','ps','Pushto; Pashto','Paschtu'),('qaa-qtz','','Reserved for local use','Reserviert für lokale Verwendung'),('que','qu','Quechua','Quechua-Sprache'),('raj','','Rajasthani','Rajasthani'),('rap','','Rapanui','Osterinsel-Sprache'),('rar','','Rarotongan; Cook Islands Maori','Rarotonganisch'),('roa','','Romance languages','Romanische Sprachen (Andere)'),('roh','rm','Romansh','Rätoromanisch'),('rom','','Romany','Romani (Sprache)'),('rum/ron','ro','Romanian; Moldavian; Moldovan','Rumänisch'),('run','rn','Rundi','Rundi-Sprache'),('rup','','Aromanian; Arumanian; Macedo-Romanian','Aromunisch'),('rus','ru','Russian','Russisch'),('sad','','Sandawe','Sandawe-Sprache'),('sag','sg','Sango','Sango-Sprache'),('sah','','Yakut','Jakutisch'),('sai','','South American Indian languages','Indianersprachen, Südamerika (Andere)'),('sal','','Salishan languages','Salish-Sprache'),('sam','','Samaritan Aramaic','Samaritanisch'),('san','sa','Sanskrit','Sanskrit'),('sas','','Sasak','Sasak'),('sat','','Santali','Santali'),('scn','','Sicilian','Sizilianisch'),('sco','','Scots','Schottisch'),('sel','','Selkup','Selkupisch'),('sem','','Semitic languages','Semitische Sprachen (Andere)'),('sga','','Irish, Old (to 900)','Altirisch'),('sgn','','Sign Languages','Zeichensprachen'),('shn','','Shan','Schan-Sprache'),('sid','','Sidamo','Sidamo-Sprache'),('sin','si','Sinhala; Sinhalese','Singhalesisch'),('sio','','Siouan languages','Sioux-Sprachen (Andere)'),('sit','','Sino-Tibetan languages','Sinotibetische Sprachen (Andere)'),('sla','','Slavic languages','Slawische Sprachen (Andere)'),('slo/slk','sk','Slovak','Slowakisch'),('slv','sl','Slovenian','Slowenisch'),('sma','','Southern Sami','Südsaamisch'),('sme','se','Northern Sami','Nordsaamisch'),('smi','','Sami languages','Saamisch'),('smj','','Lule Sami','Lulesaamisch'),('smn','','Inari Sami','Inarisaamisch'),('smo','sm','Samoan','Samoanisch'),('sms','','Skolt Sami','Skoltsaamisch'),('sna','sn','Shona','Schona-Sprache'),('snd','sd','Sindhi','Sindhi-Sprache'),('snk','','Soninke','Soninke-Sprache'),('sog','','Sogdian','Sogdisch'),('som','so','Somali','Somali'),('son','','Songhai languages','Songhai-Sprache'),('sot','st','Sotho, Southern','Süd-Sotho-Sprache'),('spa','es','Spanish; Castilian','Spanisch'),('srd','sc','Sardinian','Sardisch'),('srn','','Sranan Tongo','Sranantongo'),('srp','sr','Serbian','Serbisch'),('srr','','Serer','Serer-Sprache'),('ssa','','Nilo-Saharan languages','Nilosaharanische Sprachen (Andere)'),('ssw','ss','Swati','Swasi-Sprache'),('suk','','Sukuma','Sukuma-Sprache'),('sun','su','Sundanese','Sundanesisch'),('sus','','Susu','Susu'),('sux','','Sumerian','Sumerisch'),('swa','sw','Swahili','Swahili'),('swe','sv','Swedish','Schwedisch'),('syc','','Classical Syriac','Syrisch'),('syr','','Syriac','Neuostaramäisch'),('tah','ty','Tahitian','Tahitisch'),('tai','','Tai languages','Thaisprachen (Andere)'),('tam','ta','Tamil','Tamil'),('tat','tt','Tatar','Tatarisch'),('tel','te','Telugu','Telugu-Sprache'),('tem','','Timne','Temne-Sprache'),('ter','','Tereno','Tereno-Sprache'),('tet','','Tetum','Tetum-Sprache'),('tgk','tg','Tajik','Tadschikisch'),('tgl','tl','Tagalog','Tagalog'),('tha','th','Thai','Thailändisch'),('tib/bod','bo','Tibetan','Tibetisch'),('tig','','Tigre','Tigre-Sprache'),('tir','ti','Tigrinya','Tigrinja-Sprache'),('tiv','','Tiv','Tiv-Sprache'),('tkl','','Tokelau','Tokelauanisch'),('tlh','','Klingon; tlhIngan-Hol','Klingonisch'),('tli','','Tlingit','Tlingit-Sprache'),('tmh','','Tamashek','Tamašeq'),('tog','','Tonga (Nyasa)','Tonga (Bantusprache, Sambia)'),('ton','to','Tonga (Tonga Islands)','Tongaisch'),('tpi','','Tok Pisin','Neumelanesisch'),('tsi','','Tsimshian','Tsimshian-Sprache'),('tsn','tn','Tswana','Tswana-Sprache'),('tso','ts','Tsonga','Tsonga-Sprache'),('tuk','tk','Turkmen','Turkmenisch'),('tum','','Tumbuka','Tumbuka-Sprache'),('tup','','Tupi languages','Tupi-Sprache'),('tur','tr','Turkish','Türkisch'),('tut','','Altaic languages','Altaische Sprachen (Andere)'),('tvl','','Tuvalu','Elliceanisch'),('twi','tw','Twi','Twi-Sprache'),('tyv','','Tuvinian','Tuwinisch'),('udm','','Udmurt','Udmurtisch'),('uga','','Ugaritic','Ugaritisch'),('uig','ug','Uighur; Uyghur','Uigurisch'),('ukr','uk','Ukrainian','Ukrainisch'),('umb','','Umbundu','Mbundu-Sprache'),('und','','Undetermined','Nicht zu entscheiden'),('urd','ur','Urdu','Urdu'),('uzb','uz','Uzbek','Usbekisch'),('vai','','Vai','Vai-Sprache'),('ven','ve','Venda','Venda-Sprache'),('vie','vi','Vietnamese','Vietnamesisch'),('vol','vo','Volapük','Volapük'),('vot','','Votic','Wotisch'),('wak','','Wakashan languages','Wakash-Sprachen'),('wal','','Wolaitta; Wolaytta','Walamo-Sprache'),('war','','Waray','Waray'),('was','','Washo','Washo-Sprache'),('wel/cym','cy','Welsh','Kymrisch'),('wen','','Sorbian languages','Sorbisch (Andere)'),('wln','wa','Walloon','Wallonisch'),('wol','wo','Wolof','Wolof-Sprache'),('xal','','Kalmyk; Oirat','Kalmückisch'),('xho','xh','Xhosa','Xhosa-Sprache'),('yao','','Yao','Yao-Sprache (Bantusprache)'),('yap','','Yapese','Yapesisch'),('yid','yi','Yiddish','Jiddisch'),('yor','yo','Yoruba','Yoruba-Sprache'),('ypk','','Yupik languages','Ypik-Sprachen'),('zap','','Zapotec','Zapotekisch'),('zbl','','Blissymbols; Blissymbolics; Bliss','Bliss-Symbol'),('zen','','Zenaga','Zenaga'),('zgh','','Standard Moroccan Tamazight','Marokkanisch'),('zha','za','Zhuang; Chuang','Zhuang'),('znd','','Zande languages','Zande-Sprachen'),('zul','zu','Zulu','Zulu-Sprache'),('zun','','Zuni','Zuñi-Sprache'),('zxx','','No linguistic content; Not applicable','Kein linguistischer Inhalt'),('zza','','Zaza; Dimili; Dimli; Kirdki; Kirmanjki; Zazaki','Zazaki');
/*!40000 ALTER TABLE `code_country` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `translation`
--

DROP TABLE IF EXISTS `translation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `translation` (
  `shortcut` varchar(100) NOT NULL,
  `german` varchar(2000) DEFAULT NULL,
  `english` varchar(2000) DEFAULT NULL,
  `init` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`shortcut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `translation`
--

LOCK TABLES `translation` WRITE;
/*!40000 ALTER TABLE `translation` DISABLE KEYS */;
INSERT INTO `translation` VALUES ('100','UB Magdeburg','UB Magdeburg',0),('A','nicht bestellbar','not available',0),('ACCESS','Zugänge','Access',1),('ADDITIONALINFORMATIONS','Zusatzinformationen','Additional information',0),('ADDRESS','Anschrift','Address',0),('AFT','VerfasserIn eines Nachworts','Author of afterword, colophon, etc.',0),('ALREADYINCHECKLIST','Bereits in Merkliste vorhanden','Item is already on checklist',1),('AREA','Bereich','Area',1),('ARR','ArrangeurIn','Arranger',0),('ARTICLE','Artikel','Article',1),('ASN','BeteiligteR\r\n','Associated name',0),('ASSISTANT','Assistent','Assistant',1),('ASSISTANTNO','Keine Treffer vorhanden','No hits found',1),('ASSISTANTYES','Treffer vorhanden','hits found',1),('ASSOCIATES','weitere Personen','Associates',0),('AUDIOCARRIER','Tonträger','Audio Carrier',0),('AUDIOCARRIERADDITIONALMATERIAL','Tonträger & Begleitmaterial','Audio Carrier & Additional material',0),('AUT','VerfasserIn','Author',0),('AUTHOR','Verfasser','Author',0),('AVAILABLE','Verfügbar','Available',0),('AVAILABLEFROM','Verfügbar ab','Available from',0),('B','bestellbar / Leihen und (Teil-)Kopie','available / loan and (partial) copy',0),('BOOK','Buch','Book',1),('C','nicht bestellbar','not available',0),('CANCEL','Zurück','Cancel',1),('CANCELSUCCESS','Die Vormerkung wurde erfolgreich stoniert','The reservation has been cancelled successfully',1),('CHANGEPASSWORT','Passwort ändern','Chance Password',1),('CHECKLIST','Merkliste','Checklist',1),('CHECKLISTCLEAR','Merkliste leeren','Clear checklist',1),('CHECKLISTCLEARSUCCESS','Die Merkliste wurde geleert','Checklist has been cleared',1),('CHECKLISTEMPTY','Ihre Merkliste ist leer','Checklist is empty',1),('CHECKLISTREMOVE','Von Merkliste entfernen','Remove from checklist',1),('CHECKLISTREMOVESUCCESS','Erfolgreich von der Merkliste entfernt','Successfully removed from checklist',1),('CHECKLISTSUCCESS','Erfolgreich zur Merkliste ergänzt','Successfully added to checklist',1),('CITATION','Bibliografische Zitate','Citation',0),('CLASS','Sachgebiet','Class',0),('CLASSIFICATION','Klassifikation','Classification',0),('CLEAR','Leeren','Clear',1),('CMP','KomponistIn','Composer',0),('COL1','1-Spaltig','1 Column',1),('COL2','2-Spaltig','2 Columns',1),('COL3','3-Spaltig','3 Columns',1),('COL4','4-Spaltig','4 Columns',1),('COLLECTABLE','Abholbar','Collectable',0),('COLLECTABLEREMARK','Wichtig: Ihre Vormerkung(en) sind an der Ausleihtheke abholbar!','Note: Your reservations are collectable!',1),('COMMENT','Kommentar','Comment',1),('COMPUTERFILE','Technische Angaben','Computer file',0),('CORPORATION','Körperschaft','corporation',0),('CORRECTPERIOD','Bitte geben Sie einen gültigen Bereich aus dem Zeitraum {PERIOD} an!','Please enter a valid area from the period {PERIOD}',1),('CTB','MitwirkendeR\r\n','Contributor',0),('DATACARRIER','Datenträger ','Data carrier',0),('DATAMEDIA','Datenträger','Data Media',1),('DATAPOOLGLOBAL','Alle Bibliotheken','All Libraries',1),('DATAPOOLLOCAL','Diese Bibliothek','This Library',1),('DISSERTATION','Hochschulschrift','Dissertation',0),('DRT','RegisseurIn','Director',0),('DTE','WidmungsempfängerIn','Dedicatee',0),('DVD','DVD','DVD',0),('EARTICLE','eArtikel','eArticle',0),('EBOOK','eBook','eBook',0),('EDITION','Ausgabe','Edition',0),('EDT','HerausgeberIn','Editor',0),('EJOURNAL','eZeitschrift','eJournal',0),('ELECTRONICRESOURCE','elektronische Quelle','eRessource',1),('EMAIL','Email','Email',0),('ENG','Englisch','English',1),('EXEMPLARS','Exemplare','Exemplars',0),('EXPIRES','Ablaufdatum','Expires',0),('EXPORT','Export','Export',1),('F','bestellbar / nur Kopie','available / copy only',0),('FACETFORMAT','Formate','Formats',1),('FACETTYP','Typ','Type',1),('FACETYEAR','Zeitraum','Period',1),('FEETOTAL','Saldo','Total',0),('FIRSTNAME','Vorname','First Name',0),('FIRSTOBJECT','Erstes Medium bereits erreicht','First record reached',1),('FORMAT','Format','Format',0),('G','nicht bestellbar','not available',0),('GAME','Spiel','Game',1),('GBV-ODISS','Online-Dissertationen','Online-Dissertationen',0),('GBV-SPRINGER-LNCS','E-Journals/Artikel (Springer-Verlag)','E-Journals/Artikel (Springer-Verlag)',0),('GBV_GVK','Gemeinsamer Verbundkatalog / GVK','Gemeinsamer Verbundkatalog / GVK',0),('GBV_ILN_100','UB Magdeburg','UB Magdeburg',0),('GBV_ILN_697','DHI Bibliothek Washington','DHI Library Washington',0),('GBV_NLM','Medline','Medline',0),('GBV_NL_ARTICLE','Artikel (Nationallizenzen)','Artikel (Nationallizenzen)',0),('GBV_NL_EBOOK','E-Books (Nationallizenzen)','E-Books (Nationallizenzen)',0),('GBV_OEVK','Verbundkatalog Öffentlicher Bibliotheken','Verbundkatalog Öffentlicher Bibliotheken',0),('GENERAL','Allgemein','General',0),('GER','Deutsch','German',1),('HNR','GefeierteR','Honoree',0),('I','nicht bestellbar','not available',0),('ID','Kennung','ID',1),('IDLEHAPPENED','System wird für eine neue Suche vorbereitet','System will be prepared for a new search',1),('ILL','IllustratorIn','Illustrator',0),('IMPRINT','Impressum','Imprint',1),('IN','In','In',0),('INPUTBYLINE','Bitte erfassen Sie in den passenden Reitern Ihre Suchbegriffe (pro Zeile ein Suchbegriff)','Please enter your searchphrases in the tabs (One searchphrase per line)',1),('INTERLOAN','Fernleihe','interlibrary loan',0),('ISBN','ISBN','ISBN',0),('ISN','ISBN / ISSN','ISBN / ISSN',1),('ISSN','ISSN','ISSN',0),('ITEMLOCATION','Hier finden Sie das Medium:','You will find the item here:',0),('JOP','Journals Online & Print','Journals Online & Print',1),('JOURNAL','Zeitschrift','Journal',1),('LANGUAGE','Sprache','Language',0),('LANGUAGECHANGED','Die Sprache wurde erfolgreich umgestellt!','Language has been switched',1),('LANGUAGENOTES','Informationen zu Sprache/Schrift','Language note',0),('LASTNAME','Nachname','Last Name',0),('LASTOBJECT','Kein weiteres Medium vorhanden','Last record reached',1),('LENDABLE','Ausleihbar','Lendable',0),('LEVEL','Etage','Level',0),('LICENSE','Lizenzbestimmungen','License',0),('LOCKED','Gesperrt','Locked',0),('LOCKEDLEND','Für die Ausleihe gesperrt','Locked for lend',0),('LOGIN','Anmelden','Login',1),('LOGINERROR','Sie konnten leider nicht angemeldet werden!<br />Bitte überprüfen Sie Ihre Eingaben!','Your login was rnot accepted!<br />Please verify your login fields',1),('LOGINFIRST','Hinweis<br />Bitte zunächst anmelden!','Note<br />Please login first!',1),('LOGINSUCCESS','Hallo {firstname} {lastname}<br />Sind sind erfolgreich angemeldet!','Hello {firstname} {lastname}<br />You\'re logged in successfully',1),('LOGOUT','Abmelden','Logout',1),('LOGOUTREALLY','Wollen Sie sich von {system} abmelden?','Do you want to logout from {system}?',1),('LOGOUTSUCCESS','Abmeldung war erfolgreich','Logout successfully done',1),('MAGAZINE','Magazin','Magazine',0),('MAIL','Mail','Mail',1),('MAILRECIPIENT','Empfänger Mailadresse','Mail Recipient',1),('MAILSUCCESS','Mail erfolgreich verschickt','Mail successfully sent',1),('MAILWRONG','Mailadresse ist nicht korrekt','Mail is not valid',1),('MANUSCRIPT','Handschrift','Manuscripts',1),('MAP','Karte','Map',1),('MF-NR.','MF-Nr.','MF-Nr.',0),('MICROFORM','Microform','Microform',1),('MIXEDMATERIALS','Medienkombination','Mixed materials',1),('MOTIONPICTURE','Film','Movie',1),('MOVIE','Film','Movie',0),('MOVIEADDITIONALMATERIAL','Begleitmaterial zum Film','Movie: additional material',0),('MULTIVOLUMEWORK','Mehrbändiges Werk','Multi Volume Work',0),('MUSICALSCORE','Partitur','Musical Score',1),('MYAREA','Mein Bereich','My Area',1),('NOHITS','Es wurden leider keine Treffer gefunden...<br />Bitte Suchkriterien überprüfen','No hits found...<br />Please verify search criteria',1),('NOSIMULARHITS','Es wurden keine ähnlichen Publikationen gefunden...','No simular hits found...',1),('NOTAVAILABLE','Nicht verfügbar','Not available',0),('NOTE','Hinweis','Note',0),('NOTES','Hinweise','Notes',0),('NOTLENDABLE','Das Exemplar kann <b>nicht</b> ausgeliehen werden! (Präsenzbestand)','This item is <b>not</b> lendable (reference collection)',0),('NRT','ErzählerIn','Narrator',0),('OK','OK','OK',1),('ONLINE','Online','Online',0),('ORDER','Bestellung','Order',1),('ORDERED','Exemplar bestellt oder in Bearbeitung','Item ordered or in process',0),('ORDERHERE','Das Exemplar kann hier bestellt werden','This Example can be ordered here',0),('ORDERSUCCESS','Die Bestellung wurde erfolgreich aufgegeben','The order has been completed successfully',1),('OTH','Sonstige Person, Familie und Körperschaft MitwirkendeR','Other',0),('PART','Teil','Part',0),('PARTICIPANTS','Beteiligte Personen','Participants',0),('PASSWORD','Passwort','Password',1),('PHYSICALDESCRIPTION','Umfang','Physical Object',0),('PICTURE','Picture','Bild',1),('PRF','AusführendeR','Performer',0),('PRINT','Druck','Print',1),('PROCESSSTOPPED','Der Vorgang konnte nicht beendet werden!','The process has been stopped unexpectedly',1),('PROVIDEMAIL','Diese Funktion ist erst verfügbar, wenn Sie der Bibliothek Ihre Mailadresse mitgeteilt haben','This function is available only after you have submitted your email address to the library',1),('PRT','DruckerIn','Printer',0),('PUBLISHED','Veröffentlicht','Published in',0),('PUBLISHER','Verlag','Publisher',1),('QUEUE','Vormerkungen','Queue position',0),('READINGROOMONLY','Benutzung nur im Lesesaal','For use only in reading room',0),('RECORDDAIA','Daten DAIA','Record DAIA',0),('RECORDMARC21','Daten MARC21','Record MARC21',0),('RECORDPAIA','Daten PAIA','Record PAIA',0),('REFERENCECOLLECTION','Präsenzbestand','Reference collection',0),('RELATEDARTICLES','Zugehörige Artikel','Related articles',0),('RELATEDJOURNALS','Zugehörige Einzelhefte','Related Journals',0),('RELATEDPUBLICATIONS','Zugehörige Publikationen','Related Publications',0),('RENEW','Verlängern','Renew',0),('RENEWALS','Verlängerungen','Renewals',0),('RENEWSUCCESS','Die Verlängerung wurde erfolgreich bearbeitet','The renew has been completed successfully',1),('REPRODUCTION','Reproduktion als','Reproduction as',0),('REQUIREDFIELD','Dieses Feld ist auszufüllen','Field is required',1),('REQUIREDTWOFIELDS','Beide Felder sind auszufüllen','Both fields are required.',1),('RESERVATION','Vormerken','Reserve',1),('RESERVATIONPOSSIBLE','Das Exemplar ist ausgeliehen und kann vorgemerkt werden','The item is currently not available but can be reserved here',0),('RESERVATIONSUCCESS','Die Vormerkung wurde erfolgreich vorgenommen','The reservation has been completed successfully',1),('RESULTFILTER','Ergebnisse eingrenzen','Filter results',1),('RETURNSINCE','Rückgabe seit','Return since',0),('RETURNUNTIL','Rückgabe bis','Return until',0),('SEEADDINFO','Siehe Zusatzinformationen','See add. information',0),('SEEPUBLISHED','Siehe Veröffentlicht','See published',0),('SELECTDATAPOOL','Bestand auswählen','Select datapool',1),('SELECTLANGUAGE','Sprache auswählen','Select language',1),('SELECTTHEME','Theme auswählen','Select theme',1),('SELECTVIEW','Ansicht auswählen','Select view',1),('SENDMAIL','Mail verschicken','Send Mail',1),('SERIAL','Schriftenreihe','Serial Volume',0),('SERIALVOLUME','Reihentitel','Serial Volume',1),('SERIES','Schriftenreihe','Series',0),('SFX','SFX','SFX',1),('SHELVE','Freihand','On shelf',0),('SIGNATURE','Signatur','Shelf mark',0),('SIMULARPUBS','Ähnliche Publikationen','Similar publications',0),('SOUNDRECORDING','Tonträger','Audio Carrier',1),('SOURCE','Quelle','Source',0),('SPK','RednerIn','Speaker',0),('SSG-OLC-ALT','Altertumswissenschaften','Ancient studies',0),('SSG-OLC-ANG','Anglistik','English language and literature',0),('SSG-OLC-ARC','Architektur','Architecture',0),('SSG-OLC-ASS','Afrika südlich der Sahara','Africa south of Sahara',0),('SSG-OLC-AST','Astronomie','Astronomy',0),('SSG-OLC-BIF','Bildungsforschung','Educational research',0),('SSG-OLC-BUB','Informations-, Buch- und Bibliothekswesen','Library and information science',0),('SSG-OLC-CHE','Chemie','Chemistry',0),('SSG-OLC-ETH','Ethnologie','Ethnology',0),('SSG-OLC-FOR','Forstwissenschaften','Forest sciences',0),('SSG-OLC-FRK','Frankreichkunde und Allgemeine Romanistik','French studies and Romance studies',0),('SSG-OLC-FTH','Film und Theater','Film and theatre',0),('SSG-OLC-GEO','Geowissenschaften','Geosciences',0),('SSG-OLC-GER','Germanistik','German studies',0),('SSG-OLC-GWK','Kunst und Kunstwissenschaft','Art and science of art',0),('SSG-OLC-HIS','Geschichte','History',0),('SSG-OLC-HSW','Hochschulwesen','University education',0),('SSG-OLC-IBA','Ibero-Amerika','Ibero-America',0),('SSG-OLC-IBL','Internationale Beziehungen und Länderkunde','International relations and ragional geography',0),('SSG-OLC-ITF','Italienforschung','Italy research',0),('SSG-OLC-JUR','Recht','Law',0),('SSG-OLC-KPH','Klassische Philologie','Classical philology',0),('SSG-OLC-MAT','Mathematik und Informatik','Mathematics and informatics',0),('SSG-OLC-MFO','Asien und Nordafrika','Asia and North Africa',0),('SSG-OLC-MKW','Medien- und Kommunikationswissenschaft','Media and communication science',0),('SSG-OLC-MUS','Musikwissenschaft','Musicology',0),('SSG-OLC-OAS','Ost- und Südostasien','East and Southeast Asia',0),('SSG-OLC-OEB','Auswahl deutschsprachiger Zeitschriften','Selection of journals ',0),('SSG-OLC-OEU','Osteuropa','East Europe',0),('SSG-OLC-PHA','Pharmazie','Pharmacy',0),('SSG-OLC-PHI','Philosophie','Philosophy',0),('SSG-OLC-PHY','Physik','Physics',0),('SSG-OLC-POL','Politikwissenschaft und Friedensforschung','Political science and peace studies',0),('SSG-OLC-PSY','Psychologie','Psychology',0),('SSG-OLC-ROK','Romanischer Kulturkreis','Romance cultural environment',0),('SSG-OLC-SAS','Südasien','South Asia',0),('SSG-OLC-SLA','Slavistik','Slavistic studies',0),('SSG-OLC-SOW','Sozialwissenschaften','Social Sciences',0),('SSG-OLC-SPO','Sportwissenschaften','Sports sciences',0),('SSG-OLC-SPP','Spanien und Portugal','Spain and Portugal',0),('SSG-OLC-TEC','Technik','technology',0),('SSG-OLC-TGE','Technikgeschichte','History of technology',0),('SSG-OLC-UMW','Umwelt','Environment',0),('SSG-OLC-VET','Veterinärmedizin','Veterinary medicine',0),('SSG-OLC-VOR','Vorderer Orient','Middle East',0),('SSG-OLC-WIW','Wirtschaftswissenschaften','Economic sciences',0),('SSG-OLC-ZGE','Zeitgeschichte','Contemporary history',0),('STARTSEARCH','Suche starten','Start search',1),('STATUS','Status','Storage Retrieval Requests',0),('SUBJECT','Schlagwort','Subject Area',0),('SUMMARY','Zusammenfassung','Summon Results',0),('SYSTEMDETAILS','Systemvoraussetzungen','System details',0),('TITLE','Titel','Title',0),('TOC','Inhaltsverzeichnis','Table of contents',1),('TRL','ÜbersetzerIn','Translator',0),('TYPELECTR','Elektronisch','Electronic',1),('TYPPRINT','Gedruckt','Print',1),('TYPTOTAL','Komplett','Total',1),('U','bestellbar / Leihen und (Teil-)Kopie','available / loan and (partial) copy',0),('UNIFORMTITLE','Einheitstitel','Uniform Title',0),('UNKNOWN','Sonstige','Others',1),('USERFEES','Gebühren','Fees',1),('USERNAME','Benutzername','Username',1),('USERORDERS','Bestellungen','Orders',1),('USERRENTALS','Ausleihen','Rentals',1),('USERRESERVATIONS','Vormerkungen','Reservations',1),('USERSEARCHES','Gespeicherte Suchen','Stored Searches',1),('USERSTORE','Suche speichern','Store search',1),('VIEWCHANGED','Ansicht auf {col}-Spaltig gewechselt','View changed to {col} columns',1),('VOLUME','Band','Volume',0),('YEAR','Jahr','Year',1),('YOURMAIL','Ihre Mailadresse','Your Mail',1),('YOURMESSAGE','Ihre Nachricht','Your Message',1),('YOURNAME','Ihr Name','Your Name',1),('YOURSEARCH','Ihre Suche...','Your search...',1),('ZDB-1-AAS4','American Antiquarian Society (AAS) Historical Periodicals Collection: Series 4 (1853-1865)','American Antiquarian Society (AAS) Historical Periodicals Collection: Series 4 (1853-1865)',0),('ZDB-1-ACS','ACS Legacy Archives 1879-1995','ACS Legacy Archives 1879-1995',0),('ZDB-1-BCN','17th - 18th Century Burney Collection Newspapers / BBCN','17th - 18th Century Burney Collection Newspapers / BBCN',0),('ZDB-1-BEC','Brill Online / E-Books : Human Rights and Humanitarian Law ; International Law','Brill Online / E-Books : Human Rights and Humanitarian Law ; International Law',0),('ZDB-1-BJA','Brill Online / Brill Journal Archive Online','Brill Online / Brill Journal Archive Online',0),('ZDB-1-CDC','Corvey Digital Collection: Literature of the 18th and 19th Centuries','Corvey Digital Collection: Literature of the 18th and 19th Centuries',0),('ZDB-1-CEE','Central and Eastern European Online Library (C.E.E.O.L.) - Archiv','Central and Eastern European Online Library (C.E.E.O.L.) - Archiv',0),('ZDB-1-CIA','Columbia International Affairs Online (CIAO)','Columbia International Affairs Online (CIAO)',0),('ZDB-1-CUP','Cambridge Journals','Cambridge Journals',0),('ZDB-1-DFL','Deutschsprachige Frauenliteratur des 18. & 19. Jahrhunderts, Teil 1 und 2','Deutschsprachige Frauenliteratur des 18. & 19. Jahrhunderts, Teil 1 und 2',0),('ZDB-1-DGR','Walter de Gruyter Online-Zeitschriften','Walter de Gruyter Online-Zeitschriften',0),('ZDB-1-DHW','Duncker & Humblot E-Books WIRTSCHAFTSWISSENSCHAFTEN 1996–2005','Duncker & Humblot E-Books WIRTSCHAFTSWISSENSCHAFTEN 1996–2005',0),('ZDB-1-EAI','Early American Imprints : Evans 1639-1800 (Series I) / EAI I','Early American Imprints : Evans 1639-1800 (Series I) / EAI I',0),('ZDB-1-EAP','Early American Imprints : Shaw/Shoemaker 1801-1819 (Series II) / EAI II','Early American Imprints : Shaw/Shoemaker 1801-1819 (Series II) / EAI II',0),('ZDB-1-ECC','Eighteenth Century Collections Online / ECCO','Eighteenth Century Collections Online / ECCO',0),('ZDB-1-EEB','Early English Books Online / EEBO','Early English Books Online / EEBO',0),('ZDB-1-EFD','Emerald Fulltext Archive Database - 2014','Emerald Fulltext Archive Database - 2014',0),('ZDB-1-EIO','Torrossa / Monografie','Torrossa / Monografie',0),('ZDB-1-EIU','EIU Country Reports Archive','EIU Country Reports Archive',0),('ZDB-1-ELW','English Language Women\'s Literature of the 18. & 19. Centuries','English Language Women\'s Literature of the 18. & 19. Centuries',0),('ZDB-1-HRA','EHRAF World Cultures','EHRAF World Cultures',0),('ZDB-1-JAP','American Physiological Society - APS Journal Legacy Content (- Jg. 1997)','American Physiological Society - APS Journal Legacy Content (- Jg. 1997)',0),('ZDB-1-KEB','Karger eBooks Collection','Karger eBooks Collection',0),('ZDB-1-LEO','Der Literarische Expressionismus Online','Der Literarische Expressionismus Online',0),('ZDB-1-LWW','Lippincott Williams & Wilkins \'LWW Legacy Archive\' Jg.1 - 2004','Lippincott Williams & Wilkins \'LWW Legacy Archive\' Jg.1 - 2004',0),('ZDB-1-MME','Making of the Modern World: economics, politics and industry','Making of the Modern World: economics, politics and industry',0),('ZDB-1-MML','Making of Modern Law : Legal Treatises 1800-1926 / MOML 1','Making of Modern Law : Legal Treatises 1800-1926 / MOML 1',0),('ZDB-1-MYA','Mystik & Aszese des 16.-19. Jahrhunderts / Mysticism & Asceticism 16th -19th Centuries','Mystik & Aszese des 16.-19. Jahrhunderts / Mysticism & Asceticism 16th -19th Centuries',0),('ZDB-1-NEF','NetLibrary','NetLibrary',0),('ZDB-1-NEL','EBSCOhost eBook Collection','EBSCOhost eBook Collection',0),('ZDB-1-NTA','Nature Archives 1869 - 2009','Nature Archives 1869 - 2009',0),('ZDB-1-PAO','Periodicals Archive Online / PAO 1802-2000','Periodicals Archive Online / PAO 1802-2000',0),('ZDB-1-PIO','Periodicals Index Online / PIO 1739-2000','Periodicals Index Online / PIO 1739-2000',0),('ZDB-1-RSE','RSC eBook Collection 1968-2009','RSC eBook Collection 1968-2009',0),('ZDB-1-RTH','Religion & Theologie des 16.-19. Jahrhunderts / Religion & Theology 16th - 19th Centuries','Religion & Theologie des 16.-19. Jahrhunderts / Religion & Theology 16th - 19th Centuries',0),('ZDB-1-SAG','Sage Journals Online','Sage Journals Online',0),('ZDB-1-SCM','Springer ebook collection / Chemistry and Materials Science 2005-2008','Springer ebook collection / Chemistry and Materials Science 2005-2008',0),('ZDB-1-SDJ','Elsevier Journal Backfiles on ScienceDirect 1907 - 2002','Elsevier Journal Backfiles on ScienceDirect 1907 - 2002',0),('ZDB-1-SLN','Springer Lecture Notes Archiv 1964-1996','Springer Lecture Notes Archiv 1964-1996',0),('ZDB-1-SMI','Springer ebook collection / Medicine 2005-2008','Springer ebook collection / Medicine 2005-2008',0),('ZDB-1-SOJ','Springer Online Journal Archives 1860-2002','Springer Online Journal Archives 1860-2002',0),('ZDB-1-TCE','Thieme Zeitschriftenarchive 1980-2007','Thieme Zeitschriftenarchive 1980-2007',0),('ZDB-1-TFO','Taylor & Francis Online Archives 1799-2000','Taylor & Francis Online Archives 1799-2000',0),('ZDB-1-USC','U.S. Congressional Serial Set, 1817-1980','U.S. Congressional Serial Set, 1817-1980',0),('ZDB-1-WBA','World Bank E-Library Archive','World Bank E-Library Archive',0),('ZDB-1-WIS','Wiley InterScience Backfile Collections 1832-2005','Wiley InterScience Backfile Collections 1832-2005',0);
/*!40000 ALTER TABLE `translation` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-09-15 12:19:03
-- MySQL dump 10.16  Distrib 10.1.13-MariaDB, for Win32 (AMD64)
--
-- Host: localhost    Database: lukida
-- ------------------------------------------------------
-- Server version	10.1.13-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `counter`
--

DROP TABLE IF EXISTS `counter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `counter` (
  `name` varchar(100) NOT NULL,
  `value` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `counter_library`
--

DROP TABLE IF EXISTS `counter_library`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `counter_library` (
  `name` varchar(100) NOT NULL,
  `iln` int(10) NOT NULL,
  `value` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`,`iln`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `search_log`
--

DROP TABLE IF EXISTS `search_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_log` (
  `suche` varchar(200) NOT NULL DEFAULT '',
  `anzahl` int(10) NOT NULL,
  `datumzeit` datetime NOT NULL,
  PRIMARY KEY (`suche`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `search_user`
--

DROP TABLE IF EXISTS `search_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user` varchar(100) NOT NULL,
  `datumzeit` datetime NOT NULL,
  `suche` varchar(200) NOT NULL,
  `facetten` varchar(2000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stats_library`
--

DROP TABLE IF EXISTS `stats_library`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_library` (
  `iln` int(10) NOT NULL,
  `area` varchar(100) NOT NULL,
  `day` date NOT NULL,
  `hour_00` int(11) NOT NULL DEFAULT '0',
  `hour_01` int(11) NOT NULL DEFAULT '0',
  `hour_02` int(11) NOT NULL DEFAULT '0',
  `hour_03` int(11) NOT NULL DEFAULT '0',
  `hour_04` int(11) NOT NULL DEFAULT '0',
  `hour_05` int(11) NOT NULL DEFAULT '0',
  `hour_06` int(11) NOT NULL DEFAULT '0',
  `hour_07` int(11) NOT NULL DEFAULT '0',
  `hour_08` int(11) NOT NULL DEFAULT '0',
  `hour_09` int(11) NOT NULL DEFAULT '0',
  `hour_10` int(11) NOT NULL DEFAULT '0',
  `hour_11` int(11) NOT NULL DEFAULT '0',
  `hour_12` int(11) NOT NULL DEFAULT '0',
  `hour_13` int(11) NOT NULL DEFAULT '0',
  `hour_14` int(11) NOT NULL DEFAULT '0',
  `hour_15` int(11) NOT NULL DEFAULT '0',
  `hour_16` int(11) NOT NULL DEFAULT '0',
  `hour_17` int(11) NOT NULL DEFAULT '0',
  `hour_18` int(11) NOT NULL DEFAULT '0',
  `hour_19` int(11) NOT NULL DEFAULT '0',
  `hour_20` int(11) NOT NULL DEFAULT '0',
  `hour_21` int(11) NOT NULL DEFAULT '0',
  `hour_22` int(11) NOT NULL DEFAULT '0',
  `hour_23` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`iln`,`area`,`day`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `translation_library`
--

DROP TABLE IF EXISTS `translation_library`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `translation_library` (
  `shortcut` varchar(100) NOT NULL,
  `iln` int(10) NOT NULL,
  `german` varchar(2000) DEFAULT NULL,
  `english` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`iln`,`shortcut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `words`
--

DROP TABLE IF EXISTS `words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `words` (
  `wort` varchar(200) NOT NULL,
  `anzahl` int(10) NOT NULL,
  `datumzeit` datetime NOT NULL,
  PRIMARY KEY (`wort`),
  KEY `Zugriff` (`wort`,`anzahl`,`datumzeit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `words_unsolved`
--

DROP TABLE IF EXISTS `words_unsolved`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `words_unsolved` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `worte` text NOT NULL,
  `status` int(1) NOT NULL,
  `datumzeit` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=88237 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-09-15 12:19:03
