CREATE SCHEMA "user";

-- ============================================================
-- Lukida Essential — Datenbankschema
-- PostgreSQL 14+
-- Ausführen als: psql -U postgres -d lukida_essential
-- ============================================================

-- Datenbank und Benutzer anlegen (als postgres-Superuser):
--
--   CREATE DATABASE lukida_essential;
--   CREATE USER lukida_essential WITH PASSWORD 'IhrPasswortHier';
--   GRANT ALL PRIVILEGES ON DATABASE lukida_essential TO lukida_essential;
--   \connect lukida_essential
--   CREATE SCHEMA "user" AUTHORIZATION lukida_essential;
--   GRANT ALL ON SCHEMA "user" TO lukida_essential;

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

-- ============================================================
-- TABELLE: locationentries
-- Standortbezeichnungen (kurz/lang, DE/EN)
-- ============================================================

CREATE TABLE "user".locationentries (
    id integer NOT NULL,
    client character varying(50) NOT NULL,
    locid character varying(50) NOT NULL,
    art character varying(12) DEFAULT 'standort',
    sortorder integer DEFAULT 0,
    level integer DEFAULT 1,
    groupid character varying(50),
    labelshort_g character varying(255),
    labelshort_e character varying(255),
    view_g text,
    view_e text,
    updated timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    comment_g text,
    comment_e text
);

ALTER TABLE "user".locationentries ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME "user".locationentries_id_seq
    START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1
);
ALTER TABLE ONLY "user".locationentries ADD CONSTRAINT locationentries_pkey PRIMARY KEY (id);
ALTER TABLE ONLY "user".locationentries ADD CONSTRAINT locationentries_client_locid_key UNIQUE (client, locid);

-- ============================================================
-- BERECHTIGUNGEN
-- ============================================================

GRANT SELECT ON TABLE "user".adminbms TO lukida_essential;
GRANT SELECT ON TABLE "user".admincases TO lukida_essential;
GRANT SELECT ON TABLE "user".admincasesmedia TO lukida_essential;
GRANT SELECT ON TABLE "user".locationentries TO lukida_essential;
GRANT ALL ON ALL SEQUENCES IN SCHEMA "user" TO lukida_essential;
GRANT ALL ON ALL TABLES IN SCHEMA "user" TO lukida_essential;
