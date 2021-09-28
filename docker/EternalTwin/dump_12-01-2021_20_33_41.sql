--
-- PostgreSQL database dump
--

-- Dumped from database version 13.1 (Debian 13.1-1.pgdg100+1)
-- Dumped by pg_dump version 13.1 (Debian 13.1-1.pgdg100+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: btree_gist; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS btree_gist WITH SCHEMA public;


--
-- Name: EXTENSION btree_gist; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION btree_gist IS 'support for indexing common datatypes in GiST';


--
-- Name: pgcrypto; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;


--
-- Name: EXTENSION pgcrypto; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION pgcrypto IS 'cryptographic functions';


--
-- Name: announcement_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.announcement_id AS uuid;


ALTER DOMAIN public.announcement_id OWNER TO "etwin.dev.admin";

--
-- Name: u16; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.u16 AS integer
	CONSTRAINT u16_check CHECK (((0 <= VALUE) AND (VALUE < 65536)));


ALTER DOMAIN public.u16 OWNER TO "etwin.dev.admin";

--
-- Name: raw_dinoparc_dinoz_elements; Type: TYPE; Schema: public; Owner: etwin.dev.admin
--

CREATE TYPE public.raw_dinoparc_dinoz_elements AS (
	fire public.u16,
	earth public.u16,
	water public.u16,
	thunder public.u16,
	air public.u16
);


ALTER TYPE public.raw_dinoparc_dinoz_elements OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_dinoz_elements; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_dinoz_elements AS public.raw_dinoparc_dinoz_elements
	CONSTRAINT dinoparc_dinoz_elements_check CHECK (((VALUE IS NULL) OR (((VALUE).fire IS NOT NULL) AND ((VALUE).earth IS NOT NULL) AND ((VALUE).water IS NOT NULL) AND ((VALUE).thunder IS NOT NULL) AND ((VALUE).air IS NOT NULL))));


ALTER DOMAIN public.dinoparc_dinoz_elements OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_dinoz_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_dinoz_id AS character varying(10)
	CONSTRAINT dinoparc_dinoz_id_check CHECK (((VALUE)::text ~ '^(?:0|[1-9]\d{0,9})$'::text));


ALTER DOMAIN public.dinoparc_dinoz_id OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_dinoz_name; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_dinoz_name AS character varying(100);


ALTER DOMAIN public.dinoparc_dinoz_name OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_dinoz_race; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_dinoz_race AS character varying(50)
	CONSTRAINT dinoparc_dinoz_race_check CHECK (((VALUE)::text = ANY ((ARRAY['Cargou'::character varying, 'Castivore'::character varying, 'Gorriloz'::character varying, 'Gorilloz'::character varying, 'Gluon'::character varying, 'Hippoclamp'::character varying, 'Kabuki'::character varying, 'Korgon'::character varying, 'Kump'::character varying, 'Moueffe'::character varying, 'Ouistiti'::character varying, 'Picori'::character varying, 'Pigmou'::character varying, 'Pteroz'::character varying, 'Rokky'::character varying, 'Santaz'::character varying, 'Serpantin'::character varying, 'Sirain'::character varying, 'Wanwan'::character varying, 'Winks'::character varying])::text[])));


ALTER DOMAIN public.dinoparc_dinoz_race OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_dinoz_skin; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_dinoz_skin AS character varying(100);


ALTER DOMAIN public.dinoparc_dinoz_skin OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_epic_reward_key; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_epic_reward_key AS character varying(30)
	CONSTRAINT dinoparc_epic_reward_key_check CHECK (((VALUE)::text ~ '^[a-z0-9_]{1,30}$'::text));


ALTER DOMAIN public.dinoparc_epic_reward_key OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_epic_reward_set_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_epic_reward_set_id AS uuid;


ALTER DOMAIN public.dinoparc_epic_reward_set_id OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_item_count_map_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_item_count_map_id AS uuid;


ALTER DOMAIN public.dinoparc_item_count_map_id OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_item_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_item_id AS character varying(10)
	CONSTRAINT dinoparc_item_id_check CHECK (((VALUE)::text ~ '^[1-9]\d{0,9}$'::text));


ALTER DOMAIN public.dinoparc_item_id OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_location_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_location_id AS character varying(2)
	CONSTRAINT dinoparc_location_id_check CHECK (((VALUE)::text ~ '^\d{1,2}$'::text));


ALTER DOMAIN public.dinoparc_location_id OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_reward_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_reward_id AS character varying(10)
	CONSTRAINT dinoparc_reward_id_check CHECK (((VALUE)::text ~ '^[1-9]\d?$'::text));


ALTER DOMAIN public.dinoparc_reward_id OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_reward_set_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_reward_set_id AS uuid;


ALTER DOMAIN public.dinoparc_reward_set_id OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_server; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_server AS character varying(15)
	CONSTRAINT dinoparc_server_check CHECK (((VALUE)::text = ANY ((ARRAY['dinoparc.com'::character varying, 'en.dinoparc.com'::character varying, 'sp.dinoparc.com'::character varying])::text[])));


ALTER DOMAIN public.dinoparc_server OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_session_key; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_session_key AS character varying(32)
	CONSTRAINT dinoparc_session_key_check CHECK (((VALUE)::text ~ '^[0-9a-zA-Z]{32}$'::text));


ALTER DOMAIN public.dinoparc_session_key OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_skill; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_skill AS character varying(50)
	CONSTRAINT dinoparc_skill_check CHECK (((VALUE)::text = ANY ((ARRAY['Bargain'::character varying, 'Camouflage'::character varying, 'Climb'::character varying, 'Cook'::character varying, 'Counterattack'::character varying, 'Dexterity'::character varying, 'Dig'::character varying, 'EarthApprentice'::character varying, 'FireApprentice'::character varying, 'FireProtection'::character varying, 'Intelligence'::character varying, 'Juggle'::character varying, 'Jump'::character varying, 'Luck'::character varying, 'MartialArts'::character varying, 'Medicine'::character varying, 'Mercenary'::character varying, 'Music'::character varying, 'Navigation'::character varying, 'Perception'::character varying, 'Provoke'::character varying, 'Run'::character varying, 'Saboteur'::character varying, 'ShadowPower'::character varying, 'Spy'::character varying, 'Stamina'::character varying, 'Steal'::character varying, 'Strategy'::character varying, 'Strength'::character varying, 'Survival'::character varying, 'Swim'::character varying, 'TotemThief'::character varying, 'ThunderApprentice'::character varying, 'WaterApprentice'::character varying])::text[])));


ALTER DOMAIN public.dinoparc_skill OWNER TO "etwin.dev.admin";

--
-- Name: u8; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.u8 AS smallint
	CONSTRAINT u8_check CHECK (((0 <= VALUE) AND (VALUE < 256)));


ALTER DOMAIN public.u8 OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_skill_level; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_skill_level AS public.u8
	CONSTRAINT dinoparc_skill_level_check CHECK (((VALUE)::smallint <= 5));


ALTER DOMAIN public.dinoparc_skill_level OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_skill_level_map_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_skill_level_map_id AS uuid;


ALTER DOMAIN public.dinoparc_skill_level_map_id OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_user_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_user_id AS character varying(10)
	CONSTRAINT dinoparc_user_id_check CHECK (((VALUE)::text ~ '^(?:0|[1-9]\d{0,9})$'::text));


ALTER DOMAIN public.dinoparc_user_id OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_username; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.dinoparc_username AS character varying(20);


ALTER DOMAIN public.dinoparc_username OWNER TO "etwin.dev.admin";

--
-- Name: email_address; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.email_address AS text;


ALTER DOMAIN public.email_address OWNER TO "etwin.dev.admin";

--
-- Name: email_address_enc; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.email_address_enc AS bytea;


ALTER DOMAIN public.email_address_enc OWNER TO "etwin.dev.admin";

--
-- Name: email_address_hash; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.email_address_hash AS bytea;


ALTER DOMAIN public.email_address_hash OWNER TO "etwin.dev.admin";

--
-- Name: forum_thread_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.forum_thread_id AS uuid;


ALTER DOMAIN public.forum_thread_id OWNER TO "etwin.dev.admin";

--
-- Name: raw_hammerfest_date; Type: TYPE; Schema: public; Owner: etwin.dev.admin
--

CREATE TYPE public.raw_hammerfest_date AS (
	month public.u8,
	day public.u8,
	isodow public.u8
);


ALTER TYPE public.raw_hammerfest_date OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_date; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_date AS public.raw_hammerfest_date
	CONSTRAINT hammerfest_date_check CHECK (((VALUE IS NULL) OR (((VALUE).month IS NOT NULL) AND ((VALUE).day IS NOT NULL) AND ((VALUE).isodow IS NOT NULL))));


ALTER DOMAIN public.hammerfest_date OWNER TO "etwin.dev.admin";

--
-- Name: raw_hammerfest_date_time; Type: TYPE; Schema: public; Owner: etwin.dev.admin
--

CREATE TYPE public.raw_hammerfest_date_time AS (
	month public.u8,
	day public.u8,
	isodow public.u8,
	hour public.u8,
	minute public.u8
);


ALTER TYPE public.raw_hammerfest_date_time OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_date_time; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_date_time AS public.raw_hammerfest_date_time
	CONSTRAINT hammerfest_date_time_check CHECK (((VALUE IS NULL) OR (((VALUE).month IS NOT NULL) AND (1 <= ((VALUE).month)::smallint) AND (((VALUE).month)::smallint <= 12) AND ((VALUE).day IS NOT NULL) AND (1 <= ((VALUE).day)::smallint) AND (((VALUE).day)::smallint <= 31) AND ((VALUE).isodow IS NOT NULL) AND (1 <= ((VALUE).isodow)::smallint) AND (((VALUE).isodow)::smallint <= 7) AND ((VALUE).hour IS NOT NULL) AND (0 <= ((VALUE).hour)::smallint) AND (((VALUE).hour)::smallint <= 23) AND ((VALUE).minute IS NOT NULL) AND (0 <= ((VALUE).minute)::smallint) AND (((VALUE).minute)::smallint <= 59))));


ALTER DOMAIN public.hammerfest_date_time OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_post_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_forum_post_id AS character varying(10)
	CONSTRAINT hammerfest_forum_message_id_check CHECK (((VALUE)::text ~ '^[1-9]\d{0,9}$'::text));


ALTER DOMAIN public.hammerfest_forum_post_id OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_role; Type: TYPE; Schema: public; Owner: etwin.dev.admin
--

CREATE TYPE public.hammerfest_forum_role AS ENUM (
    'None',
    'Moderator',
    'Administrator'
);


ALTER TYPE public.hammerfest_forum_role OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_theme_description; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_forum_theme_description AS character varying(500);


ALTER DOMAIN public.hammerfest_forum_theme_description OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_theme_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_forum_theme_id AS character varying(10)
	CONSTRAINT hammerfest_forum_theme_id_check CHECK (((VALUE)::text ~ '^[1-9]\d{0,9}$'::text));


ALTER DOMAIN public.hammerfest_forum_theme_id OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_theme_title; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_forum_theme_title AS character varying(100);


ALTER DOMAIN public.hammerfest_forum_theme_title OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_thread_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_forum_thread_id AS character varying(10)
	CONSTRAINT hammerfest_forum_thread_id_check CHECK (((VALUE)::text ~ '^[1-9]\d{0,9}$'::text));


ALTER DOMAIN public.hammerfest_forum_thread_id OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_thread_title; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_forum_thread_title AS character varying(100);


ALTER DOMAIN public.hammerfest_forum_thread_title OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_item_count_map_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_item_count_map_id AS uuid;


ALTER DOMAIN public.hammerfest_item_count_map_id OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_item_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_item_id AS character varying(4)
	CONSTRAINT hammerfest_item_id_check CHECK (((VALUE)::text ~ '^(?:0|[1-9]\d{0,3})$'::text));


ALTER DOMAIN public.hammerfest_item_id OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_ladder_level; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_ladder_level AS public.u8
	CONSTRAINT hammerfest_ladder_level_check CHECK (((VALUE)::smallint < 5));


ALTER DOMAIN public.hammerfest_ladder_level OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_quest_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_quest_id AS character varying(4)
	CONSTRAINT hammerfest_quest_id_check CHECK (((VALUE)::text ~ '^(?:0|[1-9]\d{0,3})$'::text));


ALTER DOMAIN public.hammerfest_quest_id OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_quest_status; Type: TYPE; Schema: public; Owner: etwin.dev.admin
--

CREATE TYPE public.hammerfest_quest_status AS ENUM (
    'None',
    'Pending',
    'Complete'
);


ALTER TYPE public.hammerfest_quest_status OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_quest_status_map_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_quest_status_map_id AS uuid;


ALTER DOMAIN public.hammerfest_quest_status_map_id OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_server; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_server AS character varying(13)
	CONSTRAINT hammerfest_server_check CHECK (((VALUE)::text = ANY ((ARRAY['hammerfest.es'::character varying, 'hammerfest.fr'::character varying, 'hfest.net'::character varying])::text[])));


ALTER DOMAIN public.hammerfest_server OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_session_key; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_session_key AS character varying(26)
	CONSTRAINT hammerfest_session_key_check CHECK (((VALUE)::text ~ '^[0-9a-z]{26}$'::text));


ALTER DOMAIN public.hammerfest_session_key OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_unlocked_item_set_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_unlocked_item_set_id AS uuid;


ALTER DOMAIN public.hammerfest_unlocked_item_set_id OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_user_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_user_id AS character varying(10)
	CONSTRAINT hammerfest_user_id_check CHECK (((VALUE)::text ~ '^[1-9]\d{0,9}$'::text));


ALTER DOMAIN public.hammerfest_user_id OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_username; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.hammerfest_username AS character varying(20)
	CONSTRAINT hammerfest_username_check CHECK (((VALUE)::text ~ '^[0-9A-Za-z]{1,12}$'::text));


ALTER DOMAIN public.hammerfest_username OWNER TO "etwin.dev.admin";

--
-- Name: i16; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.i16 AS smallint;


ALTER DOMAIN public.i16 OWNER TO "etwin.dev.admin";

--
-- Name: i32; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.i32 AS integer;


ALTER DOMAIN public.i32 OWNER TO "etwin.dev.admin";

--
-- Name: i64; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.i64 AS bigint;


ALTER DOMAIN public.i64 OWNER TO "etwin.dev.admin";

--
-- Name: i8; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.i8 AS smallint
	CONSTRAINT i8_check CHECK ((('-128'::integer <= VALUE) AND (VALUE < 128)));


ALTER DOMAIN public.i8 OWNER TO "etwin.dev.admin";

--
-- Name: instant; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.instant AS timestamp(3) with time zone;


ALTER DOMAIN public.instant OWNER TO "etwin.dev.admin";

--
-- Name: array_is_ordered_set(anyarray); Type: FUNCTION; Schema: public; Owner: etwin.dev.admin
--

CREATE FUNCTION public.array_is_ordered_set(arr anyarray) RETURNS boolean
    LANGUAGE sql IMMUTABLE STRICT PARALLEL SAFE
    AS $$
SELECT arr = (
  SELECT ARRAY_AGG(item)
  FROM (
    SELECT DISTINCT UNNEST(arr) AS item
    ORDER BY item ASC
  ) AS items
  WHERE item IS NOT NULL
);
$$;


ALTER FUNCTION public.array_is_ordered_set(arr anyarray) OWNER TO "etwin.dev.admin";

--
-- Name: instant_set; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.instant_set AS public.instant[]
	CONSTRAINT instant_set_check CHECK (public.array_is_ordered_set(VALUE));


ALTER DOMAIN public.instant_set OWNER TO "etwin.dev.admin";

--
-- Name: int_percentage; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.int_percentage AS public.u8
	CONSTRAINT int_percentage_check CHECK (((VALUE)::smallint <= 100));


ALTER DOMAIN public.int_percentage OWNER TO "etwin.dev.admin";

--
-- Name: locale_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.locale_id AS character varying(10);


ALTER DOMAIN public.locale_id OWNER TO "etwin.dev.admin";

--
-- Name: password_hash; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.password_hash AS bytea;


ALTER DOMAIN public.password_hash OWNER TO "etwin.dev.admin";

--
-- Name: period; Type: TYPE; Schema: public; Owner: etwin.dev.admin
--

CREATE TYPE public.period AS RANGE (
    subtype = public.instant
);


ALTER TYPE public.period OWNER TO "etwin.dev.admin";

--
-- Name: period_lower; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.period_lower AS public.period
	CONSTRAINT period_from_check CHECK (((NOT lower_inf(VALUE)) AND lower_inc(VALUE) AND (NOT upper_inc(VALUE))));


ALTER DOMAIN public.period_lower OWNER TO "etwin.dev.admin";

--
-- Name: raw_schema_meta; Type: TYPE; Schema: public; Owner: etwin.dev.admin
--

CREATE TYPE public.raw_schema_meta AS (
	version integer
);


ALTER TYPE public.raw_schema_meta OWNER TO "etwin.dev.admin";

--
-- Name: rfc_oauth_access_token_key; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.rfc_oauth_access_token_key AS text;


ALTER DOMAIN public.rfc_oauth_access_token_key OWNER TO "etwin.dev.admin";

--
-- Name: rfc_oauth_refresh_token_key; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.rfc_oauth_refresh_token_key AS text;


ALTER DOMAIN public.rfc_oauth_refresh_token_key OWNER TO "etwin.dev.admin";

--
-- Name: schema_meta; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.schema_meta AS public.raw_schema_meta
	CONSTRAINT schema_meta_check CHECK ((((VALUE).version IS NOT NULL) AND ((VALUE).version >= 1)));


ALTER DOMAIN public.schema_meta OWNER TO "etwin.dev.admin";

--
-- Name: twinoid_user_display_name; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.twinoid_user_display_name AS character varying(50);


ALTER DOMAIN public.twinoid_user_display_name OWNER TO "etwin.dev.admin";

--
-- Name: twinoid_user_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.twinoid_user_id AS character varying(10)
	CONSTRAINT twinoid_user_id_check CHECK (((VALUE)::text ~ '^[1-9]\d{0,9}$'::text));


ALTER DOMAIN public.twinoid_user_id OWNER TO "etwin.dev.admin";

--
-- Name: u32; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.u32 AS bigint
	CONSTRAINT u32_check CHECK (((0 <= VALUE) AND (VALUE < '4294967296'::bigint)));


ALTER DOMAIN public.u32 OWNER TO "etwin.dev.admin";

--
-- Name: user_display_name; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.user_display_name AS character varying(64);


ALTER DOMAIN public.user_display_name OWNER TO "etwin.dev.admin";

--
-- Name: user_id; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.user_id AS uuid;


ALTER DOMAIN public.user_id OWNER TO "etwin.dev.admin";

--
-- Name: username; Type: DOMAIN; Schema: public; Owner: etwin.dev.admin
--

CREATE DOMAIN public.username AS character varying(64);


ALTER DOMAIN public.username OWNER TO "etwin.dev.admin";

--
-- Name: array_is_sampled_instant_set(public.instant[], interval); Type: FUNCTION; Schema: public; Owner: etwin.dev.admin
--

CREATE FUNCTION public.array_is_sampled_instant_set(arr public.instant[], sampling_window interval) RETURNS boolean
    LANGUAGE sql IMMUTABLE STRICT PARALLEL SAFE
    AS $$
SELECT array_is_ordered_set(arr) AND (
  SELECT MAX(sample_count_in_window)
  FROM (
    SELECT COUNT(item) OVER (ORDER BY item RANGE sampling_window PRECEDING) AS sample_count_in_window
    FROM (
      SELECT UNNEST(arr) AS item
    ) AS items
  ) AS counts
) <= 2;
$$;


ALTER FUNCTION public.array_is_sampled_instant_set(arr public.instant[], sampling_window interval) OWNER TO "etwin.dev.admin";

--
-- Name: const_sampling_window(); Type: FUNCTION; Schema: public; Owner: etwin.dev.admin
--

CREATE FUNCTION public.const_sampling_window() RETURNS interval
    LANGUAGE sql IMMUTABLE STRICT PARALLEL SAFE
    AS $$
SELECT '1day'::INTERVAL
$$;


ALTER FUNCTION public.const_sampling_window() OWNER TO "etwin.dev.admin";

--
-- Name: get_schema_meta(); Type: FUNCTION; Schema: public; Owner: etwin.dev.admin
--

CREATE FUNCTION public.get_schema_meta() RETURNS public.schema_meta
    LANGUAGE sql IMMUTABLE STRICT PARALLEL SAFE
    AS $$ SELECT ROW(21); $$;


ALTER FUNCTION public.get_schema_meta() OWNER TO "etwin.dev.admin";

--
-- Name: ordered_set_insert(anyarray, anyelement); Type: FUNCTION; Schema: public; Owner: etwin.dev.admin
--

CREATE FUNCTION public.ordered_set_insert(arr anyarray, new_value anyelement) RETURNS anyarray
    LANGUAGE sql IMMUTABLE STRICT PARALLEL SAFE
    AS $$
SELECT ARRAY_AGG(item)
FROM (
  SELECT DISTINCT UNNEST(array_append(arr, new_value)) AS item
  ORDER BY item ASC
) AS items;
$$;


ALTER FUNCTION public.ordered_set_insert(arr anyarray, new_value anyelement) OWNER TO "etwin.dev.admin";

--
-- Name: sampled_instant_set_insert_back(public.instant[], interval, public.instant); Type: FUNCTION; Schema: public; Owner: etwin.dev.admin
--

CREATE FUNCTION public.sampled_instant_set_insert_back(arr public.instant[], sampling_window interval, new_value public.instant) RETURNS public.instant[]
    LANGUAGE sql IMMUTABLE STRICT PARALLEL SAFE
    AS $$
SELECT CASE WHEN ARRAY_LENGTH(arr, 1) = 0
              THEN ARRAY [new_value]
            WHEN ARRAY_LENGTH(arr, 1) = 1 AND arr[1] <> new_value
              THEN arr || new_value
            WHEN ARRAY_LENGTH(arr, 1) >= 1 AND arr[ARRAY_LENGTH(arr, 1)] = new_value
              THEN arr
            WHEN ARRAY_LENGTH(arr, 1) >= 2 AND new_value - arr[ARRAY_LENGTH(arr, 1) - 1] < sampling_window
              THEN arr[1:ARRAY_LENGTH(arr, 1) - 1] || new_value
            ELSE arr || new_value END
$$;


ALTER FUNCTION public.sampled_instant_set_insert_back(arr public.instant[], sampling_window interval, new_value public.instant) OWNER TO "etwin.dev.admin";

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: _post_formatting_costs; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public._post_formatting_costs (
    forum_post_revision_id uuid NOT NULL,
    formatting character varying(20) NOT NULL,
    cost integer,
    CONSTRAINT _post_formatting_costs_cost_check CHECK ((cost > 0))
);


ALTER TABLE public._post_formatting_costs OWNER TO "etwin.dev.admin";

--
-- Name: announcements; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.announcements (
    announcement_id public.announcement_id NOT NULL,
    forum_thread_id public.forum_thread_id NOT NULL,
    locale public.locale_id,
    created_at public.instant NOT NULL,
    created_by public.user_id NOT NULL
);


ALTER TABLE public.announcements OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_bills; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_bills (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_user_id public.dinoparc_user_id NOT NULL,
    bills public.u32 NOT NULL
);


ALTER TABLE public.dinoparc_bills OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_coins; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_coins (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_user_id public.dinoparc_user_id NOT NULL,
    coins public.u32 NOT NULL
);


ALTER TABLE public.dinoparc_coins OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_collections; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_collections (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_user_id public.dinoparc_user_id NOT NULL,
    dinoparc_reward_set_id public.dinoparc_reward_set_id NOT NULL,
    dinoparc_epic_reward_set_id public.dinoparc_epic_reward_set_id NOT NULL
);


ALTER TABLE public.dinoparc_collections OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_dinoz; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_dinoz (
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_dinoz_id public.dinoparc_dinoz_id NOT NULL,
    archived_at public.instant NOT NULL
);


ALTER TABLE public.dinoparc_dinoz OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_dinoz_levels; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_dinoz_levels (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_dinoz_id public.dinoparc_dinoz_id NOT NULL,
    level public.u16 NOT NULL
);


ALTER TABLE public.dinoparc_dinoz_levels OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_dinoz_locations; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_dinoz_locations (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_dinoz_id public.dinoparc_dinoz_id NOT NULL,
    location public.dinoparc_location_id NOT NULL
);


ALTER TABLE public.dinoparc_dinoz_locations OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_dinoz_names; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_dinoz_names (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_dinoz_id public.dinoparc_dinoz_id NOT NULL,
    name public.dinoparc_dinoz_name
);


ALTER TABLE public.dinoparc_dinoz_names OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_dinoz_owners; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_dinoz_owners (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_dinoz_id public.dinoparc_dinoz_id NOT NULL,
    owner public.dinoparc_user_id NOT NULL
);


ALTER TABLE public.dinoparc_dinoz_owners OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_dinoz_profiles; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_dinoz_profiles (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_dinoz_id public.dinoparc_dinoz_id NOT NULL,
    life public.int_percentage NOT NULL,
    experience public.int_percentage NOT NULL,
    danger public.i16 NOT NULL,
    in_tournament boolean NOT NULL,
    elements public.dinoparc_dinoz_elements NOT NULL,
    skills public.dinoparc_skill_level_map_id NOT NULL
);


ALTER TABLE public.dinoparc_dinoz_profiles OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_dinoz_skins; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_dinoz_skins (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_dinoz_id public.dinoparc_dinoz_id NOT NULL,
    race public.dinoparc_dinoz_race NOT NULL,
    skin public.dinoparc_dinoz_skin NOT NULL
);


ALTER TABLE public.dinoparc_dinoz_skins OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_epic_reward_set_items; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_epic_reward_set_items (
    dinoparc_epic_reward_set_id public.dinoparc_epic_reward_set_id NOT NULL,
    dinoparc_epic_reward_key public.dinoparc_epic_reward_key NOT NULL
);


ALTER TABLE public.dinoparc_epic_reward_set_items OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_epic_reward_sets; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_epic_reward_sets (
    dinoparc_epic_reward_set_id public.dinoparc_epic_reward_set_id NOT NULL,
    _sha3_256 bytea NOT NULL
);


ALTER TABLE public.dinoparc_epic_reward_sets OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_inventories; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_inventories (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_user_id public.dinoparc_user_id NOT NULL,
    item_counts public.dinoparc_item_count_map_id NOT NULL
);


ALTER TABLE public.dinoparc_inventories OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_item_count_map_items; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_item_count_map_items (
    dinoparc_item_count_map_id public.dinoparc_item_count_map_id NOT NULL,
    dinoparc_item_id public.dinoparc_item_id NOT NULL,
    count public.u32 NOT NULL
);


ALTER TABLE public.dinoparc_item_count_map_items OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_item_count_maps; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_item_count_maps (
    dinoparc_item_count_map_id public.dinoparc_item_count_map_id NOT NULL,
    _sha3_256 bytea NOT NULL
);


ALTER TABLE public.dinoparc_item_count_maps OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_locations; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_locations (
    dinoparc_location_id public.dinoparc_location_id NOT NULL
);


ALTER TABLE public.dinoparc_locations OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_reward_set_items; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_reward_set_items (
    dinoparc_reward_set_id public.dinoparc_reward_set_id NOT NULL,
    dinoparc_reward_id public.dinoparc_reward_id NOT NULL
);


ALTER TABLE public.dinoparc_reward_set_items OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_reward_sets; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_reward_sets (
    dinoparc_reward_set_id public.dinoparc_reward_set_id NOT NULL,
    _sha3_256 bytea NOT NULL
);


ALTER TABLE public.dinoparc_reward_sets OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_servers; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_servers (
    dinoparc_server public.dinoparc_server NOT NULL
);


ALTER TABLE public.dinoparc_servers OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_sessions; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_sessions (
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_session_key bytea NOT NULL,
    _dinoparc_session_key_hash bytea NOT NULL,
    dinoparc_user_id public.dinoparc_user_id NOT NULL,
    ctime public.instant NOT NULL,
    atime public.instant NOT NULL,
    CONSTRAINT dinoparc_sessions_check CHECK (((atime)::timestamp with time zone >= (ctime)::timestamp with time zone))
);


ALTER TABLE public.dinoparc_sessions OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_skill_level_map_items; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_skill_level_map_items (
    dinoparc_skill_level_map_id public.dinoparc_skill_level_map_id NOT NULL,
    dinoparc_skill public.dinoparc_skill NOT NULL,
    level public.dinoparc_skill_level NOT NULL
);


ALTER TABLE public.dinoparc_skill_level_map_items OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_skill_level_maps; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_skill_level_maps (
    dinoparc_skill_level_map_id public.dinoparc_skill_level_map_id NOT NULL,
    _sha3_256 bytea NOT NULL
);


ALTER TABLE public.dinoparc_skill_level_maps OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_user_dinoz; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_user_dinoz (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_user_id public.dinoparc_user_id NOT NULL,
    offset_in_list public.u32 NOT NULL,
    dinoparc_dinoz_id public.dinoparc_dinoz_id NOT NULL
);


ALTER TABLE public.dinoparc_user_dinoz OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_user_dinoz_counts; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_user_dinoz_counts (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_user_id public.dinoparc_user_id NOT NULL,
    dinoz_count public.u32 NOT NULL
);


ALTER TABLE public.dinoparc_user_dinoz_counts OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_user_links; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_user_links (
    user_id public.user_id NOT NULL,
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_user_id public.dinoparc_user_id NOT NULL,
    linked_by public.user_id NOT NULL,
    period public.period_lower NOT NULL,
    unlinked_by public.user_id,
    CONSTRAINT dinoparc_user_links_check CHECK (((upper_inf((period)::public.period) AND (unlinked_by IS NULL)) OR ((NOT upper_inf((period)::public.period)) AND (unlinked_by IS NOT NULL))))
);


ALTER TABLE public.dinoparc_user_links OWNER TO "etwin.dev.admin";

--
-- Name: dinoparc_users; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.dinoparc_users (
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_user_id public.dinoparc_user_id NOT NULL,
    username public.dinoparc_username NOT NULL,
    archived_at public.instant NOT NULL
);


ALTER TABLE public.dinoparc_users OWNER TO "etwin.dev.admin";

--
-- Name: email_addresses; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.email_addresses (
    email_address public.email_address_enc NOT NULL,
    _hash public.email_address_hash NOT NULL,
    created_at public.instant NOT NULL
);


ALTER TABLE public.email_addresses OWNER TO "etwin.dev.admin";

--
-- Name: email_verifications; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.email_verifications (
    user_id uuid NOT NULL,
    email_address bytea NOT NULL,
    ctime public.instant NOT NULL,
    validation_time public.instant,
    CONSTRAINT email_verifications_check CHECK (((validation_time)::timestamp with time zone >= (ctime)::timestamp with time zone))
);


ALTER TABLE public.email_verifications OWNER TO "etwin.dev.admin";

--
-- Name: forum_post_revisions; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.forum_post_revisions (
    forum_post_revision_id uuid NOT NULL,
    "time" public.instant,
    body text,
    _html_body text,
    mod_body text,
    _html_mod_body text,
    forum_post_id uuid NOT NULL,
    author_id uuid NOT NULL,
    comment character varying(200),
    CONSTRAINT forum_post_revisions_check CHECK (((body IS NULL) = (_html_body IS NULL))),
    CONSTRAINT forum_post_revisions_check1 CHECK (((mod_body IS NULL) = (_html_mod_body IS NULL)))
);


ALTER TABLE public.forum_post_revisions OWNER TO "etwin.dev.admin";

--
-- Name: forum_posts; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.forum_posts (
    forum_post_id uuid NOT NULL,
    ctime public.instant,
    forum_thread_id uuid NOT NULL
);


ALTER TABLE public.forum_posts OWNER TO "etwin.dev.admin";

--
-- Name: forum_role_grants; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.forum_role_grants (
    forum_section_id uuid NOT NULL,
    user_id uuid NOT NULL,
    start_time public.instant,
    granted_by uuid NOT NULL
);


ALTER TABLE public.forum_role_grants OWNER TO "etwin.dev.admin";

--
-- Name: forum_role_revocations; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.forum_role_revocations (
    forum_section_id uuid NOT NULL,
    user_id uuid NOT NULL,
    start_time public.instant NOT NULL,
    end_time public.instant,
    granted_by uuid NOT NULL,
    revoked_by uuid NOT NULL,
    CONSTRAINT forum_role_revocations_check CHECK (((start_time)::timestamp with time zone < (end_time)::timestamp with time zone))
);


ALTER TABLE public.forum_role_revocations OWNER TO "etwin.dev.admin";

--
-- Name: forum_sections; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.forum_sections (
    forum_section_id uuid NOT NULL,
    key character varying(32),
    ctime public.instant,
    display_name character varying(64) NOT NULL,
    display_name_mtime public.instant NOT NULL,
    locale character varying(10),
    locale_mtime public.instant NOT NULL,
    CONSTRAINT forum_sections_check CHECK (((display_name_mtime)::timestamp with time zone >= (ctime)::timestamp with time zone))
);


ALTER TABLE public.forum_sections OWNER TO "etwin.dev.admin";

--
-- Name: forum_threads; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.forum_threads (
    forum_thread_id uuid NOT NULL,
    key character varying(32),
    ctime public.instant,
    title character varying(64) NOT NULL,
    title_mtime public.instant NOT NULL,
    forum_section_id uuid NOT NULL,
    is_pinned boolean NOT NULL,
    is_pinned_mtime public.instant NOT NULL,
    is_locked boolean NOT NULL,
    is_locked_mtime public.instant NOT NULL,
    CONSTRAINT forum_threads_check CHECK (((title_mtime)::timestamp with time zone >= (ctime)::timestamp with time zone)),
    CONSTRAINT forum_threads_check1 CHECK (((is_pinned_mtime)::timestamp with time zone >= (ctime)::timestamp with time zone)),
    CONSTRAINT forum_threads_check2 CHECK (((is_locked_mtime)::timestamp with time zone >= (ctime)::timestamp with time zone))
);


ALTER TABLE public.forum_threads OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_best_season_ranks; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_best_season_ranks (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_user_id public.hammerfest_user_id NOT NULL,
    best_season_rank public.u32
);


ALTER TABLE public.hammerfest_best_season_ranks OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_emails; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_emails (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_user_id public.hammerfest_user_id NOT NULL,
    email public.email_address_hash
);


ALTER TABLE public.hammerfest_emails OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_post_ids; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_forum_post_ids (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_thread_id public.hammerfest_forum_thread_id NOT NULL,
    page public.u16 NOT NULL,
    offset_in_list public.u8 NOT NULL,
    hammerfest_post_id public.hammerfest_forum_post_id NOT NULL,
    CONSTRAINT hammerfest_forum_post_ids_page_check CHECK (((page)::integer > 0))
);


ALTER TABLE public.hammerfest_forum_post_ids OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_posts; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_forum_posts (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_thread_id public.hammerfest_forum_thread_id NOT NULL,
    page public.u16 NOT NULL,
    offset_in_list public.u8 NOT NULL,
    author public.hammerfest_user_id NOT NULL,
    posted_at public.hammerfest_date_time NOT NULL,
    remote_html_body text NOT NULL,
    _mkt_body text,
    _html_body text,
    CONSTRAINT hammerfest_forum_posts_page_check CHECK (((page)::integer > 0))
);


ALTER TABLE public.hammerfest_forum_posts OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_roles; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_forum_roles (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_user_id public.hammerfest_user_id NOT NULL,
    role public.hammerfest_forum_role NOT NULL
);


ALTER TABLE public.hammerfest_forum_roles OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_theme_counts; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_forum_theme_counts (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_theme_id public.hammerfest_forum_thread_id NOT NULL,
    page_count public.u16 NOT NULL,
    CONSTRAINT hammerfest_forum_theme_counts_page_count_check CHECK (((page_count)::integer > 0))
);


ALTER TABLE public.hammerfest_forum_theme_counts OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_theme_page_counts; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_forum_theme_page_counts (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_theme_id public.hammerfest_forum_thread_id NOT NULL,
    page public.u16 NOT NULL,
    thread_count public.u8 NOT NULL
);


ALTER TABLE public.hammerfest_forum_theme_page_counts OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_theme_threads; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_forum_theme_threads (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_theme_id public.hammerfest_forum_thread_id NOT NULL,
    page public.u16 NOT NULL,
    offset_in_list public.u8 NOT NULL,
    hammerfest_thread_id public.hammerfest_forum_thread_id NOT NULL
);


ALTER TABLE public.hammerfest_forum_theme_threads OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_themes; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_forum_themes (
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_theme_id public.hammerfest_forum_theme_id NOT NULL,
    archived_at public.instant NOT NULL,
    title public.hammerfest_forum_theme_title NOT NULL,
    description public.hammerfest_forum_theme_description,
    is_public boolean NOT NULL
);


ALTER TABLE public.hammerfest_forum_themes OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_thread_page_counts; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_forum_thread_page_counts (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_thread_id public.hammerfest_forum_thread_id NOT NULL,
    page public.u16 NOT NULL,
    post_count public.u8 NOT NULL,
    CONSTRAINT hammerfest_forum_thread_page_counts_page_check CHECK (((page)::integer > 0))
);


ALTER TABLE public.hammerfest_forum_thread_page_counts OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_thread_shared_meta; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_forum_thread_shared_meta (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_thread_id public.hammerfest_forum_thread_id NOT NULL,
    hammerfest_theme_id public.hammerfest_forum_theme_id NOT NULL,
    title public.hammerfest_forum_thread_title NOT NULL,
    is_closed boolean NOT NULL,
    page_count public.u32 NOT NULL,
    CONSTRAINT hammerfest_forum_thread_shared_meta_page_count_check CHECK (((page_count)::bigint > 0))
);


ALTER TABLE public.hammerfest_forum_thread_shared_meta OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_thread_theme_meta; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_forum_thread_theme_meta (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_thread_id public.hammerfest_forum_thread_id NOT NULL,
    is_sticky boolean NOT NULL,
    latest_post_at public.hammerfest_date,
    author public.hammerfest_user_id NOT NULL,
    reply_count public.u16 NOT NULL,
    CONSTRAINT hammerfest_forum_thread_theme_meta_check CHECK (((is_sticky AND (latest_post_at IS NULL)) OR ((NOT is_sticky) AND (latest_post_at IS NOT NULL))))
);


ALTER TABLE public.hammerfest_forum_thread_theme_meta OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_forum_threads; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_forum_threads (
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_thread_id public.hammerfest_forum_thread_id NOT NULL,
    archived_at public.instant NOT NULL
);


ALTER TABLE public.hammerfest_forum_threads OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_godchild_lists; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_godchild_lists (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_user_id public.hammerfest_user_id NOT NULL,
    godchild_count public.u32 NOT NULL
);


ALTER TABLE public.hammerfest_godchild_lists OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_godchildren; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_godchildren (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_user_id public.hammerfest_user_id NOT NULL,
    offset_in_list public.u32 NOT NULL,
    godchild_id public.hammerfest_user_id NOT NULL,
    tokens public.u32 NOT NULL,
    CONSTRAINT hammerfest_godchildren_check CHECK (((godchild_id)::text <> (hammerfest_user_id)::text))
);


ALTER TABLE public.hammerfest_godchildren OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_inventories; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_inventories (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_user_id public.hammerfest_user_id NOT NULL,
    item_counts public.hammerfest_item_count_map_id NOT NULL
);


ALTER TABLE public.hammerfest_inventories OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_item_count_map_items; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_item_count_map_items (
    hammerfest_item_count_map_id public.hammerfest_item_count_map_id NOT NULL,
    hammerfest_item_id public.hammerfest_item_id NOT NULL,
    count public.u32 NOT NULL
);


ALTER TABLE public.hammerfest_item_count_map_items OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_item_count_maps; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_item_count_maps (
    hammerfest_item_count_map_id public.hammerfest_item_count_map_id NOT NULL,
    _sha3_256 bytea NOT NULL
);


ALTER TABLE public.hammerfest_item_count_maps OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_items; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_items (
    hammerfest_item_id public.hammerfest_item_id NOT NULL,
    is_hidden boolean NOT NULL
);


ALTER TABLE public.hammerfest_items OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_profiles; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_profiles (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_user_id public.hammerfest_user_id NOT NULL,
    best_score public.u32 NOT NULL,
    best_level public.u8 NOT NULL,
    season_score public.u32,
    quest_statuses public.hammerfest_quest_status_map_id NOT NULL,
    unlocked_items public.hammerfest_unlocked_item_set_id NOT NULL,
    CONSTRAINT hammerfest_profiles_best_level_check CHECK (((best_level)::smallint < 120))
);


ALTER TABLE public.hammerfest_profiles OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_quest_status_map_items; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_quest_status_map_items (
    hammerfest_quest_status_map_id public.hammerfest_quest_status_map_id NOT NULL,
    hammerfest_quest_id public.hammerfest_quest_id NOT NULL,
    status public.hammerfest_quest_status NOT NULL
);


ALTER TABLE public.hammerfest_quest_status_map_items OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_quest_status_maps; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_quest_status_maps (
    hammerfest_quest_status_map_id public.hammerfest_quest_status_map_id NOT NULL,
    _sha3_256 bytea NOT NULL
);


ALTER TABLE public.hammerfest_quest_status_maps OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_quests; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_quests (
    hammerfest_quest_id public.hammerfest_quest_id NOT NULL
);


ALTER TABLE public.hammerfest_quests OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_servers; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_servers (
    hammerfest_server public.hammerfest_server NOT NULL,
    CONSTRAINT hammerfest_servers_domain_check CHECK (((hammerfest_server)::text = ANY (ARRAY[('hammerfest.fr'::character varying)::text, ('hfest.net'::character varying)::text, ('hammerfest.es'::character varying)::text])))
);


ALTER TABLE public.hammerfest_servers OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_sessions; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_sessions (
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_session_key bytea NOT NULL,
    _hammerfest_session_key_hash bytea NOT NULL,
    hammerfest_user_id public.hammerfest_user_id NOT NULL,
    ctime public.instant NOT NULL,
    atime public.instant NOT NULL,
    CONSTRAINT hammerfest_sessions_check CHECK (((atime)::timestamp with time zone >= (ctime)::timestamp with time zone))
);


ALTER TABLE public.hammerfest_sessions OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_shops; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_shops (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_user_id public.hammerfest_user_id NOT NULL,
    weekly_tokens public.u8 NOT NULL,
    purchased_tokens public.u8,
    has_quest_bonus boolean NOT NULL
);


ALTER TABLE public.hammerfest_shops OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_tokens; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_tokens (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_user_id public.hammerfest_user_id NOT NULL,
    tokens public.u32 NOT NULL
);


ALTER TABLE public.hammerfest_tokens OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_unlocked_item_set_items; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_unlocked_item_set_items (
    hammerfest_unlocked_item_set_id public.hammerfest_unlocked_item_set_id NOT NULL,
    hammerfest_item_id public.hammerfest_item_id NOT NULL
);


ALTER TABLE public.hammerfest_unlocked_item_set_items OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_unlocked_item_sets; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_unlocked_item_sets (
    hammerfest_unlocked_item_set_id public.hammerfest_unlocked_item_set_id NOT NULL,
    _sha3_256 bytea NOT NULL
);


ALTER TABLE public.hammerfest_unlocked_item_sets OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_user_achievements; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_user_achievements (
    period public.period_lower NOT NULL,
    retrieved_at public.instant_set NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_user_id public.hammerfest_user_id NOT NULL,
    has_carrot boolean NOT NULL,
    ladder_level public.hammerfest_ladder_level NOT NULL
);


ALTER TABLE public.hammerfest_user_achievements OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_user_links; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_user_links (
    user_id public.user_id NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_user_id public.hammerfest_user_id NOT NULL,
    linked_by public.user_id NOT NULL,
    period public.period_lower NOT NULL,
    unlinked_by public.user_id,
    CONSTRAINT hammerfest_user_links_check CHECK (((upper_inf((period)::public.period) AND (unlinked_by IS NULL)) OR ((NOT upper_inf((period)::public.period)) AND (unlinked_by IS NOT NULL))))
);


ALTER TABLE public.hammerfest_user_links OWNER TO "etwin.dev.admin";

--
-- Name: hammerfest_users; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.hammerfest_users (
    hammerfest_server public.hammerfest_server NOT NULL,
    username character varying(20) NOT NULL,
    hammerfest_user_id public.hammerfest_user_id NOT NULL,
    archived_at public.instant NOT NULL
);


ALTER TABLE public.hammerfest_users OWNER TO "etwin.dev.admin";

--
-- Name: oauth_access_tokens; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.oauth_access_tokens (
    oauth_access_token_id uuid NOT NULL,
    oauth_client_id uuid NOT NULL,
    user_id uuid NOT NULL,
    ctime public.instant NOT NULL,
    atime public.instant NOT NULL,
    CONSTRAINT oauth_access_tokens_check CHECK (((atime)::timestamp with time zone >= (ctime)::timestamp with time zone))
);


ALTER TABLE public.oauth_access_tokens OWNER TO "etwin.dev.admin";

--
-- Name: oauth_clients; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.oauth_clients (
    oauth_client_id uuid NOT NULL,
    key character varying(32),
    ctime public.instant,
    display_name character varying(64) NOT NULL,
    display_name_mtime public.instant NOT NULL,
    app_uri character varying(512) NOT NULL,
    app_uri_mtime public.instant NOT NULL,
    callback_uri character varying(512) NOT NULL,
    callback_uri_mtime public.instant NOT NULL,
    secret bytea NOT NULL,
    secret_mtime public.instant NOT NULL,
    owner_id uuid,
    CONSTRAINT oauth_clients_check CHECK (((display_name_mtime)::timestamp with time zone >= (ctime)::timestamp with time zone)),
    CONSTRAINT oauth_clients_check1 CHECK (((app_uri_mtime)::timestamp with time zone >= (ctime)::timestamp with time zone)),
    CONSTRAINT oauth_clients_check2 CHECK (((callback_uri_mtime)::timestamp with time zone >= (ctime)::timestamp with time zone)),
    CONSTRAINT oauth_clients_check3 CHECK (((secret_mtime)::timestamp with time zone >= (ctime)::timestamp with time zone)),
    CONSTRAINT oauth_clients_check4 CHECK (((key IS NULL) <> (owner_id IS NULL)))
);


ALTER TABLE public.oauth_clients OWNER TO "etwin.dev.admin";

--
-- Name: old_dinoparc_sessions; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.old_dinoparc_sessions (
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_session_key bytea NOT NULL,
    _dinoparc_session_key_hash bytea NOT NULL,
    dinoparc_user_id public.dinoparc_user_id NOT NULL,
    ctime public.instant NOT NULL,
    atime public.instant NOT NULL,
    dtime public.instant NOT NULL,
    CONSTRAINT old_dinoparc_sessions_check CHECK (((atime)::timestamp with time zone >= (ctime)::timestamp with time zone)),
    CONSTRAINT old_dinoparc_sessions_check1 CHECK (((dtime)::timestamp with time zone >= (atime)::timestamp with time zone)),
    CONSTRAINT old_dinoparc_sessions_check2 CHECK (((dtime)::timestamp with time zone > (ctime)::timestamp with time zone))
);


ALTER TABLE public.old_dinoparc_sessions OWNER TO "etwin.dev.admin";

--
-- Name: old_hammerfest_sessions; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.old_hammerfest_sessions (
    hammerfest_server public.hammerfest_server NOT NULL,
    hammerfest_session_key bytea NOT NULL,
    _hammerfest_session_key_hash bytea NOT NULL,
    hammerfest_user_id public.hammerfest_user_id NOT NULL,
    ctime public.instant NOT NULL,
    atime public.instant NOT NULL,
    dtime public.instant NOT NULL,
    CONSTRAINT old_hammerfest_sessions_check CHECK (((atime)::timestamp with time zone >= (ctime)::timestamp with time zone)),
    CONSTRAINT old_hammerfest_sessions_check1 CHECK (((dtime)::timestamp with time zone >= (atime)::timestamp with time zone)),
    CONSTRAINT old_hammerfest_sessions_check2 CHECK (((dtime)::timestamp with time zone > (ctime)::timestamp with time zone))
);


ALTER TABLE public.old_hammerfest_sessions OWNER TO "etwin.dev.admin";

--
-- Name: old_oauth_client_app_uris; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.old_oauth_client_app_uris (
    oauth_client_id uuid NOT NULL,
    start_time public.instant NOT NULL,
    app_uri character varying(512) NOT NULL
);


ALTER TABLE public.old_oauth_client_app_uris OWNER TO "etwin.dev.admin";

--
-- Name: old_oauth_client_callback_uris; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.old_oauth_client_callback_uris (
    oauth_client_id uuid NOT NULL,
    start_time public.instant NOT NULL,
    callback_uri character varying(512) NOT NULL
);


ALTER TABLE public.old_oauth_client_callback_uris OWNER TO "etwin.dev.admin";

--
-- Name: old_oauth_client_display_names; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.old_oauth_client_display_names (
    oauth_client_id uuid NOT NULL,
    start_time public.instant NOT NULL,
    display_name character varying(64) NOT NULL
);


ALTER TABLE public.old_oauth_client_display_names OWNER TO "etwin.dev.admin";

--
-- Name: old_oauth_client_secrets; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.old_oauth_client_secrets (
    oauth_client_id uuid NOT NULL,
    start_time public.instant NOT NULL,
    secret bytea NOT NULL
);


ALTER TABLE public.old_oauth_client_secrets OWNER TO "etwin.dev.admin";

--
-- Name: old_twinoid_access_tokens; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.old_twinoid_access_tokens (
    twinoid_access_token bytea NOT NULL,
    _twinoid_access_token_hash bytea NOT NULL,
    twinoid_user_id public.twinoid_user_id NOT NULL,
    ctime public.instant NOT NULL,
    atime public.instant NOT NULL,
    dtime public.instant NOT NULL,
    expiration_time public.instant NOT NULL,
    CONSTRAINT old_twinoid_access_tokens_check CHECK (((ctime)::timestamp with time zone <= (atime)::timestamp with time zone)),
    CONSTRAINT old_twinoid_access_tokens_check1 CHECK (((atime)::timestamp with time zone <= (expiration_time)::timestamp with time zone)),
    CONSTRAINT old_twinoid_access_tokens_check2 CHECK (((ctime)::timestamp with time zone < (expiration_time)::timestamp with time zone)),
    CONSTRAINT old_twinoid_access_tokens_check3 CHECK (((atime)::timestamp with time zone <= (dtime)::timestamp with time zone)),
    CONSTRAINT old_twinoid_access_tokens_check4 CHECK (((ctime)::timestamp with time zone < (dtime)::timestamp with time zone))
);


ALTER TABLE public.old_twinoid_access_tokens OWNER TO "etwin.dev.admin";

--
-- Name: old_twinoid_refresh_tokens; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.old_twinoid_refresh_tokens (
    twinoid_refresh_token bytea NOT NULL,
    _twinoid_refresh_token_hash bytea NOT NULL,
    twinoid_user_id public.twinoid_user_id NOT NULL,
    ctime public.instant NOT NULL,
    atime public.instant NOT NULL,
    dtime public.instant NOT NULL,
    CONSTRAINT old_twinoid_refresh_tokens_check CHECK (((ctime)::timestamp with time zone <= (atime)::timestamp with time zone)),
    CONSTRAINT old_twinoid_refresh_tokens_check1 CHECK (((atime)::timestamp with time zone <= (dtime)::timestamp with time zone)),
    CONSTRAINT old_twinoid_refresh_tokens_check2 CHECK (((ctime)::timestamp with time zone < (dtime)::timestamp with time zone))
);


ALTER TABLE public.old_twinoid_refresh_tokens OWNER TO "etwin.dev.admin";

--
-- Name: sessions; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.sessions (
    session_id uuid NOT NULL,
    user_id uuid,
    ctime public.instant NOT NULL,
    atime public.instant NOT NULL,
    data json NOT NULL,
    CONSTRAINT sessions_check CHECK (((atime)::timestamp with time zone >= (ctime)::timestamp with time zone))
);


ALTER TABLE public.sessions OWNER TO "etwin.dev.admin";

--
-- Name: twinoid_access_tokens; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.twinoid_access_tokens (
    twinoid_access_token bytea NOT NULL,
    _twinoid_access_token_hash bytea NOT NULL,
    twinoid_user_id public.twinoid_user_id NOT NULL,
    ctime public.instant NOT NULL,
    atime public.instant NOT NULL,
    expiration_time public.instant NOT NULL,
    CONSTRAINT twinoid_access_tokens_check CHECK (((ctime)::timestamp with time zone <= (atime)::timestamp with time zone)),
    CONSTRAINT twinoid_access_tokens_check1 CHECK (((atime)::timestamp with time zone <= (expiration_time)::timestamp with time zone)),
    CONSTRAINT twinoid_access_tokens_check2 CHECK (((ctime)::timestamp with time zone < (expiration_time)::timestamp with time zone))
);


ALTER TABLE public.twinoid_access_tokens OWNER TO "etwin.dev.admin";

--
-- Name: twinoid_refresh_tokens; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.twinoid_refresh_tokens (
    twinoid_refresh_token bytea NOT NULL,
    _twinoid_refresh_token_hash bytea NOT NULL,
    twinoid_user_id public.twinoid_user_id NOT NULL,
    ctime public.instant NOT NULL,
    atime public.instant NOT NULL,
    CONSTRAINT twinoid_refresh_tokens_check CHECK (((ctime)::timestamp with time zone <= (atime)::timestamp with time zone))
);


ALTER TABLE public.twinoid_refresh_tokens OWNER TO "etwin.dev.admin";

--
-- Name: twinoid_user_links; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.twinoid_user_links (
    user_id public.user_id NOT NULL,
    twinoid_user_id public.twinoid_user_id NOT NULL,
    linked_by public.user_id NOT NULL,
    period public.period_lower NOT NULL,
    unlinked_by public.user_id,
    CONSTRAINT twinoid_user_links_check CHECK (((upper_inf((period)::public.period) AND (unlinked_by IS NULL)) OR ((NOT upper_inf((period)::public.period)) AND (unlinked_by IS NOT NULL))))
);


ALTER TABLE public.twinoid_user_links OWNER TO "etwin.dev.admin";

--
-- Name: twinoid_users; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.twinoid_users (
    twinoid_user_id public.twinoid_user_id NOT NULL,
    name character varying(50) NOT NULL,
    archived_at public.instant NOT NULL
);


ALTER TABLE public.twinoid_users OWNER TO "etwin.dev.admin";

--
-- Name: users; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.users (
    user_id public.user_id NOT NULL,
    created_at public.instant,
    is_administrator boolean NOT NULL,
    _is_current boolean DEFAULT true NOT NULL,
    CONSTRAINT users__is_current_check CHECK (_is_current)
);


ALTER TABLE public.users OWNER TO "etwin.dev.admin";

--
-- Name: users_history; Type: TABLE; Schema: public; Owner: etwin.dev.admin
--

CREATE TABLE public.users_history (
    user_id public.user_id NOT NULL,
    period public.period_lower NOT NULL,
    _is_current boolean,
    updated_by public.user_id NOT NULL,
    display_name public.user_display_name NOT NULL,
    username public.username,
    email public.email_address_hash,
    password public.password_hash,
    CONSTRAINT users_history_check CHECK ((((NOT upper_inf((period)::public.period)) AND (_is_current IS NULL)) OR (upper_inf((period)::public.period) AND (_is_current IS NOT NULL) AND _is_current)))
);


ALTER TABLE public.users_history OWNER TO "etwin.dev.admin";

--
-- Name: users_current; Type: VIEW; Schema: public; Owner: etwin.dev.admin
--

CREATE VIEW public.users_current AS
 SELECT users.user_id,
    users.created_at,
    lower((users_history.period)::public.period) AS updated_at,
    users_history.updated_by,
    users.is_administrator,
    users_history.display_name,
    users_history.username,
    email_addresses.email_address AS email,
    users_history.email AS _email_hash,
    users_history.password
   FROM ((public.users
     JOIN public.users_history USING (user_id, _is_current))
     LEFT JOIN public.email_addresses ON (((users_history.email)::bytea = (email_addresses._hash)::bytea)));


ALTER TABLE public.users_current OWNER TO "etwin.dev.admin";

--
-- Data for Name: _post_formatting_costs; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public._post_formatting_costs (forum_post_revision_id, formatting, cost) FROM stdin;
\.


--
-- Data for Name: announcements; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.announcements (announcement_id, forum_thread_id, locale, created_at, created_by) FROM stdin;
\.


--
-- Data for Name: dinoparc_bills; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_bills (period, retrieved_at, dinoparc_server, dinoparc_user_id, bills) FROM stdin;
\.


--
-- Data for Name: dinoparc_coins; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_coins (period, retrieved_at, dinoparc_server, dinoparc_user_id, coins) FROM stdin;
\.


--
-- Data for Name: dinoparc_collections; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_collections (period, retrieved_at, dinoparc_server, dinoparc_user_id, dinoparc_reward_set_id, dinoparc_epic_reward_set_id) FROM stdin;
\.


--
-- Data for Name: dinoparc_dinoz; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_dinoz (dinoparc_server, dinoparc_dinoz_id, archived_at) FROM stdin;
\.


--
-- Data for Name: dinoparc_dinoz_levels; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_dinoz_levels (period, retrieved_at, dinoparc_server, dinoparc_dinoz_id, level) FROM stdin;
\.


--
-- Data for Name: dinoparc_dinoz_locations; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_dinoz_locations (period, retrieved_at, dinoparc_server, dinoparc_dinoz_id, location) FROM stdin;
\.


--
-- Data for Name: dinoparc_dinoz_names; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_dinoz_names (period, retrieved_at, dinoparc_server, dinoparc_dinoz_id, name) FROM stdin;
\.


--
-- Data for Name: dinoparc_dinoz_owners; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_dinoz_owners (period, retrieved_at, dinoparc_server, dinoparc_dinoz_id, owner) FROM stdin;
\.


--
-- Data for Name: dinoparc_dinoz_profiles; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_dinoz_profiles (period, retrieved_at, dinoparc_server, dinoparc_dinoz_id, life, experience, danger, in_tournament, elements, skills) FROM stdin;
\.


--
-- Data for Name: dinoparc_dinoz_skins; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_dinoz_skins (period, retrieved_at, dinoparc_server, dinoparc_dinoz_id, race, skin) FROM stdin;
\.


--
-- Data for Name: dinoparc_epic_reward_set_items; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_epic_reward_set_items (dinoparc_epic_reward_set_id, dinoparc_epic_reward_key) FROM stdin;
\.


--
-- Data for Name: dinoparc_epic_reward_sets; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_epic_reward_sets (dinoparc_epic_reward_set_id, _sha3_256) FROM stdin;
\.


--
-- Data for Name: dinoparc_inventories; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_inventories (period, retrieved_at, dinoparc_server, dinoparc_user_id, item_counts) FROM stdin;
\.


--
-- Data for Name: dinoparc_item_count_map_items; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_item_count_map_items (dinoparc_item_count_map_id, dinoparc_item_id, count) FROM stdin;
\.


--
-- Data for Name: dinoparc_item_count_maps; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_item_count_maps (dinoparc_item_count_map_id, _sha3_256) FROM stdin;
\.


--
-- Data for Name: dinoparc_locations; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_locations (dinoparc_location_id) FROM stdin;
0
1
2
3
4
5
6
7
8
9
10
11
12
13
14
15
16
17
18
19
20
21
22
\.


--
-- Data for Name: dinoparc_reward_set_items; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_reward_set_items (dinoparc_reward_set_id, dinoparc_reward_id) FROM stdin;
\.


--
-- Data for Name: dinoparc_reward_sets; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_reward_sets (dinoparc_reward_set_id, _sha3_256) FROM stdin;
\.


--
-- Data for Name: dinoparc_servers; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_servers (dinoparc_server) FROM stdin;
dinoparc.com
en.dinoparc.com
sp.dinoparc.com
\.


--
-- Data for Name: dinoparc_sessions; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_sessions (dinoparc_server, dinoparc_session_key, _dinoparc_session_key_hash, dinoparc_user_id, ctime, atime) FROM stdin;
\.


--
-- Data for Name: dinoparc_skill_level_map_items; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_skill_level_map_items (dinoparc_skill_level_map_id, dinoparc_skill, level) FROM stdin;
\.


--
-- Data for Name: dinoparc_skill_level_maps; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_skill_level_maps (dinoparc_skill_level_map_id, _sha3_256) FROM stdin;
\.


--
-- Data for Name: dinoparc_user_dinoz; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_user_dinoz (period, retrieved_at, dinoparc_server, dinoparc_user_id, offset_in_list, dinoparc_dinoz_id) FROM stdin;
\.


--
-- Data for Name: dinoparc_user_dinoz_counts; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_user_dinoz_counts (period, retrieved_at, dinoparc_server, dinoparc_user_id, dinoz_count) FROM stdin;
\.


--
-- Data for Name: dinoparc_user_links; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_user_links (user_id, dinoparc_server, dinoparc_user_id, linked_by, period, unlinked_by) FROM stdin;
\.


--
-- Data for Name: dinoparc_users; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.dinoparc_users (dinoparc_server, dinoparc_user_id, username, archived_at) FROM stdin;
\.


--
-- Data for Name: email_addresses; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.email_addresses (email_address, _hash, created_at) FROM stdin;
\.


--
-- Data for Name: email_verifications; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.email_verifications (user_id, email_address, ctime, validation_time) FROM stdin;
\.


--
-- Data for Name: forum_post_revisions; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.forum_post_revisions (forum_post_revision_id, "time", body, _html_body, mod_body, _html_mod_body, forum_post_id, author_id, comment) FROM stdin;
\.


--
-- Data for Name: forum_posts; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.forum_posts (forum_post_id, ctime, forum_thread_id) FROM stdin;
\.


--
-- Data for Name: forum_role_grants; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.forum_role_grants (forum_section_id, user_id, start_time, granted_by) FROM stdin;
\.


--
-- Data for Name: forum_role_revocations; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.forum_role_revocations (forum_section_id, user_id, start_time, end_time, granted_by, revoked_by) FROM stdin;
\.


--
-- Data for Name: forum_sections; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.forum_sections (forum_section_id, key, ctime, display_name, display_name_mtime, locale, locale_mtime) FROM stdin;
e7462c82-4d69-4918-aea0-3383ad61ee43	en_main	2021-09-28 11:48:41.967+00	Main Forum (en-US)	2021-09-28 11:48:41.967+00	en-US	2021-09-28 11:48:41.967+00
8ac08087-e491-4fe9-b86b-3ab47a651c8a	es_main	2021-09-28 11:48:41.97+00	Foro principal (es-SP)	2021-09-28 11:48:41.97+00	es-SP	2021-09-28 11:48:41.97+00
8eacf590-78a5-47a2-91d0-4c66b423b22d	eo_main	2021-09-28 11:48:41.971+00	Ĉefa forumo (eo)	2021-09-28 11:48:41.971+00	eo	2021-09-28 11:48:41.971+00
c49b1631-7677-4dd9-8c96-d7fd30c88213	fr_main	2021-09-28 11:48:41.973+00	Forum Général (fr-FR)	2021-09-28 11:48:41.973+00	fr-FR	2021-09-28 11:48:41.973+00
\.


--
-- Data for Name: forum_threads; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.forum_threads (forum_thread_id, key, ctime, title, title_mtime, forum_section_id, is_pinned, is_pinned_mtime, is_locked, is_locked_mtime) FROM stdin;
\.


--
-- Data for Name: hammerfest_best_season_ranks; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_best_season_ranks (period, retrieved_at, hammerfest_server, hammerfest_user_id, best_season_rank) FROM stdin;
\.


--
-- Data for Name: hammerfest_emails; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_emails (period, retrieved_at, hammerfest_server, hammerfest_user_id, email) FROM stdin;
\.


--
-- Data for Name: hammerfest_forum_post_ids; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_forum_post_ids (period, retrieved_at, hammerfest_server, hammerfest_thread_id, page, offset_in_list, hammerfest_post_id) FROM stdin;
\.


--
-- Data for Name: hammerfest_forum_posts; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_forum_posts (period, retrieved_at, hammerfest_server, hammerfest_thread_id, page, offset_in_list, author, posted_at, remote_html_body, _mkt_body, _html_body) FROM stdin;
\.


--
-- Data for Name: hammerfest_forum_roles; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_forum_roles (period, retrieved_at, hammerfest_server, hammerfest_user_id, role) FROM stdin;
\.


--
-- Data for Name: hammerfest_forum_theme_counts; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_forum_theme_counts (period, retrieved_at, hammerfest_server, hammerfest_theme_id, page_count) FROM stdin;
\.


--
-- Data for Name: hammerfest_forum_theme_page_counts; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_forum_theme_page_counts (period, retrieved_at, hammerfest_server, hammerfest_theme_id, page, thread_count) FROM stdin;
\.


--
-- Data for Name: hammerfest_forum_theme_threads; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_forum_theme_threads (period, retrieved_at, hammerfest_server, hammerfest_theme_id, page, offset_in_list, hammerfest_thread_id) FROM stdin;
\.


--
-- Data for Name: hammerfest_forum_themes; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_forum_themes (hammerfest_server, hammerfest_theme_id, archived_at, title, description, is_public) FROM stdin;
\.


--
-- Data for Name: hammerfest_forum_thread_page_counts; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_forum_thread_page_counts (period, retrieved_at, hammerfest_server, hammerfest_thread_id, page, post_count) FROM stdin;
\.


--
-- Data for Name: hammerfest_forum_thread_shared_meta; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_forum_thread_shared_meta (period, retrieved_at, hammerfest_server, hammerfest_thread_id, hammerfest_theme_id, title, is_closed, page_count) FROM stdin;
\.


--
-- Data for Name: hammerfest_forum_thread_theme_meta; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_forum_thread_theme_meta (period, retrieved_at, hammerfest_server, hammerfest_thread_id, is_sticky, latest_post_at, author, reply_count) FROM stdin;
\.


--
-- Data for Name: hammerfest_forum_threads; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_forum_threads (hammerfest_server, hammerfest_thread_id, archived_at) FROM stdin;
\.


--
-- Data for Name: hammerfest_godchild_lists; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_godchild_lists (period, retrieved_at, hammerfest_server, hammerfest_user_id, godchild_count) FROM stdin;
\.


--
-- Data for Name: hammerfest_godchildren; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_godchildren (period, retrieved_at, hammerfest_server, hammerfest_user_id, offset_in_list, godchild_id, tokens) FROM stdin;
\.


--
-- Data for Name: hammerfest_inventories; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_inventories (period, retrieved_at, hammerfest_server, hammerfest_user_id, item_counts) FROM stdin;
\.


--
-- Data for Name: hammerfest_item_count_map_items; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_item_count_map_items (hammerfest_item_count_map_id, hammerfest_item_id, count) FROM stdin;
\.


--
-- Data for Name: hammerfest_item_count_maps; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_item_count_maps (hammerfest_item_count_map_id, _sha3_256) FROM stdin;
\.


--
-- Data for Name: hammerfest_items; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_items (hammerfest_item_id, is_hidden) FROM stdin;
65	f
66	f
67	f
68	f
69	f
70	f
71	f
72	f
73	f
74	f
75	f
76	f
77	f
78	f
79	f
80	f
81	f
82	f
83	f
84	f
85	f
86	f
87	f
88	f
89	f
90	f
91	f
92	f
93	f
94	f
95	f
96	f
0	f
1	f
2	f
3	f
4	f
5	f
6	f
7	f
8	f
9	f
10	f
11	f
12	f
13	f
14	f
15	f
16	f
17	f
18	f
19	f
20	f
21	f
22	f
23	f
24	f
25	f
26	f
27	f
28	f
29	f
30	f
31	f
32	f
33	f
34	f
35	f
36	f
37	f
38	f
39	f
40	f
41	f
42	f
43	f
44	f
45	f
46	f
47	f
48	f
49	f
50	f
51	f
52	f
53	f
54	f
55	f
56	f
57	f
58	f
59	f
60	f
61	f
62	f
63	f
64	f
97	f
98	f
99	f
100	f
101	f
102	f
103	f
104	f
105	f
106	f
107	f
108	f
109	f
110	f
111	f
112	f
113	f
114	f
115	f
116	f
117	f
1000	f
1001	f
1002	f
1003	f
1004	f
1005	f
1006	f
1007	f
1008	f
1009	f
1010	f
1011	f
1012	f
1013	f
1014	f
1015	f
1016	f
1017	f
1018	f
1019	f
1020	f
1021	f
1022	f
1023	f
1024	f
1025	f
1026	f
1027	f
1028	f
1029	f
1030	f
1031	f
1032	f
1033	f
1034	f
1035	f
1036	f
1037	f
1038	f
1039	f
1040	f
1041	f
1042	f
1043	f
1044	f
1045	f
1046	f
1047	f
1048	f
1049	f
1050	f
1051	f
1052	f
1053	f
1054	f
1055	f
1056	f
1057	f
1058	f
1059	f
1060	f
1061	f
1062	f
1063	f
1064	f
1065	f
1066	f
1067	f
1068	f
1069	f
1070	f
1071	f
1072	f
1073	f
1074	f
1075	f
1076	f
1077	f
1078	f
1079	f
1080	f
1081	f
1082	f
1083	f
1084	f
1085	f
1086	f
1087	f
1088	f
1089	f
1090	f
1091	f
1092	f
1093	f
1094	f
1095	f
1096	f
1097	f
1098	f
1099	f
1100	f
1101	f
1102	f
1103	f
1104	f
1105	f
1106	f
1107	f
1108	f
1109	f
1110	f
1111	f
1112	f
1113	f
1114	f
1115	f
1116	f
1117	f
1118	f
1119	f
1120	f
1121	f
1122	f
1123	f
1124	f
1125	f
1126	f
1127	f
1128	f
1129	f
1130	f
1131	f
1132	f
1133	f
1134	f
1135	f
1136	f
1137	f
1138	f
1139	f
1140	f
1141	f
1142	f
1143	f
1144	f
1145	f
1146	f
1147	f
1148	f
1149	f
1150	f
1151	f
1152	f
1153	f
1154	f
1155	f
1156	f
1157	f
1158	f
1159	f
1160	f
1161	f
1162	f
1163	f
1164	f
1165	f
1166	f
1167	f
1168	f
1169	f
1170	f
1171	f
1172	f
1173	f
1174	f
1175	f
1176	f
1177	f
1178	f
1179	f
1180	f
1181	f
1182	f
1183	f
1184	f
1185	f
1186	t
1187	t
1188	t
1189	t
1190	f
1191	f
1192	f
1193	f
1194	f
1195	f
1196	f
1197	f
1198	f
1199	f
1200	f
1201	f
1202	f
1203	f
1204	f
1205	f
1206	f
1207	f
1208	f
1209	f
1210	f
1211	f
1212	f
1213	f
1214	f
1215	f
1216	f
1217	f
1218	f
1219	f
1220	f
1221	f
1222	f
1223	f
1224	f
1225	f
1226	f
1227	f
1228	f
1229	f
1230	f
1231	f
1232	f
1233	f
1234	f
1235	f
1236	f
1237	f
1238	f
\.


--
-- Data for Name: hammerfest_profiles; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_profiles (period, retrieved_at, hammerfest_server, hammerfest_user_id, best_score, best_level, season_score, quest_statuses, unlocked_items) FROM stdin;
\.


--
-- Data for Name: hammerfest_quest_status_map_items; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_quest_status_map_items (hammerfest_quest_status_map_id, hammerfest_quest_id, status) FROM stdin;
\.


--
-- Data for Name: hammerfest_quest_status_maps; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_quest_status_maps (hammerfest_quest_status_map_id, _sha3_256) FROM stdin;
\.


--
-- Data for Name: hammerfest_quests; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_quests (hammerfest_quest_id) FROM stdin;
0
1
2
3
4
5
6
7
8
9
10
11
12
13
14
15
16
17
18
19
20
21
22
23
24
25
26
27
28
29
30
31
32
33
34
35
36
37
38
39
40
41
42
43
44
45
46
47
48
49
50
51
52
53
54
55
56
57
58
59
60
61
62
63
64
65
66
67
68
69
70
71
72
73
74
75
\.


--
-- Data for Name: hammerfest_servers; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_servers (hammerfest_server) FROM stdin;
hammerfest.fr
hfest.net
hammerfest.es
\.


--
-- Data for Name: hammerfest_sessions; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_sessions (hammerfest_server, hammerfest_session_key, _hammerfest_session_key_hash, hammerfest_user_id, ctime, atime) FROM stdin;
\.


--
-- Data for Name: hammerfest_shops; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_shops (period, retrieved_at, hammerfest_server, hammerfest_user_id, weekly_tokens, purchased_tokens, has_quest_bonus) FROM stdin;
\.


--
-- Data for Name: hammerfest_tokens; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_tokens (period, retrieved_at, hammerfest_server, hammerfest_user_id, tokens) FROM stdin;
\.


--
-- Data for Name: hammerfest_unlocked_item_set_items; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_unlocked_item_set_items (hammerfest_unlocked_item_set_id, hammerfest_item_id) FROM stdin;
\.


--
-- Data for Name: hammerfest_unlocked_item_sets; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_unlocked_item_sets (hammerfest_unlocked_item_set_id, _sha3_256) FROM stdin;
\.


--
-- Data for Name: hammerfest_user_achievements; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_user_achievements (period, retrieved_at, hammerfest_server, hammerfest_user_id, has_carrot, ladder_level) FROM stdin;
\.


--
-- Data for Name: hammerfest_user_links; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_user_links (user_id, hammerfest_server, hammerfest_user_id, linked_by, period, unlinked_by) FROM stdin;
\.


--
-- Data for Name: hammerfest_users; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.hammerfest_users (hammerfest_server, username, hammerfest_user_id, archived_at) FROM stdin;
\.


--
-- Data for Name: oauth_access_tokens; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.oauth_access_tokens (oauth_access_token_id, oauth_client_id, user_id, ctime, atime) FROM stdin;
\.


--
-- Data for Name: oauth_clients; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.oauth_clients (oauth_client_id, key, ctime, display_name, display_name_mtime, app_uri, app_uri_mtime, callback_uri, callback_uri_mtime, secret, secret_mtime, owner_id) FROM stdin;
7a726bc4-19d6-4969-884f-2e6fde2ed5a6	eternalfest@clients	2021-09-28 11:48:35.869+00	Eternalfest	2021-09-28 11:48:35.869+00	http://localhost:50313/	2021-09-28 11:48:35.869+00	http://localhost:50313/oauth/callback	2021-09-28 11:48:35.869+00	\\xc30d040703020f32e4e92c3776e364d29101103d2a4ea2df1af75fb62cadaf5cf0aba3cf0ce2870a318f1f9265e156e2f45c4369bfff5b4815c11ee43635b20be149aa81a4ff7e8cd3a0e113e0e66cc3f2cb577180878689df60ad8f5b94f8dfc1be34bca2d729d2bd8df7b2d354a90fb55c96c9ca7d6c9ced91c133c80b3d606f482747d8bfeb18ab7c5e1a3211056910e36a2a59fd65519881862b0c75e1fc5bb3	2021-09-28 11:48:35.869+00	\N
90f00ce9-069b-463a-a6df-84bbea269c51	myhordes@clients	2021-09-28 11:48:36.875+00	MyHordes	2021-09-28 11:48:36.875+00	http://myhordes.localhost/	2021-09-28 11:48:36.875+00	http://myhordes.localhost/oauth/callback	2021-09-28 11:48:36.875+00	\\xc30d04070302481209ee168055b966d2910160b2ff9882b4c286da3f0f4b9a30f08d91ad83eb6c04e40aab139f70ef0d2cfdc77c2decc24595f4ca76d014e31023f0c77521de1d9502023a201a142f55778cf5c920e56a01f232edaed772cda77b0caae65ab89fff4256b13688a0ea99523b49ddab964e7212c439940273997c37049d40174e2cd5cf0fd60a64bcbb2b139b295c9308030c52b280051e3aefbb02c7	2021-09-28 11:48:36.875+00	\N
fec04159-965e-4c57-a3ec-19ad94105e49	neoparc@clients	2021-09-28 11:48:37.912+00	NeoParc	2021-09-28 11:48:37.912+00	http://neoparc.localhost/	2021-09-28 11:48:37.912+00	http://neoparc.localhost/oauth/callback	2021-09-28 11:48:37.912+00	\\xc30d0407030274048f9951983c8475d291010bb5bc22672ecca9f35d4626cbd68ee5b4a33427e9b9ecb187c7f588b377c7b719fc51f1dc5d2780c047614758141839a9ef57cc879dba4febdcb4a9f85ab5214a5c035089d9224853c69c63e1642390c4460b931f216c36b48bc627ca1964c3cf8e70188634972ea18f5b1ae8793b75234af97d9ec2c7de304831dc349803e72f830c08925f2ff7434d71bc4205f068	2021-09-28 11:48:37.912+00	\N
23df0b1c-8610-4458-9ff0-e00f7f73cf04	kadokadeo@clients	2021-09-28 11:48:38.865+00	Kadokadeo	2021-09-28 11:48:38.865+00	http://kadokadeo.localhost/	2021-09-28 11:48:38.865+00	http://kadokadeo.localhost/oauth/callback	2021-09-28 11:48:38.865+00	\\xc30d0407030209bdf7c5ed95389c65d29101a6a17808780a31c820ea7aec01c532c6325868a4199602bb2def6bf12071a332f2d566d71600d0b33798b15bd5edc618b3852e33ac870892ee96c7d057d26424df6a5e13e4d37b97fb5353a7ddeff42cf8d191d8b91099a547f98f4219042e24621dce6f1b0de5f92afa010b94d515604d35ed479f6e41ff547209699ed04db738a3c41e72b62df8310714658b491872	2021-09-28 11:48:38.865+00	\N
aca15f9c-177a-4a61-b229-1cd2abb5e117	mjrt@clients	2021-09-28 11:48:39.845+00	Mjrt	2021-09-28 11:48:39.845+00	http://mjrt.localhost/	2021-09-28 11:48:39.845+00	http://mjrt.localhost/oauth/callback	2021-09-28 11:48:39.845+00	\\xc30d0407030278b2c530ccd4c15f6ed291013b7a76ed1a207b4398e2f91f99dda0b54858ae3da3d3b305bc49bacba11c657e62d62ecffc27452704172c826af10c31014995d4dd2f347d2f604735b178e6e7d1ce1df2357c53c22d1419ef1b00ef77a9a7aee6e31befddd2f6277fe22585ff5db4e9d9af936b5a23817e18e7cabd4fe4e9937535cd5940fc11d02c5e8bb3e947b25aee49a51bc29b4d6cc236913ca4	2021-09-28 11:48:39.845+00	\N
ebf7f455-29e8-43fd-9634-d825cf78c89a	dinorpg@clients	2021-09-28 11:48:40.887+00	DinoRPG	2021-09-28 11:48:40.887+00	http://localhost:8080/	2021-09-28 11:48:40.887+00	http://localhost:8080/authentication	2021-09-28 11:48:40.887+00	\\xc30d04070302bd4d9923fa35fa4a71d29101dbb52939aae3864f2711ef4928aabb77dc15658e6ca4823efd080a47ba08276d788cd723f3c1aeba8c334b9bb1afc898c771d4cfe3852503683f144fcf3287ea782bb2d0b7febade526e271d4a0855cb66488317d5058faeb7e4f3fbdf95ba3f100157285ad313d51c0e4664a15202ee56bd2d243ddc6a04ceb6cd77b92f8c8b159c9910a77f23c1de2f94e093df68aa	2021-09-28 11:48:40.887+00	\N
\.


--
-- Data for Name: old_dinoparc_sessions; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.old_dinoparc_sessions (dinoparc_server, dinoparc_session_key, _dinoparc_session_key_hash, dinoparc_user_id, ctime, atime, dtime) FROM stdin;
\.


--
-- Data for Name: old_hammerfest_sessions; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.old_hammerfest_sessions (hammerfest_server, hammerfest_session_key, _hammerfest_session_key_hash, hammerfest_user_id, ctime, atime, dtime) FROM stdin;
\.


--
-- Data for Name: old_oauth_client_app_uris; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.old_oauth_client_app_uris (oauth_client_id, start_time, app_uri) FROM stdin;
\.


--
-- Data for Name: old_oauth_client_callback_uris; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.old_oauth_client_callback_uris (oauth_client_id, start_time, callback_uri) FROM stdin;
\.


--
-- Data for Name: old_oauth_client_display_names; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.old_oauth_client_display_names (oauth_client_id, start_time, display_name) FROM stdin;
\.


--
-- Data for Name: old_oauth_client_secrets; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.old_oauth_client_secrets (oauth_client_id, start_time, secret) FROM stdin;
\.


--
-- Data for Name: old_twinoid_access_tokens; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.old_twinoid_access_tokens (twinoid_access_token, _twinoid_access_token_hash, twinoid_user_id, ctime, atime, dtime, expiration_time) FROM stdin;
\.


--
-- Data for Name: old_twinoid_refresh_tokens; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.old_twinoid_refresh_tokens (twinoid_refresh_token, _twinoid_refresh_token_hash, twinoid_user_id, ctime, atime, dtime) FROM stdin;
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.sessions (session_id, user_id, ctime, atime, data) FROM stdin;
\.


--
-- Data for Name: twinoid_access_tokens; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.twinoid_access_tokens (twinoid_access_token, _twinoid_access_token_hash, twinoid_user_id, ctime, atime, expiration_time) FROM stdin;
\.


--
-- Data for Name: twinoid_refresh_tokens; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.twinoid_refresh_tokens (twinoid_refresh_token, _twinoid_refresh_token_hash, twinoid_user_id, ctime, atime) FROM stdin;
\.


--
-- Data for Name: twinoid_user_links; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.twinoid_user_links (user_id, twinoid_user_id, linked_by, period, unlinked_by) FROM stdin;
\.


--
-- Data for Name: twinoid_users; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.twinoid_users (twinoid_user_id, name, archived_at) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.users (user_id, created_at, is_administrator, _is_current) FROM stdin;
\.


--
-- Data for Name: users_history; Type: TABLE DATA; Schema: public; Owner: etwin.dev.admin
--

COPY public.users_history (user_id, period, _is_current, updated_by, display_name, username, email, password) FROM stdin;
\.


--
-- Name: _post_formatting_costs _post_formatting_costs_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public._post_formatting_costs
    ADD CONSTRAINT _post_formatting_costs_pkey PRIMARY KEY (forum_post_revision_id, formatting);


--
-- Name: announcements announcements_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_pkey PRIMARY KEY (announcement_id);


--
-- Name: dinoparc_bills dinoparc_bills_dinoparc_server_dinoparc_user_id_period_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_bills
    ADD CONSTRAINT dinoparc_bills_dinoparc_server_dinoparc_user_id_period_excl EXCLUDE USING gist (dinoparc_server WITH =, dinoparc_user_id WITH =, period WITH &&);


--
-- Name: dinoparc_bills dinoparc_bills_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_bills
    ADD CONSTRAINT dinoparc_bills_pkey PRIMARY KEY (period, dinoparc_server, dinoparc_user_id);


--
-- Name: dinoparc_coins dinoparc_coins_dinoparc_server_dinoparc_user_id_period_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_coins
    ADD CONSTRAINT dinoparc_coins_dinoparc_server_dinoparc_user_id_period_excl EXCLUDE USING gist (dinoparc_server WITH =, dinoparc_user_id WITH =, period WITH &&);


--
-- Name: dinoparc_coins dinoparc_coins_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_coins
    ADD CONSTRAINT dinoparc_coins_pkey PRIMARY KEY (period, dinoparc_server, dinoparc_user_id);


--
-- Name: dinoparc_collections dinoparc_collections_dinoparc_server_dinoparc_user_id_peri_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_collections
    ADD CONSTRAINT dinoparc_collections_dinoparc_server_dinoparc_user_id_peri_excl EXCLUDE USING gist (dinoparc_server WITH =, dinoparc_user_id WITH =, period WITH &&);


--
-- Name: dinoparc_collections dinoparc_collections_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_collections
    ADD CONSTRAINT dinoparc_collections_pkey PRIMARY KEY (period, dinoparc_server, dinoparc_user_id);


--
-- Name: dinoparc_dinoz_levels dinoparc_dinoz_levels_dinoparc_server_dinoparc_dinoz_id_pe_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_levels
    ADD CONSTRAINT dinoparc_dinoz_levels_dinoparc_server_dinoparc_dinoz_id_pe_excl EXCLUDE USING gist (dinoparc_server WITH =, dinoparc_dinoz_id WITH =, period WITH &&);


--
-- Name: dinoparc_dinoz_levels dinoparc_dinoz_levels_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_levels
    ADD CONSTRAINT dinoparc_dinoz_levels_pkey PRIMARY KEY (period, dinoparc_server, dinoparc_dinoz_id);


--
-- Name: dinoparc_dinoz_locations dinoparc_dinoz_locations_dinoparc_server_dinoparc_dinoz_id_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_locations
    ADD CONSTRAINT dinoparc_dinoz_locations_dinoparc_server_dinoparc_dinoz_id_excl EXCLUDE USING gist (dinoparc_server WITH =, dinoparc_dinoz_id WITH =, period WITH &&);


--
-- Name: dinoparc_dinoz_locations dinoparc_dinoz_locations_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_locations
    ADD CONSTRAINT dinoparc_dinoz_locations_pkey PRIMARY KEY (period, dinoparc_server, dinoparc_dinoz_id);


--
-- Name: dinoparc_dinoz_names dinoparc_dinoz_names_dinoparc_server_dinoparc_dinoz_id_per_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_names
    ADD CONSTRAINT dinoparc_dinoz_names_dinoparc_server_dinoparc_dinoz_id_per_excl EXCLUDE USING gist (dinoparc_server WITH =, dinoparc_dinoz_id WITH =, period WITH &&);


--
-- Name: dinoparc_dinoz_names dinoparc_dinoz_names_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_names
    ADD CONSTRAINT dinoparc_dinoz_names_pkey PRIMARY KEY (period, dinoparc_server, dinoparc_dinoz_id);


--
-- Name: dinoparc_dinoz_owners dinoparc_dinoz_owners_dinoparc_server_dinoparc_dinoz_id_pe_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_owners
    ADD CONSTRAINT dinoparc_dinoz_owners_dinoparc_server_dinoparc_dinoz_id_pe_excl EXCLUDE USING gist (dinoparc_server WITH =, dinoparc_dinoz_id WITH =, period WITH &&);


--
-- Name: dinoparc_dinoz_owners dinoparc_dinoz_owners_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_owners
    ADD CONSTRAINT dinoparc_dinoz_owners_pkey PRIMARY KEY (period, dinoparc_server, dinoparc_dinoz_id);


--
-- Name: dinoparc_dinoz dinoparc_dinoz_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz
    ADD CONSTRAINT dinoparc_dinoz_pkey PRIMARY KEY (dinoparc_server, dinoparc_dinoz_id);


--
-- Name: dinoparc_dinoz_profiles dinoparc_dinoz_profiles_dinoparc_server_dinoparc_dinoz_id__excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_profiles
    ADD CONSTRAINT dinoparc_dinoz_profiles_dinoparc_server_dinoparc_dinoz_id__excl EXCLUDE USING gist (dinoparc_server WITH =, dinoparc_dinoz_id WITH =, period WITH &&);


--
-- Name: dinoparc_dinoz_profiles dinoparc_dinoz_profiles_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_profiles
    ADD CONSTRAINT dinoparc_dinoz_profiles_pkey PRIMARY KEY (period, dinoparc_server, dinoparc_dinoz_id);


--
-- Name: dinoparc_dinoz_skins dinoparc_dinoz_skins_dinoparc_server_dinoparc_dinoz_id_per_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_skins
    ADD CONSTRAINT dinoparc_dinoz_skins_dinoparc_server_dinoparc_dinoz_id_per_excl EXCLUDE USING gist (dinoparc_server WITH =, dinoparc_dinoz_id WITH =, period WITH &&);


--
-- Name: dinoparc_dinoz_skins dinoparc_dinoz_skins_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_skins
    ADD CONSTRAINT dinoparc_dinoz_skins_pkey PRIMARY KEY (period, dinoparc_server, dinoparc_dinoz_id);


--
-- Name: dinoparc_epic_reward_set_items dinoparc_epic_reward_set_items_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_epic_reward_set_items
    ADD CONSTRAINT dinoparc_epic_reward_set_items_pkey PRIMARY KEY (dinoparc_epic_reward_set_id, dinoparc_epic_reward_key);


--
-- Name: dinoparc_epic_reward_sets dinoparc_epic_reward_sets__sha3_256_key; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_epic_reward_sets
    ADD CONSTRAINT dinoparc_epic_reward_sets__sha3_256_key UNIQUE (_sha3_256);


--
-- Name: dinoparc_epic_reward_sets dinoparc_epic_reward_sets_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_epic_reward_sets
    ADD CONSTRAINT dinoparc_epic_reward_sets_pkey PRIMARY KEY (dinoparc_epic_reward_set_id);


--
-- Name: dinoparc_inventories dinoparc_inventories_dinoparc_server_dinoparc_user_id_peri_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_inventories
    ADD CONSTRAINT dinoparc_inventories_dinoparc_server_dinoparc_user_id_peri_excl EXCLUDE USING gist (dinoparc_server WITH =, dinoparc_user_id WITH =, period WITH &&);


--
-- Name: dinoparc_inventories dinoparc_inventories_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_inventories
    ADD CONSTRAINT dinoparc_inventories_pkey PRIMARY KEY (period, dinoparc_server, dinoparc_user_id);


--
-- Name: dinoparc_item_count_map_items dinoparc_item_count_map_items_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_item_count_map_items
    ADD CONSTRAINT dinoparc_item_count_map_items_pkey PRIMARY KEY (dinoparc_item_count_map_id, dinoparc_item_id);


--
-- Name: dinoparc_item_count_maps dinoparc_item_count_maps__sha3_256_key; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_item_count_maps
    ADD CONSTRAINT dinoparc_item_count_maps__sha3_256_key UNIQUE (_sha3_256);


--
-- Name: dinoparc_item_count_maps dinoparc_item_count_maps_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_item_count_maps
    ADD CONSTRAINT dinoparc_item_count_maps_pkey PRIMARY KEY (dinoparc_item_count_map_id);


--
-- Name: dinoparc_locations dinoparc_locations_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_locations
    ADD CONSTRAINT dinoparc_locations_pkey PRIMARY KEY (dinoparc_location_id);


--
-- Name: dinoparc_reward_set_items dinoparc_reward_set_items_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_reward_set_items
    ADD CONSTRAINT dinoparc_reward_set_items_pkey PRIMARY KEY (dinoparc_reward_set_id, dinoparc_reward_id);


--
-- Name: dinoparc_reward_sets dinoparc_reward_sets__sha3_256_key; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_reward_sets
    ADD CONSTRAINT dinoparc_reward_sets__sha3_256_key UNIQUE (_sha3_256);


--
-- Name: dinoparc_reward_sets dinoparc_reward_sets_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_reward_sets
    ADD CONSTRAINT dinoparc_reward_sets_pkey PRIMARY KEY (dinoparc_reward_set_id);


--
-- Name: dinoparc_servers dinoparc_servers_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_servers
    ADD CONSTRAINT dinoparc_servers_pkey PRIMARY KEY (dinoparc_server);


--
-- Name: dinoparc_sessions dinoparc_sessions_dinoparc_server_dinoparc_user_id_key; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_sessions
    ADD CONSTRAINT dinoparc_sessions_dinoparc_server_dinoparc_user_id_key UNIQUE (dinoparc_server, dinoparc_user_id);


--
-- Name: dinoparc_sessions dinoparc_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_sessions
    ADD CONSTRAINT dinoparc_sessions_pkey PRIMARY KEY (dinoparc_server, _dinoparc_session_key_hash);


--
-- Name: dinoparc_skill_level_map_items dinoparc_skill_level_map_items_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_skill_level_map_items
    ADD CONSTRAINT dinoparc_skill_level_map_items_pkey PRIMARY KEY (dinoparc_skill_level_map_id, dinoparc_skill);


--
-- Name: dinoparc_skill_level_maps dinoparc_skill_level_maps__sha3_256_key; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_skill_level_maps
    ADD CONSTRAINT dinoparc_skill_level_maps__sha3_256_key UNIQUE (_sha3_256);


--
-- Name: dinoparc_skill_level_maps dinoparc_skill_level_maps_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_skill_level_maps
    ADD CONSTRAINT dinoparc_skill_level_maps_pkey PRIMARY KEY (dinoparc_skill_level_map_id);


--
-- Name: dinoparc_user_dinoz_counts dinoparc_user_dinoz_counts_dinoparc_server_dinoparc_user_i_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_user_dinoz_counts
    ADD CONSTRAINT dinoparc_user_dinoz_counts_dinoparc_server_dinoparc_user_i_excl EXCLUDE USING gist (dinoparc_server WITH =, dinoparc_user_id WITH =, period WITH &&);


--
-- Name: dinoparc_user_dinoz_counts dinoparc_user_dinoz_counts_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_user_dinoz_counts
    ADD CONSTRAINT dinoparc_user_dinoz_counts_pkey PRIMARY KEY (period, dinoparc_server, dinoparc_user_id);


--
-- Name: dinoparc_user_dinoz dinoparc_user_dinoz_dinoparc_server_dinoparc_dinoz_id_peri_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_user_dinoz
    ADD CONSTRAINT dinoparc_user_dinoz_dinoparc_server_dinoparc_dinoz_id_peri_excl EXCLUDE USING gist (dinoparc_server WITH =, dinoparc_dinoz_id WITH =, period WITH &&);


--
-- Name: dinoparc_user_dinoz dinoparc_user_dinoz_dinoparc_server_dinoparc_user_id_offse_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_user_dinoz
    ADD CONSTRAINT dinoparc_user_dinoz_dinoparc_server_dinoparc_user_id_offse_excl EXCLUDE USING gist (dinoparc_server WITH =, dinoparc_user_id WITH =, offset_in_list WITH =, period WITH &&);


--
-- Name: dinoparc_user_dinoz dinoparc_user_dinoz_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_user_dinoz
    ADD CONSTRAINT dinoparc_user_dinoz_pkey PRIMARY KEY (period, dinoparc_server, dinoparc_user_id, offset_in_list);


--
-- Name: dinoparc_user_links dinoparc_user_links_dinoparc_server_dinoparc_user_id_perio_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_user_links
    ADD CONSTRAINT dinoparc_user_links_dinoparc_server_dinoparc_user_id_perio_excl EXCLUDE USING gist (dinoparc_server WITH =, dinoparc_user_id WITH =, period WITH &&);


--
-- Name: dinoparc_user_links dinoparc_user_links_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_user_links
    ADD CONSTRAINT dinoparc_user_links_pkey PRIMARY KEY (user_id, dinoparc_server, dinoparc_user_id, period);


--
-- Name: dinoparc_user_links dinoparc_user_links_user_id_dinoparc_server_period_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_user_links
    ADD CONSTRAINT dinoparc_user_links_user_id_dinoparc_server_period_excl EXCLUDE USING gist (user_id WITH =, dinoparc_server WITH =, period WITH &&);


--
-- Name: dinoparc_users dinoparc_users_dinoparc_server_username_key; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_users
    ADD CONSTRAINT dinoparc_users_dinoparc_server_username_key UNIQUE (dinoparc_server, username);


--
-- Name: dinoparc_users dinoparc_users_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_users
    ADD CONSTRAINT dinoparc_users_pkey PRIMARY KEY (dinoparc_server, dinoparc_user_id);


--
-- Name: email_addresses email_addresses_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.email_addresses
    ADD CONSTRAINT email_addresses_pkey PRIMARY KEY (_hash);


--
-- Name: forum_post_revisions forum_post_revisions_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_post_revisions
    ADD CONSTRAINT forum_post_revisions_pkey PRIMARY KEY (forum_post_revision_id);


--
-- Name: forum_posts forum_posts_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_posts
    ADD CONSTRAINT forum_posts_pkey PRIMARY KEY (forum_post_id);


--
-- Name: forum_role_grants forum_role_grants_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_role_grants
    ADD CONSTRAINT forum_role_grants_pkey PRIMARY KEY (forum_section_id, user_id);


--
-- Name: forum_role_revocations forum_role_revocations_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_role_revocations
    ADD CONSTRAINT forum_role_revocations_pkey PRIMARY KEY (forum_section_id, user_id, start_time);


--
-- Name: forum_sections forum_sections_key_key; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_sections
    ADD CONSTRAINT forum_sections_key_key UNIQUE (key);


--
-- Name: forum_sections forum_sections_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_sections
    ADD CONSTRAINT forum_sections_pkey PRIMARY KEY (forum_section_id);


--
-- Name: forum_threads forum_threads_key_key; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_threads
    ADD CONSTRAINT forum_threads_key_key UNIQUE (key);


--
-- Name: forum_threads forum_threads_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_threads
    ADD CONSTRAINT forum_threads_pkey PRIMARY KEY (forum_thread_id);


--
-- Name: hammerfest_best_season_ranks hammerfest_best_season_ranks_hammerfest_server_hammerfest__excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_best_season_ranks
    ADD CONSTRAINT hammerfest_best_season_ranks_hammerfest_server_hammerfest__excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_user_id WITH =, period WITH &&);


