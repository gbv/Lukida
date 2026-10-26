-- ============================================================
-- Lukida Essential — Datenbankschema
-- PostgreSQL 14+
--
-- Als Datenbank-User der Anwendung einspielen (nicht als postgres),
-- damit ihm Schema und Tabellen gehören:
--   psql -h localhost -U lukida_essential -d lukida_essential -f schema.sql
-- ============================================================

CREATE SCHEMA "user";

-- ============================================================
-- TABELLE: adminbms
-- BMS-Verbindungen (PAIA-Endpunkte, ILN, ISIL)
-- ============================================================

CREATE TABLE "user".adminbms (
    id integer NOT NULL,
    client character varying(50) NOT NULL,
    bmsid integer NOT NULL,
    active text DEFAULT 'n' NOT NULL,
    testonly text DEFAULT 'n' NOT NULL,
    namecode character varying(100),
    type character varying(50),
    isil character varying(50),
    iln character varying(50),
    apitest character varying(500),
    apiprod character varying(500),
    options text,
    logins text
);

CREATE SEQUENCE "user".bms_id_seq
    AS integer START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;
ALTER SEQUENCE "user".bms_id_seq OWNED BY "user".adminbms.id;
ALTER TABLE ONLY "user".adminbms ALTER COLUMN id SET DEFAULT nextval('"user".bms_id_seq'::regclass);
ALTER TABLE ONLY "user".adminbms ADD CONSTRAINT bms_pkey PRIMARY KEY (id);

-- ============================================================
-- TABELLE: admincases
-- Anzeigeregeln für physische und Online-Medien
-- ============================================================

CREATE TABLE "user".admincases (
    id integer NOT NULL,
    client text NOT NULL,
    seq integer,
    name text,
    active character(1) DEFAULT 'y',
    filters jsonb,
    light text,
    itemcomments jsonb,
    itembutton text,
    itemicon text,
    detailwindow character(1) DEFAULT 'n',
    detailinfos jsonb,
    detailbutton text,
    detailaction text,
    detailaction5 text,
    ids text,
    comment text
);

CREATE SEQUENCE "user".admincases_id_seq
    AS integer START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;
ALTER SEQUENCE "user".admincases_id_seq OWNED BY "user".admincases.id;
ALTER TABLE ONLY "user".admincases ALTER COLUMN id SET DEFAULT nextval('"user".admincases_id_seq'::regclass);
ALTER TABLE ONLY "user".admincases ADD CONSTRAINT admincases_pkey PRIMARY KEY (id);

-- ============================================================
-- TABELLE: admincasesmedia
-- Anzeigeregeln für Zeitschriften und Sondermedien
-- ============================================================

CREATE TABLE "user".admincasesmedia (
    id integer NOT NULL,
    client text NOT NULL,
    seq integer,
    name text,
    active character(1) DEFAULT 'y',
    filters jsonb,
    light text,
    itemcomments jsonb,
    itembutton text,
    itemicon text,
    detailwindow character(1) DEFAULT 'n',
    detailinfos jsonb,
    detailbutton text,
    detailaction text,
    detailaction5 text,
    ids text,
    comment text
);

CREATE SEQUENCE "user".admincasesmedia_id_seq
    AS integer START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;
ALTER SEQUENCE "user".admincasesmedia_id_seq OWNED BY "user".admincasesmedia.id;
ALTER TABLE ONLY "user".admincasesmedia ALTER COLUMN id SET DEFAULT nextval('"user".admincasesmedia_id_seq'::regclass);
ALTER TABLE ONLY "user".admincasesmedia ADD CONSTRAINT admincasesmedia_pkey PRIMARY KEY (id);

-- ============================================================
-- TABELLE: adminconfigs
-- Solr-Verbindung und sonstige Einstellungen pro Client
-- ============================================================

CREATE TABLE "user".adminconfigs (
    id integer NOT NULL,
    client character varying(100) NOT NULL,
    category character varying(100) NOT NULL,
    setting character varying(100) NOT NULL,
    value text
);

CREATE SEQUENCE "user".adminconfigs_id_seq
    AS integer START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;
ALTER SEQUENCE "user".adminconfigs_id_seq OWNED BY "user".adminconfigs.id;
ALTER TABLE ONLY "user".adminconfigs ALTER COLUMN id SET DEFAULT nextval('"user".adminconfigs_id_seq'::regclass);
ALTER TABLE ONLY "user".adminconfigs ADD CONSTRAINT adminconfigs_pkey PRIMARY KEY (id);

-- ============================================================
-- TABELLE: adminlinks
-- Konfigurierbare Links pro Client (link = "url-deutsch,url-englisch")
-- ============================================================

CREATE TABLE "user".adminlinks (
    id integer NOT NULL,
    client character varying(100) NOT NULL,
    shortcut character varying(100) NOT NULL,
    german text,
    english text,
    link text
);