--
-- Name: hammerfest_best_season_ranks hammerfest_best_season_ranks_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_best_season_ranks
    ADD CONSTRAINT hammerfest_best_season_ranks_pkey PRIMARY KEY (hammerfest_server, hammerfest_user_id, period);


--
-- Name: hammerfest_emails hammerfest_emails_hammerfest_server_email_period_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_emails
    ADD CONSTRAINT hammerfest_emails_hammerfest_server_email_period_excl EXCLUDE USING gist (hammerfest_server WITH =, email WITH =, period WITH &&);


--
-- Name: hammerfest_emails hammerfest_emails_hammerfest_server_hammerfest_user_id_per_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_emails
    ADD CONSTRAINT hammerfest_emails_hammerfest_server_hammerfest_user_id_per_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_user_id WITH =, period WITH &&);


--
-- Name: hammerfest_emails hammerfest_emails_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_emails
    ADD CONSTRAINT hammerfest_emails_pkey PRIMARY KEY (period, hammerfest_server, hammerfest_user_id);


--
-- Name: hammerfest_forum_post_ids hammerfest_forum_post_ids_hammerfest_server_hammerfest_pos_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_post_ids
    ADD CONSTRAINT hammerfest_forum_post_ids_hammerfest_server_hammerfest_pos_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_post_id WITH =, period WITH &&);


--
-- Name: hammerfest_forum_post_ids hammerfest_forum_post_ids_hammerfest_server_hammerfest_thr_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_post_ids
    ADD CONSTRAINT hammerfest_forum_post_ids_hammerfest_server_hammerfest_thr_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_thread_id WITH =, page WITH =, offset_in_list WITH =, period WITH &&);


--
-- Name: hammerfest_forum_post_ids hammerfest_forum_post_ids_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_post_ids
    ADD CONSTRAINT hammerfest_forum_post_ids_pkey PRIMARY KEY (period, hammerfest_server, hammerfest_thread_id, page, offset_in_list);


--
-- Name: hammerfest_forum_posts hammerfest_forum_posts_hammerfest_server_hammerfest_thread_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_posts
    ADD CONSTRAINT hammerfest_forum_posts_hammerfest_server_hammerfest_thread_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_thread_id WITH =, page WITH =, offset_in_list WITH =, period WITH &&);


--
-- Name: hammerfest_forum_posts hammerfest_forum_posts_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_posts
    ADD CONSTRAINT hammerfest_forum_posts_pkey PRIMARY KEY (period, hammerfest_server, hammerfest_thread_id, page, offset_in_list);


--
-- Name: hammerfest_forum_roles hammerfest_forum_roles_hammerfest_server_hammerfest_user_i_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_roles
    ADD CONSTRAINT hammerfest_forum_roles_hammerfest_server_hammerfest_user_i_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_user_id WITH =, period WITH &&);