CREATE SEQUENCE "user".adminlinks_id_seq
    AS integer START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;
ALTER SEQUENCE "user".adminlinks_id_seq OWNED BY "user".adminlinks.id;
ALTER TABLE ONLY "user".adminlinks ALTER COLUMN id SET DEFAULT nextval('"user".adminlinks_id_seq'::regclass);
ALTER TABLE ONLY "user".adminlinks ADD CONSTRAINT adminlinks_pkey PRIMARY KEY (id);

-- ============================================================
-- TABELLE: admintexts
-- Texte und Beschriftungen pro Client (deutsch / englisch)
-- ============================================================

CREATE TABLE "user".admintexts (
    id integer NOT NULL,
    client character varying(100) NOT NULL,
    shortcut character varying(100) NOT NULL,
    german text,
    english text
);

CREATE SEQUENCE "user".admintexts_id_seq
    AS integer START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;
ALTER SEQUENCE "user".admintexts_id_seq OWNED BY "user".admintexts.id;
ALTER TABLE ONLY "user".admintexts ALTER COLUMN id SET DEFAULT nextval('"user".admintexts_id_seq'::regclass);
ALTER TABLE ONLY "user".admintexts ADD CONSTRAINT admintexts_pkey PRIMARY KEY (id);

-- ============================================================
-- TABELLE: admindialogs
-- Formulare (z. B. Bestellung, Vormerkung) und ihre Empfänger-Adressen pro Client
-- ============================================================

CREATE TABLE "user".admindialogs (
    id integer NOT NULL,
    client character varying(100) NOT NULL,
    active character varying(1) DEFAULT 'y',
    action jsonb DEFAULT '{}',
    mailprod text DEFAULT '',
    mailtest text DEFAULT ''
);

CREATE SEQUENCE "user".admindialogs_id_seq
    AS integer START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;
ALTER SEQUENCE "user".admindialogs_id_seq OWNED BY "user".admindialogs.id;
ALTER TABLE ONLY "user".admindialogs ALTER COLUMN id SET DEFAULT nextval('"user".admindialogs_id_seq'::regclass);
ALTER TABLE ONLY "user".admindialogs ADD CONSTRAINT admindialogs_pkey PRIMARY KEY (id);

-- ============================================================
-- TABELLE: admindialogfields
-- Felder der Formulare aus admindialogs
-- ============================================================

CREATE TABLE "user".admindialogfields (
    id integer NOT NULL,
    client character varying(100) NOT NULL,
    active character varying(1) DEFAULT 'y',
    dialogcode character varying(100) NOT NULL,
    fieldcode character varying(100) NOT NULL,
    seq integer DEFAULT 0,
    fieldtype character varying(50) DEFAULT 'text',
    fieldoptions jsonb DEFAULT '{}',
    editable character varying(1) DEFAULT 'y',
    autofill character varying(1) DEFAULT 'n',
    rules jsonb DEFAULT '{}'
);

CREATE SEQUENCE "user".admindialogfields_id_seq
    AS integer START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;
ALTER SEQUENCE "user".admindialogfields_id_seq OWNED BY "user".admindialogfields.id;
ALTER TABLE ONLY "user".admindialogfields ALTER COLUMN id SET DEFAULT nextval('"user".admindialogfields_id_seq'::regclass);
ALTER TABLE ONLY "user".admindialogfields ADD CONSTRAINT admindialogfields_pkey PRIMARY KEY (id);

-- ============================================================
-- TABELLE: adminlevels
-- Zugriffsebenen: Kollektionen, ILNs, IP-Ranges
-- ============================================================

CREATE TABLE "user".adminlevels (
    id integer NOT NULL,
    client character varying(100),
    level character varying(100),
    collectionsaddfull jsonb DEFAULT '[]',
    collectionsadd jsonb DEFAULT '[]',
    collectionsrem jsonb DEFAULT '[]',
    holdingilns text DEFAULT '',
    holdingisils text DEFAULT '',
    holdingshaptic text DEFAULT '',
    locations text DEFAULT '',
    classlocals text DEFAULT '',
    buildings text DEFAULT '',
    dois text DEFAULT '',
    sort integer DEFAULT 100,
    ipranges jsonb DEFAULT '[]'
);

CREATE SEQUENCE "user".adminlevels_id_seq
    AS integer START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;
ALTER SEQUENCE "user".adminlevels_id_seq OWNED BY "user".adminlevels.id;
ALTER TABLE ONLY "user".adminlevels ALTER COLUMN id SET DEFAULT nextval('"user".adminlevels_id_seq'::regclass);
ALTER TABLE ONLY "user".adminlevels ADD CONSTRAINT adminlevels_pkey PRIMARY KEY (id);