--
-- Name: hammerfest_forum_roles hammerfest_forum_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_roles
    ADD CONSTRAINT hammerfest_forum_roles_pkey PRIMARY KEY (hammerfest_server, hammerfest_user_id, period);


--
-- Name: hammerfest_forum_theme_counts hammerfest_forum_theme_counts_hammerfest_server_hammerfest_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_theme_counts
    ADD CONSTRAINT hammerfest_forum_theme_counts_hammerfest_server_hammerfest_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_theme_id WITH =, period WITH &&);


--
-- Name: hammerfest_forum_theme_counts hammerfest_forum_theme_counts_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_theme_counts
    ADD CONSTRAINT hammerfest_forum_theme_counts_pkey PRIMARY KEY (period, hammerfest_server, hammerfest_theme_id);


--
-- Name: hammerfest_forum_theme_page_counts hammerfest_forum_theme_page_c_hammerfest_server_hammerfest_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_theme_page_counts
    ADD CONSTRAINT hammerfest_forum_theme_page_c_hammerfest_server_hammerfest_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_theme_id WITH =, page WITH =, period WITH &&);


--
-- Name: hammerfest_forum_theme_page_counts hammerfest_forum_theme_page_counts_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_theme_page_counts
    ADD CONSTRAINT hammerfest_forum_theme_page_counts_pkey PRIMARY KEY (period, hammerfest_server, hammerfest_theme_id, page);


--
-- Name: hammerfest_forum_theme_threads hammerfest_forum_theme_threa_hammerfest_server_hammerfest_excl1; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_theme_threads
    ADD CONSTRAINT hammerfest_forum_theme_threa_hammerfest_server_hammerfest_excl1 EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_thread_id WITH =, period WITH &&);


--
-- Name: hammerfest_forum_theme_threads hammerfest_forum_theme_thread_hammerfest_server_hammerfest_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_theme_threads
    ADD CONSTRAINT hammerfest_forum_theme_thread_hammerfest_server_hammerfest_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_theme_id WITH =, page WITH =, offset_in_list WITH =, period WITH &&);


--
-- Name: hammerfest_forum_theme_threads hammerfest_forum_theme_threads_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_theme_threads
    ADD CONSTRAINT hammerfest_forum_theme_threads_pkey PRIMARY KEY (period, hammerfest_server, hammerfest_theme_id, page, offset_in_list);


--
-- Name: hammerfest_forum_themes hammerfest_forum_themes_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_themes
    ADD CONSTRAINT hammerfest_forum_themes_pkey PRIMARY KEY (hammerfest_server, hammerfest_theme_id);


--
-- Name: hammerfest_forum_thread_page_counts hammerfest_forum_thread_page__hammerfest_server_hammerfest_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_thread_page_counts
    ADD CONSTRAINT hammerfest_forum_thread_page__hammerfest_server_hammerfest_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_thread_id WITH =, page WITH =, period WITH &&);


--
-- Name: hammerfest_forum_thread_page_counts hammerfest_forum_thread_page_counts_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_thread_page_counts
    ADD CONSTRAINT hammerfest_forum_thread_page_counts_pkey PRIMARY KEY (period, hammerfest_server, hammerfest_thread_id, page);


--
-- Name: hammerfest_forum_thread_shared_meta hammerfest_forum_thread_share_hammerfest_server_hammerfest_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_thread_shared_meta
    ADD CONSTRAINT hammerfest_forum_thread_share_hammerfest_server_hammerfest_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_thread_id WITH =, period WITH &&);


--
-- Name: hammerfest_forum_thread_shared_meta hammerfest_forum_thread_shared_meta_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_thread_shared_meta
    ADD CONSTRAINT hammerfest_forum_thread_shared_meta_pkey PRIMARY KEY (period, hammerfest_server, hammerfest_thread_id);


--
-- Name: hammerfest_forum_thread_theme_meta hammerfest_forum_thread_theme_hammerfest_server_hammerfest_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_thread_theme_meta
    ADD CONSTRAINT hammerfest_forum_thread_theme_hammerfest_server_hammerfest_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_thread_id WITH =, period WITH &&);


--
-- Name: hammerfest_forum_thread_theme_meta hammerfest_forum_thread_theme_meta_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_thread_theme_meta
    ADD CONSTRAINT hammerfest_forum_thread_theme_meta_pkey PRIMARY KEY (period, hammerfest_server, hammerfest_thread_id);


--
-- Name: hammerfest_forum_threads hammerfest_forum_threads_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_threads
    ADD CONSTRAINT hammerfest_forum_threads_pkey PRIMARY KEY (hammerfest_server, hammerfest_thread_id);


--
-- Name: hammerfest_godchild_lists hammerfest_godchild_lists_hammerfest_server_hammerfest_use_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_godchild_lists
    ADD CONSTRAINT hammerfest_godchild_lists_hammerfest_server_hammerfest_use_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_user_id WITH =, period WITH &&);


--
-- Name: hammerfest_godchild_lists hammerfest_godchild_lists_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_godchild_lists
    ADD CONSTRAINT hammerfest_godchild_lists_pkey PRIMARY KEY (period, hammerfest_server, hammerfest_user_id);


--
-- Name: hammerfest_godchildren hammerfest_godchildren_hammerfest_server_godchild_id_perio_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_godchildren
    ADD CONSTRAINT hammerfest_godchildren_hammerfest_server_godchild_id_perio_excl EXCLUDE USING gist (hammerfest_server WITH =, godchild_id WITH =, period WITH &&);


--
-- Name: hammerfest_godchildren hammerfest_godchildren_hammerfest_server_hammerfest_user_i_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_godchildren
    ADD CONSTRAINT hammerfest_godchildren_hammerfest_server_hammerfest_user_i_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_user_id WITH =, offset_in_list WITH =, period WITH &&);


--
-- Name: hammerfest_godchildren hammerfest_godchildren_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_godchildren
    ADD CONSTRAINT hammerfest_godchildren_pkey PRIMARY KEY (period, hammerfest_server, hammerfest_user_id, offset_in_list);


--
-- Name: hammerfest_inventories hammerfest_inventories_hammerfest_server_hammerfest_user_i_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_inventories
    ADD CONSTRAINT hammerfest_inventories_hammerfest_server_hammerfest_user_i_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_user_id WITH =, period WITH &&);


--
-- Name: hammerfest_inventories hammerfest_inventories_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_inventories
    ADD CONSTRAINT hammerfest_inventories_pkey PRIMARY KEY (period, hammerfest_server, hammerfest_user_id);


--
-- Name: hammerfest_item_count_map_items hammerfest_item_count_map_items_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_item_count_map_items
    ADD CONSTRAINT hammerfest_item_count_map_items_pkey PRIMARY KEY (hammerfest_item_count_map_id, hammerfest_item_id);


--
-- Name: hammerfest_item_count_maps hammerfest_item_count_maps__sha3_256_key; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_item_count_maps
    ADD CONSTRAINT hammerfest_item_count_maps__sha3_256_key UNIQUE (_sha3_256);


--
-- Name: hammerfest_item_count_maps hammerfest_item_count_maps_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_item_count_maps
    ADD CONSTRAINT hammerfest_item_count_maps_pkey PRIMARY KEY (hammerfest_item_count_map_id);


--
-- Name: hammerfest_items hammerfest_items_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_items
    ADD CONSTRAINT hammerfest_items_pkey PRIMARY KEY (hammerfest_item_id);


--
-- Name: hammerfest_profiles hammerfest_profiles_hammerfest_server_hammerfest_user_id_p_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_profiles
    ADD CONSTRAINT hammerfest_profiles_hammerfest_server_hammerfest_user_id_p_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_user_id WITH =, period WITH &&);


--
-- Name: hammerfest_profiles hammerfest_profiles_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_profiles
    ADD CONSTRAINT hammerfest_profiles_pkey PRIMARY KEY (period, hammerfest_server, hammerfest_user_id);


--
-- Name: hammerfest_quest_status_map_items hammerfest_quest_status_map_items_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_quest_status_map_items
    ADD CONSTRAINT hammerfest_quest_status_map_items_pkey PRIMARY KEY (hammerfest_quest_status_map_id, hammerfest_quest_id);


--
-- Name: hammerfest_quest_status_maps hammerfest_quest_status_maps__sha3_256_key; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_quest_status_maps
    ADD CONSTRAINT hammerfest_quest_status_maps__sha3_256_key UNIQUE (_sha3_256);


--
-- Name: hammerfest_quest_status_maps hammerfest_quest_status_maps_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_quest_status_maps
    ADD CONSTRAINT hammerfest_quest_status_maps_pkey PRIMARY KEY (hammerfest_quest_status_map_id);


--
-- Name: hammerfest_quests hammerfest_quests_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_quests
    ADD CONSTRAINT hammerfest_quests_pkey PRIMARY KEY (hammerfest_quest_id);


--
-- Name: hammerfest_servers hammerfest_servers_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_servers
    ADD CONSTRAINT hammerfest_servers_pkey PRIMARY KEY (hammerfest_server);


--
-- Name: hammerfest_sessions hammerfest_sessions_hammerfest_server_hammerfest_user_id_key; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_sessions
    ADD CONSTRAINT hammerfest_sessions_hammerfest_server_hammerfest_user_id_key UNIQUE (hammerfest_server, hammerfest_user_id);


--
-- Name: hammerfest_sessions hammerfest_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_sessions
    ADD CONSTRAINT hammerfest_sessions_pkey PRIMARY KEY (hammerfest_server, _hammerfest_session_key_hash);


--
-- Name: hammerfest_shops hammerfest_shops_hammerfest_server_hammerfest_user_id_peri_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_shops
    ADD CONSTRAINT hammerfest_shops_hammerfest_server_hammerfest_user_id_peri_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_user_id WITH =, period WITH &&);


--
-- Name: hammerfest_shops hammerfest_shops_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_shops
    ADD CONSTRAINT hammerfest_shops_pkey PRIMARY KEY (period, hammerfest_server, hammerfest_user_id);


--
-- Name: hammerfest_tokens hammerfest_tokens_hammerfest_server_hammerfest_user_id_per_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_tokens
    ADD CONSTRAINT hammerfest_tokens_hammerfest_server_hammerfest_user_id_per_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_user_id WITH =, period WITH &&);


--
-- Name: hammerfest_tokens hammerfest_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_tokens
    ADD CONSTRAINT hammerfest_tokens_pkey PRIMARY KEY (period, hammerfest_server, hammerfest_user_id);


--
-- Name: hammerfest_unlocked_item_set_items hammerfest_unlocked_item_set_items_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_unlocked_item_set_items
    ADD CONSTRAINT hammerfest_unlocked_item_set_items_pkey PRIMARY KEY (hammerfest_unlocked_item_set_id, hammerfest_item_id);


--
-- Name: hammerfest_unlocked_item_sets hammerfest_unlocked_item_sets__sha3_256_key; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_unlocked_item_sets
    ADD CONSTRAINT hammerfest_unlocked_item_sets__sha3_256_key UNIQUE (_sha3_256);


--
-- Name: hammerfest_unlocked_item_sets hammerfest_unlocked_item_sets_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_unlocked_item_sets
    ADD CONSTRAINT hammerfest_unlocked_item_sets_pkey PRIMARY KEY (hammerfest_unlocked_item_set_id);


--
-- Name: hammerfest_user_achievements hammerfest_user_achievements_hammerfest_server_hammerfest__excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_user_achievements
    ADD CONSTRAINT hammerfest_user_achievements_hammerfest_server_hammerfest__excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_user_id WITH =, period WITH &&);


--
-- Name: hammerfest_user_achievements hammerfest_user_achievements_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_user_achievements
    ADD CONSTRAINT hammerfest_user_achievements_pkey PRIMARY KEY (period, hammerfest_server, hammerfest_user_id);


--
-- Name: hammerfest_user_links hammerfest_user_links_hammerfest_server_hammerfest_user_id_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_user_links
    ADD CONSTRAINT hammerfest_user_links_hammerfest_server_hammerfest_user_id_excl EXCLUDE USING gist (hammerfest_server WITH =, hammerfest_user_id WITH =, period WITH &&);


--
-- Name: hammerfest_user_links hammerfest_user_links_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_user_links
    ADD CONSTRAINT hammerfest_user_links_pkey PRIMARY KEY (user_id, hammerfest_server, hammerfest_user_id, period);


--
-- Name: hammerfest_user_links hammerfest_user_links_user_id_hammerfest_server_period_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_user_links
    ADD CONSTRAINT hammerfest_user_links_user_id_hammerfest_server_period_excl EXCLUDE USING gist (user_id WITH =, hammerfest_server WITH =, period WITH &&);


--
-- Name: hammerfest_users hammerfest_users_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_users
    ADD CONSTRAINT hammerfest_users_pkey PRIMARY KEY (hammerfest_server, hammerfest_user_id);


--
-- Name: hammerfest_users hammerfest_users_server_username_key; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_users
    ADD CONSTRAINT hammerfest_users_server_username_key UNIQUE (hammerfest_server, username);


--
-- Name: oauth_access_tokens oauth_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.oauth_access_tokens
    ADD CONSTRAINT oauth_access_tokens_pkey PRIMARY KEY (oauth_access_token_id);


--
-- Name: oauth_clients oauth_clients_key_key; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.oauth_clients
    ADD CONSTRAINT oauth_clients_key_key UNIQUE (key);


--
-- Name: oauth_clients oauth_clients_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.oauth_clients
    ADD CONSTRAINT oauth_clients_pkey PRIMARY KEY (oauth_client_id);


--
-- Name: old_dinoparc_sessions old_dinoparc_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.old_dinoparc_sessions
    ADD CONSTRAINT old_dinoparc_sessions_pkey PRIMARY KEY (dinoparc_server, _dinoparc_session_key_hash, ctime);


--
-- Name: old_hammerfest_sessions old_hammerfest_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.old_hammerfest_sessions
    ADD CONSTRAINT old_hammerfest_sessions_pkey PRIMARY KEY (hammerfest_server, _hammerfest_session_key_hash, ctime);


--
-- Name: old_oauth_client_app_uris old_oauth_client_app_uris_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.old_oauth_client_app_uris
    ADD CONSTRAINT old_oauth_client_app_uris_pkey PRIMARY KEY (oauth_client_id, start_time);


--
-- Name: old_oauth_client_callback_uris old_oauth_client_callback_uris_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.old_oauth_client_callback_uris
    ADD CONSTRAINT old_oauth_client_callback_uris_pkey PRIMARY KEY (oauth_client_id, start_time);


--
-- Name: old_oauth_client_display_names old_oauth_client_display_names_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.old_oauth_client_display_names
    ADD CONSTRAINT old_oauth_client_display_names_pkey PRIMARY KEY (oauth_client_id, start_time);


--
-- Name: old_oauth_client_secrets old_oauth_client_secrets_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.old_oauth_client_secrets
    ADD CONSTRAINT old_oauth_client_secrets_pkey PRIMARY KEY (oauth_client_id, start_time);


--
-- Name: old_twinoid_access_tokens old_twinoid_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.old_twinoid_access_tokens
    ADD CONSTRAINT old_twinoid_access_tokens_pkey PRIMARY KEY (_twinoid_access_token_hash, ctime);


--
-- Name: old_twinoid_refresh_tokens old_twinoid_refresh_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.old_twinoid_refresh_tokens
    ADD CONSTRAINT old_twinoid_refresh_tokens_pkey PRIMARY KEY (_twinoid_refresh_token_hash, ctime);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (session_id);


--
-- Name: twinoid_access_tokens twinoid_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.twinoid_access_tokens
    ADD CONSTRAINT twinoid_access_tokens_pkey PRIMARY KEY (_twinoid_access_token_hash);


--
-- Name: twinoid_access_tokens twinoid_access_tokens_twinoid_user_id_key; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.twinoid_access_tokens
    ADD CONSTRAINT twinoid_access_tokens_twinoid_user_id_key UNIQUE (twinoid_user_id);


--
-- Name: twinoid_refresh_tokens twinoid_refresh_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.twinoid_refresh_tokens
    ADD CONSTRAINT twinoid_refresh_tokens_pkey PRIMARY KEY (_twinoid_refresh_token_hash);


--
-- Name: twinoid_refresh_tokens twinoid_refresh_tokens_twinoid_user_id_key; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.twinoid_refresh_tokens
    ADD CONSTRAINT twinoid_refresh_tokens_twinoid_user_id_key UNIQUE (twinoid_user_id);


--
-- Name: twinoid_user_links twinoid_user_links_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.twinoid_user_links
    ADD CONSTRAINT twinoid_user_links_pkey PRIMARY KEY (user_id, twinoid_user_id, period);


--
-- Name: twinoid_user_links twinoid_user_links_twinoid_user_id_period_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.twinoid_user_links
    ADD CONSTRAINT twinoid_user_links_twinoid_user_id_period_excl EXCLUDE USING gist (twinoid_user_id WITH =, period WITH &&);


--
-- Name: twinoid_user_links twinoid_user_links_user_id_period_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.twinoid_user_links
    ADD CONSTRAINT twinoid_user_links_user_id_period_excl EXCLUDE USING gist (user_id WITH =, period WITH &&);


--
-- Name: twinoid_users twinoid_users_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.twinoid_users
    ADD CONSTRAINT twinoid_users_pkey PRIMARY KEY (twinoid_user_id);


--
-- Name: users_history users_history_email_period_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.users_history
    ADD CONSTRAINT users_history_email_period_excl EXCLUDE USING gist (email WITH =, period WITH &&);


--
-- Name: users_history users_history_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.users_history
    ADD CONSTRAINT users_history_pkey PRIMARY KEY (user_id, period);


--
-- Name: users_history users_history_user_id__is_current_key; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.users_history
    ADD CONSTRAINT users_history_user_id__is_current_key UNIQUE (user_id, _is_current);


--
-- Name: users_history users_history_user_id_period_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.users_history
    ADD CONSTRAINT users_history_user_id_period_excl EXCLUDE USING gist (user_id WITH =, period WITH &&);


--
-- Name: users_history users_history_username_period_excl; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.users_history
    ADD CONSTRAINT users_history_username_period_excl EXCLUDE USING gist (username WITH =, period WITH &&);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (user_id);


--
-- Name: announcements announcement__forum_thread__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcement__forum_thread__fk FOREIGN KEY (forum_thread_id) REFERENCES public.forum_threads(forum_thread_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: announcements announcement__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcement__user__fk FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dinoparc_bills dinoparc_bills__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_bills
    ADD CONSTRAINT dinoparc_bills__user__fk FOREIGN KEY (dinoparc_server, dinoparc_user_id) REFERENCES public.dinoparc_users(dinoparc_server, dinoparc_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_coins dinoparc_coins__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_coins
    ADD CONSTRAINT dinoparc_coins__user__fk FOREIGN KEY (dinoparc_server, dinoparc_user_id) REFERENCES public.dinoparc_users(dinoparc_server, dinoparc_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_collections dinoparc_collections__epic_rewards__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_collections
    ADD CONSTRAINT dinoparc_collections__epic_rewards__fk FOREIGN KEY (dinoparc_epic_reward_set_id) REFERENCES public.dinoparc_epic_reward_sets(dinoparc_epic_reward_set_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_collections dinoparc_collections__rewards__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_collections
    ADD CONSTRAINT dinoparc_collections__rewards__fk FOREIGN KEY (dinoparc_reward_set_id) REFERENCES public.dinoparc_reward_sets(dinoparc_reward_set_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_collections dinoparc_collections__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_collections
    ADD CONSTRAINT dinoparc_collections__user__fk FOREIGN KEY (dinoparc_server, dinoparc_user_id) REFERENCES public.dinoparc_users(dinoparc_server, dinoparc_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_dinoz dinoparc_dinoz__servers__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz
    ADD CONSTRAINT dinoparc_dinoz__servers__fk FOREIGN KEY (dinoparc_server) REFERENCES public.dinoparc_servers(dinoparc_server) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_dinoz_levels dinoparc_dinoz_levels__dinoz__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_levels
    ADD CONSTRAINT dinoparc_dinoz_levels__dinoz__fk FOREIGN KEY (dinoparc_server, dinoparc_dinoz_id) REFERENCES public.dinoparc_dinoz(dinoparc_server, dinoparc_dinoz_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_dinoz_locations dinoparc_dinoz_locations__dinoz__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_locations
    ADD CONSTRAINT dinoparc_dinoz_locations__dinoz__fk FOREIGN KEY (dinoparc_server, dinoparc_dinoz_id) REFERENCES public.dinoparc_dinoz(dinoparc_server, dinoparc_dinoz_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_dinoz_locations dinoparc_dinoz_locations__location__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_locations
    ADD CONSTRAINT dinoparc_dinoz_locations__location__fk FOREIGN KEY (location) REFERENCES public.dinoparc_locations(dinoparc_location_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_dinoz_names dinoparc_dinoz_names__dinoz__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_names
    ADD CONSTRAINT dinoparc_dinoz_names__dinoz__fk FOREIGN KEY (dinoparc_server, dinoparc_dinoz_id) REFERENCES public.dinoparc_dinoz(dinoparc_server, dinoparc_dinoz_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_dinoz_owners dinoparc_dinoz_names__dinoz__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_owners
    ADD CONSTRAINT dinoparc_dinoz_names__dinoz__fk FOREIGN KEY (dinoparc_server, dinoparc_dinoz_id) REFERENCES public.dinoparc_dinoz(dinoparc_server, dinoparc_dinoz_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_dinoz_owners dinoparc_dinoz_names__owner__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_owners
    ADD CONSTRAINT dinoparc_dinoz_names__owner__fk FOREIGN KEY (dinoparc_server, owner) REFERENCES public.dinoparc_users(dinoparc_server, dinoparc_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_dinoz_profiles dinoparc_dinoz_profiles__dinoz__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_profiles
    ADD CONSTRAINT dinoparc_dinoz_profiles__dinoz__fk FOREIGN KEY (dinoparc_server, dinoparc_dinoz_id) REFERENCES public.dinoparc_dinoz(dinoparc_server, dinoparc_dinoz_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_dinoz_skins dinoparc_dinoz_profiles__dinoz__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_skins
    ADD CONSTRAINT dinoparc_dinoz_profiles__dinoz__fk FOREIGN KEY (dinoparc_server, dinoparc_dinoz_id) REFERENCES public.dinoparc_dinoz(dinoparc_server, dinoparc_dinoz_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_dinoz_profiles dinoparc_dinoz_profiles__skills__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_dinoz_profiles
    ADD CONSTRAINT dinoparc_dinoz_profiles__skills__fk FOREIGN KEY (skills) REFERENCES public.dinoparc_skill_level_maps(dinoparc_skill_level_map_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_epic_reward_set_items dinoparc_epic_reward_sets__set__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_epic_reward_set_items
    ADD CONSTRAINT dinoparc_epic_reward_sets__set__fk FOREIGN KEY (dinoparc_epic_reward_set_id) REFERENCES public.dinoparc_epic_reward_sets(dinoparc_epic_reward_set_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_inventories dinoparc_inventories__item_counts__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_inventories
    ADD CONSTRAINT dinoparc_inventories__item_counts__fk FOREIGN KEY (item_counts) REFERENCES public.dinoparc_item_count_maps(dinoparc_item_count_map_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_inventories dinoparc_inventories__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_inventories
    ADD CONSTRAINT dinoparc_inventories__user__fk FOREIGN KEY (dinoparc_server, dinoparc_user_id) REFERENCES public.dinoparc_users(dinoparc_server, dinoparc_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_item_count_map_items dinoparc_item_count_map_item__map__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_item_count_map_items
    ADD CONSTRAINT dinoparc_item_count_map_item__map__fk FOREIGN KEY (dinoparc_item_count_map_id) REFERENCES public.dinoparc_item_count_maps(dinoparc_item_count_map_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_reward_set_items dinoparc_reward_set_items__set__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_reward_set_items
    ADD CONSTRAINT dinoparc_reward_set_items__set__fk FOREIGN KEY (dinoparc_reward_set_id) REFERENCES public.dinoparc_reward_sets(dinoparc_reward_set_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_sessions dinoparc_session__dinoparc_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_sessions
    ADD CONSTRAINT dinoparc_session__dinoparc_user__fk FOREIGN KEY (dinoparc_server, dinoparc_user_id) REFERENCES public.dinoparc_users(dinoparc_server, dinoparc_user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dinoparc_skill_level_map_items dinoparc_skill_level_map_item__map__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_skill_level_map_items
    ADD CONSTRAINT dinoparc_skill_level_map_item__map__fk FOREIGN KEY (dinoparc_skill_level_map_id) REFERENCES public.dinoparc_skill_level_maps(dinoparc_skill_level_map_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_users dinoparc_user__dinoparc_server__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_users
    ADD CONSTRAINT dinoparc_user__dinoparc_server__fk FOREIGN KEY (dinoparc_server) REFERENCES public.dinoparc_servers(dinoparc_server) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_user_dinoz dinoparc_user_dinoz__dinoz__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_user_dinoz
    ADD CONSTRAINT dinoparc_user_dinoz__dinoz__fk FOREIGN KEY (dinoparc_server, dinoparc_dinoz_id) REFERENCES public.dinoparc_dinoz(dinoparc_server, dinoparc_dinoz_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_user_dinoz dinoparc_user_dinoz__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_user_dinoz
    ADD CONSTRAINT dinoparc_user_dinoz__user__fk FOREIGN KEY (dinoparc_server, dinoparc_user_id) REFERENCES public.dinoparc_users(dinoparc_server, dinoparc_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_user_dinoz_counts dinoparc_user_dinoz_counts__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_user_dinoz_counts
    ADD CONSTRAINT dinoparc_user_dinoz_counts__user__fk FOREIGN KEY (dinoparc_server, dinoparc_user_id) REFERENCES public.dinoparc_users(dinoparc_server, dinoparc_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_user_links dinoparc_user_link__dinoparc_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_user_links
    ADD CONSTRAINT dinoparc_user_link__dinoparc_user__fk FOREIGN KEY (dinoparc_server, dinoparc_user_id) REFERENCES public.dinoparc_users(dinoparc_server, dinoparc_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_user_links dinoparc_user_link__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_user_links
    ADD CONSTRAINT dinoparc_user_link__user__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_user_links dinoparc_user_link_linked_by__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_user_links
    ADD CONSTRAINT dinoparc_user_link_linked_by__user__fk FOREIGN KEY (linked_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_user_links dinoparc_user_link_unlinked_by__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.dinoparc_user_links
    ADD CONSTRAINT dinoparc_user_link_unlinked_by__user__fk FOREIGN KEY (unlinked_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: email_verifications email_verification__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.email_verifications
    ADD CONSTRAINT email_verification__user__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: forum_role_grants forum_moderator__forum_section__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_role_grants
    ADD CONSTRAINT forum_moderator__forum_section__fk FOREIGN KEY (forum_section_id) REFERENCES public.forum_sections(forum_section_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: forum_role_grants forum_moderator__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_role_grants
    ADD CONSTRAINT forum_moderator__user__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: forum_role_grants forum_moderator_granter__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_role_grants
    ADD CONSTRAINT forum_moderator_granter__user__fk FOREIGN KEY (granted_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: forum_role_revocations forum_moderator_granter__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_role_revocations
    ADD CONSTRAINT forum_moderator_granter__user__fk FOREIGN KEY (granted_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: forum_role_revocations forum_moderator_revoker__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_role_revocations
    ADD CONSTRAINT forum_moderator_revoker__user__fk FOREIGN KEY (revoked_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: forum_posts forum_post__forum_thread__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_posts
    ADD CONSTRAINT forum_post__forum_thread__fk FOREIGN KEY (forum_thread_id) REFERENCES public.forum_threads(forum_thread_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: forum_post_revisions forum_post_revision__forum_revision__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_post_revisions
    ADD CONSTRAINT forum_post_revision__forum_revision__fk FOREIGN KEY (forum_post_id) REFERENCES public.forum_posts(forum_post_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: _post_formatting_costs forum_post_revision__forum_revision__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public._post_formatting_costs
    ADD CONSTRAINT forum_post_revision__forum_revision__fk FOREIGN KEY (forum_post_revision_id) REFERENCES public.forum_post_revisions(forum_post_revision_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: forum_post_revisions forum_post_revision__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_post_revisions
    ADD CONSTRAINT forum_post_revision__user__fk FOREIGN KEY (author_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: forum_role_revocations forum_role_revocation__forum_section__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_role_revocations
    ADD CONSTRAINT forum_role_revocation__forum_section__fk FOREIGN KEY (forum_section_id) REFERENCES public.forum_sections(forum_section_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: forum_role_revocations forum_role_revocation_user__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_role_revocations
    ADD CONSTRAINT forum_role_revocation_user__user__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: forum_threads forum_thread__forum_section__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.forum_threads
    ADD CONSTRAINT forum_thread__forum_section__fk FOREIGN KEY (forum_section_id) REFERENCES public.forum_sections(forum_section_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: hammerfest_best_season_ranks hammerfest_best_season_rank__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_best_season_ranks
    ADD CONSTRAINT hammerfest_best_season_rank__user__fk FOREIGN KEY (hammerfest_server, hammerfest_user_id) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_emails hammerfest_email__email__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_emails
    ADD CONSTRAINT hammerfest_email__email__fk FOREIGN KEY (email) REFERENCES public.email_addresses(_hash) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_emails hammerfest_email__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_emails
    ADD CONSTRAINT hammerfest_email__user__fk FOREIGN KEY (hammerfest_server, hammerfest_user_id) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_forum_post_ids hammerfest_forum_post_ids__thread__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_post_ids
    ADD CONSTRAINT hammerfest_forum_post_ids__thread__fk FOREIGN KEY (hammerfest_server, hammerfest_thread_id) REFERENCES public.hammerfest_forum_threads(hammerfest_server, hammerfest_thread_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_forum_posts hammerfest_forum_posts__author__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_posts
    ADD CONSTRAINT hammerfest_forum_posts__author__fk FOREIGN KEY (hammerfest_server, author) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_forum_posts hammerfest_forum_posts__thread__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_posts
    ADD CONSTRAINT hammerfest_forum_posts__thread__fk FOREIGN KEY (hammerfest_server, hammerfest_thread_id) REFERENCES public.hammerfest_forum_threads(hammerfest_server, hammerfest_thread_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_forum_roles hammerfest_forum_roles__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_roles
    ADD CONSTRAINT hammerfest_forum_roles__user__fk FOREIGN KEY (hammerfest_server, hammerfest_user_id) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_forum_theme_counts hammerfest_forum_theme_counts__theme__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_theme_counts
    ADD CONSTRAINT hammerfest_forum_theme_counts__theme__fk FOREIGN KEY (hammerfest_server, hammerfest_theme_id) REFERENCES public.hammerfest_forum_themes(hammerfest_server, hammerfest_theme_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_forum_theme_page_counts hammerfest_forum_theme_page_counts__themes__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_theme_page_counts
    ADD CONSTRAINT hammerfest_forum_theme_page_counts__themes__fk FOREIGN KEY (hammerfest_server, hammerfest_theme_id) REFERENCES public.hammerfest_forum_themes(hammerfest_server, hammerfest_theme_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_forum_theme_threads hammerfest_forum_theme_threads__theme__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_theme_threads
    ADD CONSTRAINT hammerfest_forum_theme_threads__theme__fk FOREIGN KEY (hammerfest_server, hammerfest_theme_id) REFERENCES public.hammerfest_forum_themes(hammerfest_server, hammerfest_theme_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_forum_theme_threads hammerfest_forum_theme_threads__thread__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_theme_threads
    ADD CONSTRAINT hammerfest_forum_theme_threads__thread__fk FOREIGN KEY (hammerfest_server, hammerfest_thread_id) REFERENCES public.hammerfest_forum_threads(hammerfest_server, hammerfest_thread_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_forum_themes hammerfest_forum_themes__servers__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_themes
    ADD CONSTRAINT hammerfest_forum_themes__servers__fk FOREIGN KEY (hammerfest_server) REFERENCES public.hammerfest_servers(hammerfest_server) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_forum_thread_page_counts hammerfest_forum_thread_page_counts__thread__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_thread_page_counts
    ADD CONSTRAINT hammerfest_forum_thread_page_counts__thread__fk FOREIGN KEY (hammerfest_server, hammerfest_thread_id) REFERENCES public.hammerfest_forum_threads(hammerfest_server, hammerfest_thread_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_forum_thread_shared_meta hammerfest_forum_thread_shared_meta__theme__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_thread_shared_meta
    ADD CONSTRAINT hammerfest_forum_thread_shared_meta__theme__fk FOREIGN KEY (hammerfest_server, hammerfest_theme_id) REFERENCES public.hammerfest_forum_themes(hammerfest_server, hammerfest_theme_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_forum_thread_shared_meta hammerfest_forum_thread_shared_meta__thread__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_thread_shared_meta
    ADD CONSTRAINT hammerfest_forum_thread_shared_meta__thread__fk FOREIGN KEY (hammerfest_server, hammerfest_thread_id) REFERENCES public.hammerfest_forum_threads(hammerfest_server, hammerfest_thread_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_forum_thread_theme_meta hammerfest_forum_thread_theme_meta__thread__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_thread_theme_meta
    ADD CONSTRAINT hammerfest_forum_thread_theme_meta__thread__fk FOREIGN KEY (hammerfest_server, hammerfest_thread_id) REFERENCES public.hammerfest_forum_threads(hammerfest_server, hammerfest_thread_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_forum_threads hammerfest_forum_threads__servers__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_forum_threads
    ADD CONSTRAINT hammerfest_forum_threads__servers__fk FOREIGN KEY (hammerfest_server) REFERENCES public.hammerfest_servers(hammerfest_server) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_godchild_lists hammerfest_godchild_lists__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_godchild_lists
    ADD CONSTRAINT hammerfest_godchild_lists__user__fk FOREIGN KEY (hammerfest_server, hammerfest_user_id) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_godchildren hammerfest_godchildren__child__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_godchildren
    ADD CONSTRAINT hammerfest_godchildren__child__fk FOREIGN KEY (hammerfest_server, godchild_id) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_godchildren hammerfest_godchildren__father__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_godchildren
    ADD CONSTRAINT hammerfest_godchildren__father__fk FOREIGN KEY (hammerfest_server, hammerfest_user_id) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_inventories hammerfest_inventory__item_counts__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_inventories
    ADD CONSTRAINT hammerfest_inventory__item_counts__fk FOREIGN KEY (item_counts) REFERENCES public.hammerfest_item_count_maps(hammerfest_item_count_map_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_inventories hammerfest_inventory__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_inventories
    ADD CONSTRAINT hammerfest_inventory__user__fk FOREIGN KEY (hammerfest_server, hammerfest_user_id) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_item_count_map_items hammerfest_item_count_map_item__item__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_item_count_map_items
    ADD CONSTRAINT hammerfest_item_count_map_item__item__fk FOREIGN KEY (hammerfest_item_id) REFERENCES public.hammerfest_items(hammerfest_item_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_item_count_map_items hammerfest_item_count_map_item__map__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_item_count_map_items
    ADD CONSTRAINT hammerfest_item_count_map_item__map__fk FOREIGN KEY (hammerfest_item_count_map_id) REFERENCES public.hammerfest_item_count_maps(hammerfest_item_count_map_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_profiles hammerfest_profiles__quest_statuses__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_profiles
    ADD CONSTRAINT hammerfest_profiles__quest_statuses__fk FOREIGN KEY (quest_statuses) REFERENCES public.hammerfest_quest_status_maps(hammerfest_quest_status_map_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_profiles hammerfest_profiles__unlocked_items__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_profiles
    ADD CONSTRAINT hammerfest_profiles__unlocked_items__fk FOREIGN KEY (unlocked_items) REFERENCES public.hammerfest_unlocked_item_sets(hammerfest_unlocked_item_set_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_profiles hammerfest_profiles__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_profiles
    ADD CONSTRAINT hammerfest_profiles__user__fk FOREIGN KEY (hammerfest_server, hammerfest_user_id) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_quest_status_map_items hammerfest_quest_status_map_item__map__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_quest_status_map_items
    ADD CONSTRAINT hammerfest_quest_status_map_item__map__fk FOREIGN KEY (hammerfest_quest_status_map_id) REFERENCES public.hammerfest_quest_status_maps(hammerfest_quest_status_map_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_quest_status_map_items hammerfest_quest_status_map_item__quest__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_quest_status_map_items
    ADD CONSTRAINT hammerfest_quest_status_map_item__quest__fk FOREIGN KEY (hammerfest_quest_id) REFERENCES public.hammerfest_quests(hammerfest_quest_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_sessions hammerfest_session__hammerfest_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_sessions
    ADD CONSTRAINT hammerfest_session__hammerfest_user__fk FOREIGN KEY (hammerfest_server, hammerfest_user_id) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: hammerfest_shops hammerfest_shops__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_shops
    ADD CONSTRAINT hammerfest_shops__user__fk FOREIGN KEY (hammerfest_server, hammerfest_user_id) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_tokens hammerfest_tokens__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_tokens
    ADD CONSTRAINT hammerfest_tokens__user__fk FOREIGN KEY (hammerfest_server, hammerfest_user_id) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_unlocked_item_set_items hammerfest_unlocked_item_set_item__item__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_unlocked_item_set_items
    ADD CONSTRAINT hammerfest_unlocked_item_set_item__item__fk FOREIGN KEY (hammerfest_item_id) REFERENCES public.hammerfest_items(hammerfest_item_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_unlocked_item_set_items hammerfest_unlocked_item_set_item__map__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_unlocked_item_set_items
    ADD CONSTRAINT hammerfest_unlocked_item_set_item__map__fk FOREIGN KEY (hammerfest_unlocked_item_set_id) REFERENCES public.hammerfest_unlocked_item_sets(hammerfest_unlocked_item_set_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_users hammerfest_user__hammerfest_server__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_users
    ADD CONSTRAINT hammerfest_user__hammerfest_server__fk FOREIGN KEY (hammerfest_server) REFERENCES public.hammerfest_servers(hammerfest_server) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_user_achievements hammerfest_user_achievements__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_user_achievements
    ADD CONSTRAINT hammerfest_user_achievements__user__fk FOREIGN KEY (hammerfest_server, hammerfest_user_id) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_user_links hammerfest_user_link__hammerfest_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_user_links
    ADD CONSTRAINT hammerfest_user_link__hammerfest_user__fk FOREIGN KEY (hammerfest_server, hammerfest_user_id) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_user_links hammerfest_user_link__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_user_links
    ADD CONSTRAINT hammerfest_user_link__user__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_user_links hammerfest_user_link_linked_by__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_user_links
    ADD CONSTRAINT hammerfest_user_link_linked_by__user__fk FOREIGN KEY (linked_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_user_links hammerfest_user_link_unlinked_by__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.hammerfest_user_links
    ADD CONSTRAINT hammerfest_user_link_unlinked_by__user__fk FOREIGN KEY (unlinked_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: oauth_access_tokens oauth_access_token__oauth_client__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.oauth_access_tokens
    ADD CONSTRAINT oauth_access_token__oauth_client__fk FOREIGN KEY (oauth_client_id) REFERENCES public.oauth_clients(oauth_client_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: oauth_access_tokens oauth_access_token__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.oauth_access_tokens
    ADD CONSTRAINT oauth_access_token__user__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: old_dinoparc_sessions old_dinoparc_session__dinoparc_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.old_dinoparc_sessions
    ADD CONSTRAINT old_dinoparc_session__dinoparc_user__fk FOREIGN KEY (dinoparc_server, dinoparc_user_id) REFERENCES public.dinoparc_users(dinoparc_server, dinoparc_user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: old_hammerfest_sessions old_hammerfest_session__hammerfest_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.old_hammerfest_sessions
    ADD CONSTRAINT old_hammerfest_session__hammerfest_user__fk FOREIGN KEY (hammerfest_server, hammerfest_user_id) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: old_oauth_client_app_uris old_oauth_client_app_uri__oauth_client__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.old_oauth_client_app_uris
    ADD CONSTRAINT old_oauth_client_app_uri__oauth_client__fk FOREIGN KEY (oauth_client_id) REFERENCES public.oauth_clients(oauth_client_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: old_oauth_client_callback_uris old_oauth_client_callback_uri__oauth_client__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.old_oauth_client_callback_uris
    ADD CONSTRAINT old_oauth_client_callback_uri__oauth_client__fk FOREIGN KEY (oauth_client_id) REFERENCES public.oauth_clients(oauth_client_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: old_oauth_client_display_names old_oauth_client_display_name__oauth_client__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.old_oauth_client_display_names
    ADD CONSTRAINT old_oauth_client_display_name__oauth_client__fk FOREIGN KEY (oauth_client_id) REFERENCES public.oauth_clients(oauth_client_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: old_oauth_client_secrets old_oauth_client_secret__oauth_client__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.old_oauth_client_secrets
    ADD CONSTRAINT old_oauth_client_secret__oauth_client__fk FOREIGN KEY (oauth_client_id) REFERENCES public.oauth_clients(oauth_client_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: old_twinoid_refresh_tokens old_twinoid_refresh_token__twinoid_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.old_twinoid_refresh_tokens
    ADD CONSTRAINT old_twinoid_refresh_token__twinoid_user__fk FOREIGN KEY (twinoid_user_id) REFERENCES public.twinoid_users(twinoid_user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: sessions session__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT session__user__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: twinoid_access_tokens twinoid_access_token__twinoid_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.twinoid_access_tokens
    ADD CONSTRAINT twinoid_access_token__twinoid_user__fk FOREIGN KEY (twinoid_user_id) REFERENCES public.twinoid_users(twinoid_user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: old_twinoid_access_tokens twinoid_access_token__twinoid_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.old_twinoid_access_tokens
    ADD CONSTRAINT twinoid_access_token__twinoid_user__fk FOREIGN KEY (twinoid_user_id) REFERENCES public.twinoid_users(twinoid_user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: twinoid_refresh_tokens twinoid_refresh_token__twinoid_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.twinoid_refresh_tokens
    ADD CONSTRAINT twinoid_refresh_token__twinoid_user__fk FOREIGN KEY (twinoid_user_id) REFERENCES public.twinoid_users(twinoid_user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: twinoid_user_links twinoid_user_link__twinoid_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.twinoid_user_links
    ADD CONSTRAINT twinoid_user_link__twinoid_user__fk FOREIGN KEY (twinoid_user_id) REFERENCES public.twinoid_users(twinoid_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: twinoid_user_links twinoid_user_link__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.twinoid_user_links
    ADD CONSTRAINT twinoid_user_link__user__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: twinoid_user_links twinoid_user_link_linked_by__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.twinoid_user_links
    ADD CONSTRAINT twinoid_user_link_linked_by__user__fk FOREIGN KEY (linked_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: twinoid_user_links twinoid_user_link_unlinked_by__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.twinoid_user_links
    ADD CONSTRAINT twinoid_user_link_unlinked_by__user__fk FOREIGN KEY (unlinked_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: users_history user_history_email__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.users_history
    ADD CONSTRAINT user_history_email__fk FOREIGN KEY (email) REFERENCES public.email_addresses(_hash) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: users_history user_history_updated_by__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.users_history
    ADD CONSTRAINT user_history_updated_by__fk FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: users_history user_history_user_id__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.users_history
    ADD CONSTRAINT user_history_user_id__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: users users_history__fk; Type: FK CONSTRAINT; Schema: public; Owner: etwin.dev.admin
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_history__fk FOREIGN KEY (user_id, _is_current) REFERENCES public.users_history(user_id, _is_current) ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- PostgreSQL database dump complete
--

