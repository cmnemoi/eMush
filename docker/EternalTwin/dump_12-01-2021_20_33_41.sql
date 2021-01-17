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
-- Name: pgcrypto; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;


--
-- Name: EXTENSION pgcrypto; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION pgcrypto IS 'cryptographic functions';


--
-- Name: announcement_id; Type: DOMAIN; Schema: public; Owner: mysql
--

CREATE DOMAIN public.announcement_id AS uuid;


ALTER DOMAIN public.announcement_id OWNER TO mysql;

--
-- Name: dinoparc_server; Type: DOMAIN; Schema: public; Owner: mysql
--

CREATE DOMAIN public.dinoparc_server AS character varying(15)
	CONSTRAINT dinoparc_server_check CHECK (((VALUE)::text = ANY ((ARRAY['dinoparc.com'::character varying, 'en.dinoparc.com'::character varying, 'sp.dinoparc.com'::character varying])::text[])));


ALTER DOMAIN public.dinoparc_server OWNER TO mysql;

--
-- Name: dinoparc_session_key; Type: DOMAIN; Schema: public; Owner: mysql
--

CREATE DOMAIN public.dinoparc_session_key AS character varying(32)
	CONSTRAINT dinoparc_session_key_check CHECK (((VALUE)::text ~ '^[0-9a-zA-Z]{32}$'::text));


ALTER DOMAIN public.dinoparc_session_key OWNER TO mysql;

--
-- Name: dinoparc_user_id; Type: DOMAIN; Schema: public; Owner: mysql
--

CREATE DOMAIN public.dinoparc_user_id AS character varying(10)
	CONSTRAINT dinoparc_user_id_check CHECK (((VALUE)::text ~ '^[1-9]\d{0,9}$'::text));


ALTER DOMAIN public.dinoparc_user_id OWNER TO mysql;

--
-- Name: dinoparc_username; Type: DOMAIN; Schema: public; Owner: mysql
--

CREATE DOMAIN public.dinoparc_username AS character varying(20);


ALTER DOMAIN public.dinoparc_username OWNER TO mysql;

--
-- Name: forum_thread_id; Type: DOMAIN; Schema: public; Owner: mysql
--

CREATE DOMAIN public.forum_thread_id AS uuid;


ALTER DOMAIN public.forum_thread_id OWNER TO mysql;

--
-- Name: hammerfest_server; Type: DOMAIN; Schema: public; Owner: mysql
--

CREATE DOMAIN public.hammerfest_server AS character varying(13)
	CONSTRAINT hammerfest_server_check CHECK (((VALUE)::text = ANY ((ARRAY['hammerfest.es'::character varying, 'hammerfest.fr'::character varying, 'hfest.net'::character varying])::text[])));


ALTER DOMAIN public.hammerfest_server OWNER TO mysql;

--
-- Name: hammerfest_session_key; Type: DOMAIN; Schema: public; Owner: mysql
--

CREATE DOMAIN public.hammerfest_session_key AS character varying(26)
	CONSTRAINT hammerfest_session_key_check CHECK (((VALUE)::text ~ '^[0-9a-z]{26}$'::text));


ALTER DOMAIN public.hammerfest_session_key OWNER TO mysql;

--
-- Name: hammerfest_user_id; Type: DOMAIN; Schema: public; Owner: mysql
--

CREATE DOMAIN public.hammerfest_user_id AS character varying(10)
	CONSTRAINT hammerfest_user_id_check CHECK (((VALUE)::text ~ '^[1-9]\d{0,9}$'::text));


ALTER DOMAIN public.hammerfest_user_id OWNER TO mysql;

--
-- Name: hammerfest_username; Type: DOMAIN; Schema: public; Owner: mysql
--

CREATE DOMAIN public.hammerfest_username AS character varying(20)
	CONSTRAINT hammerfest_username_check CHECK (((VALUE)::text ~ '^[0-9A-Za-z]{1,12}$'::text));


ALTER DOMAIN public.hammerfest_username OWNER TO mysql;

--
-- Name: instant; Type: DOMAIN; Schema: public; Owner: mysql
--

CREATE DOMAIN public.instant AS timestamp(3) with time zone;


ALTER DOMAIN public.instant OWNER TO mysql;

--
-- Name: locale_id; Type: DOMAIN; Schema: public; Owner: mysql
--

CREATE DOMAIN public.locale_id AS character varying(10);


ALTER DOMAIN public.locale_id OWNER TO mysql;

--
-- Name: twinoid_user_id; Type: DOMAIN; Schema: public; Owner: mysql
--

CREATE DOMAIN public.twinoid_user_id AS character varying(10)
	CONSTRAINT twinoid_user_id_check CHECK (((VALUE)::text ~ '^[1-9]\d{0,9}$'::text));


ALTER DOMAIN public.twinoid_user_id OWNER TO mysql;

--
-- Name: user_id; Type: DOMAIN; Schema: public; Owner: mysql
--

CREATE DOMAIN public.user_id AS uuid;


ALTER DOMAIN public.user_id OWNER TO mysql;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: _post_formatting_costs; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public._post_formatting_costs (
    forum_post_revision_id uuid NOT NULL,
    formatting character varying(20) NOT NULL,
    cost integer,
    CONSTRAINT _post_formatting_costs_cost_check CHECK ((cost > 0))
);


ALTER TABLE public._post_formatting_costs OWNER TO mysql;

--
-- Name: announcements; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.announcements (
    announcement_id public.announcement_id NOT NULL,
    forum_thread_id public.forum_thread_id NOT NULL,
    locale public.locale_id,
    created_at public.instant NOT NULL,
    created_by public.user_id NOT NULL
);


ALTER TABLE public.announcements OWNER TO mysql;

--
-- Name: dinoparc_servers; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.dinoparc_servers (
    dinoparc_server public.dinoparc_server NOT NULL
);


ALTER TABLE public.dinoparc_servers OWNER TO mysql;

--
-- Name: dinoparc_sessions; Type: TABLE; Schema: public; Owner: mysql
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


ALTER TABLE public.dinoparc_sessions OWNER TO mysql;

--
-- Name: dinoparc_user_links; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.dinoparc_user_links (
    user_id public.user_id NOT NULL,
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_user_id public.dinoparc_user_id NOT NULL,
    linked_at public.instant NOT NULL,
    linked_by public.user_id NOT NULL
);


ALTER TABLE public.dinoparc_user_links OWNER TO mysql;

--
-- Name: dinoparc_users; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.dinoparc_users (
    dinoparc_server public.dinoparc_server NOT NULL,
    dinoparc_user_id public.dinoparc_user_id NOT NULL,
    username public.dinoparc_username NOT NULL,
    archived_at public.instant NOT NULL
);


ALTER TABLE public.dinoparc_users OWNER TO mysql;

--
-- Name: email_verifications; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.email_verifications (
    user_id uuid NOT NULL,
    email_address bytea NOT NULL,
    ctime public.instant NOT NULL,
    validation_time public.instant,
    CONSTRAINT email_verifications_check CHECK (((validation_time)::timestamp with time zone >= (ctime)::timestamp with time zone))
);


ALTER TABLE public.email_verifications OWNER TO mysql;

--
-- Name: forum_post_revisions; Type: TABLE; Schema: public; Owner: mysql
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


ALTER TABLE public.forum_post_revisions OWNER TO mysql;

--
-- Name: forum_posts; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.forum_posts (
    forum_post_id uuid NOT NULL,
    ctime public.instant,
    forum_thread_id uuid NOT NULL
);


ALTER TABLE public.forum_posts OWNER TO mysql;

--
-- Name: forum_role_grants; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.forum_role_grants (
    forum_section_id uuid NOT NULL,
    user_id uuid NOT NULL,
    start_time public.instant,
    granted_by uuid NOT NULL
);


ALTER TABLE public.forum_role_grants OWNER TO mysql;

--
-- Name: forum_role_revocations; Type: TABLE; Schema: public; Owner: mysql
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


ALTER TABLE public.forum_role_revocations OWNER TO mysql;

--
-- Name: forum_sections; Type: TABLE; Schema: public; Owner: mysql
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


ALTER TABLE public.forum_sections OWNER TO mysql;

--
-- Name: forum_threads; Type: TABLE; Schema: public; Owner: mysql
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


ALTER TABLE public.forum_threads OWNER TO mysql;

--
-- Name: hammerfest_servers; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.hammerfest_servers (
    hammerfest_server public.hammerfest_server NOT NULL,
    CONSTRAINT hammerfest_servers_domain_check CHECK (((hammerfest_server)::text = ANY (ARRAY[('hammerfest.fr'::character varying)::text, ('hfest.net'::character varying)::text, ('hammerfest.es'::character varying)::text])))
);


ALTER TABLE public.hammerfest_servers OWNER TO mysql;

--
-- Name: hammerfest_sessions; Type: TABLE; Schema: public; Owner: mysql
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


ALTER TABLE public.hammerfest_sessions OWNER TO mysql;

--
-- Name: hammerfest_user_links; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.hammerfest_user_links (
    user_id public.user_id NOT NULL,
    hammerfest_server public.hammerfest_server NOT NULL,
    linked_at public.instant NOT NULL,
    hammerfest_user_id public.hammerfest_user_id NOT NULL,
    linked_by public.user_id NOT NULL
);


ALTER TABLE public.hammerfest_user_links OWNER TO mysql;

--
-- Name: hammerfest_users; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.hammerfest_users (
    hammerfest_server public.hammerfest_server NOT NULL,
    username character varying(20) NOT NULL,
    hammerfest_user_id public.hammerfest_user_id NOT NULL,
    archived_at public.instant NOT NULL
);


ALTER TABLE public.hammerfest_users OWNER TO mysql;

--
-- Name: oauth_access_tokens; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.oauth_access_tokens (
    oauth_access_token_id uuid NOT NULL,
    oauth_client_id uuid NOT NULL,
    user_id uuid NOT NULL,
    ctime public.instant NOT NULL,
    atime public.instant NOT NULL,
    CONSTRAINT oauth_access_tokens_check CHECK (((atime)::timestamp with time zone >= (ctime)::timestamp with time zone))
);


ALTER TABLE public.oauth_access_tokens OWNER TO mysql;

--
-- Name: oauth_clients; Type: TABLE; Schema: public; Owner: mysql
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


ALTER TABLE public.oauth_clients OWNER TO mysql;

--
-- Name: old_dinoparc_sessions; Type: TABLE; Schema: public; Owner: mysql
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


ALTER TABLE public.old_dinoparc_sessions OWNER TO mysql;

--
-- Name: old_hammerfest_sessions; Type: TABLE; Schema: public; Owner: mysql
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


ALTER TABLE public.old_hammerfest_sessions OWNER TO mysql;

--
-- Name: old_oauth_client_app_uris; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.old_oauth_client_app_uris (
    oauth_client_id uuid NOT NULL,
    start_time public.instant NOT NULL,
    app_uri character varying(512) NOT NULL
);


ALTER TABLE public.old_oauth_client_app_uris OWNER TO mysql;

--
-- Name: old_oauth_client_callback_uris; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.old_oauth_client_callback_uris (
    oauth_client_id uuid NOT NULL,
    start_time public.instant NOT NULL,
    callback_uri character varying(512) NOT NULL
);


ALTER TABLE public.old_oauth_client_callback_uris OWNER TO mysql;

--
-- Name: old_oauth_client_display_names; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.old_oauth_client_display_names (
    oauth_client_id uuid NOT NULL,
    start_time public.instant NOT NULL,
    display_name character varying(64) NOT NULL
);


ALTER TABLE public.old_oauth_client_display_names OWNER TO mysql;

--
-- Name: old_oauth_client_secrets; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.old_oauth_client_secrets (
    oauth_client_id uuid NOT NULL,
    start_time public.instant NOT NULL,
    secret bytea NOT NULL
);


ALTER TABLE public.old_oauth_client_secrets OWNER TO mysql;

--
-- Name: old_twinoid_access_tokens; Type: TABLE; Schema: public; Owner: mysql
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


ALTER TABLE public.old_twinoid_access_tokens OWNER TO mysql;

--
-- Name: old_twinoid_refresh_tokens; Type: TABLE; Schema: public; Owner: mysql
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


ALTER TABLE public.old_twinoid_refresh_tokens OWNER TO mysql;

--
-- Name: old_twinoid_user_links; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.old_twinoid_user_links (
    user_id uuid NOT NULL,
    twinoid_user_id public.twinoid_user_id NOT NULL,
    start_time public.instant,
    end_time public.instant
);


ALTER TABLE public.old_twinoid_user_links OWNER TO mysql;

--
-- Name: sessions; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.sessions (
    session_id uuid NOT NULL,
    user_id uuid,
    ctime public.instant NOT NULL,
    atime public.instant NOT NULL,
    data json NOT NULL,
    CONSTRAINT sessions_check CHECK (((atime)::timestamp with time zone >= (ctime)::timestamp with time zone))
);


ALTER TABLE public.sessions OWNER TO mysql;

--
-- Name: twinoid_access_tokens; Type: TABLE; Schema: public; Owner: mysql
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


ALTER TABLE public.twinoid_access_tokens OWNER TO mysql;

--
-- Name: twinoid_refresh_tokens; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.twinoid_refresh_tokens (
    twinoid_refresh_token bytea NOT NULL,
    _twinoid_refresh_token_hash bytea NOT NULL,
    twinoid_user_id public.twinoid_user_id NOT NULL,
    ctime public.instant NOT NULL,
    atime public.instant NOT NULL,
    CONSTRAINT twinoid_refresh_tokens_check CHECK (((ctime)::timestamp with time zone <= (atime)::timestamp with time zone))
);


ALTER TABLE public.twinoid_refresh_tokens OWNER TO mysql;

--
-- Name: twinoid_user_links; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.twinoid_user_links (
    user_id public.user_id NOT NULL,
    twinoid_user_id public.twinoid_user_id NOT NULL,
    linked_at public.instant NOT NULL,
    linked_by public.user_id NOT NULL
);


ALTER TABLE public.twinoid_user_links OWNER TO mysql;

--
-- Name: twinoid_users; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.twinoid_users (
    twinoid_user_id public.twinoid_user_id NOT NULL,
    name character varying(50) NOT NULL,
    archived_at public.instant NOT NULL
);


ALTER TABLE public.twinoid_users OWNER TO mysql;

--
-- Name: users; Type: TABLE; Schema: public; Owner: mysql
--

CREATE TABLE public.users (
    user_id uuid NOT NULL,
    ctime public.instant,
    display_name character varying(64) NOT NULL,
    display_name_mtime public.instant NOT NULL,
    email_address bytea,
    email_address_mtime public.instant NOT NULL,
    username character varying(64),
    username_mtime public.instant NOT NULL,
    password bytea,
    password_mtime public.instant NOT NULL,
    is_administrator boolean NOT NULL,
    CONSTRAINT users_check CHECK (((display_name_mtime)::timestamp with time zone >= (ctime)::timestamp with time zone)),
    CONSTRAINT users_check1 CHECK (((email_address_mtime)::timestamp with time zone >= (ctime)::timestamp with time zone)),
    CONSTRAINT users_check2 CHECK (((username_mtime)::timestamp with time zone >= (ctime)::timestamp with time zone)),
    CONSTRAINT users_check3 CHECK (((password_mtime)::timestamp with time zone >= (ctime)::timestamp with time zone))
);


ALTER TABLE public.users OWNER TO mysql;

--
-- Data for Name: _post_formatting_costs; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public._post_formatting_costs (forum_post_revision_id, formatting, cost) FROM stdin;
\.


--
-- Data for Name: announcements; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.announcements (announcement_id, forum_thread_id, locale, created_at, created_by) FROM stdin;
\.


--
-- Data for Name: dinoparc_servers; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.dinoparc_servers (dinoparc_server) FROM stdin;
dinoparc.com
en.dinoparc.com
sp.dinoparc.com
\.


--
-- Data for Name: dinoparc_sessions; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.dinoparc_sessions (dinoparc_server, dinoparc_session_key, _dinoparc_session_key_hash, dinoparc_user_id, ctime, atime) FROM stdin;
\.


--
-- Data for Name: dinoparc_user_links; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.dinoparc_user_links (user_id, dinoparc_server, dinoparc_user_id, linked_at, linked_by) FROM stdin;
\.


--
-- Data for Name: dinoparc_users; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.dinoparc_users (dinoparc_server, dinoparc_user_id, username, archived_at) FROM stdin;
\.


--
-- Data for Name: email_verifications; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.email_verifications (user_id, email_address, ctime, validation_time) FROM stdin;
\.


--
-- Data for Name: forum_post_revisions; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.forum_post_revisions (forum_post_revision_id, "time", body, _html_body, mod_body, _html_mod_body, forum_post_id, author_id, comment) FROM stdin;
\.


--
-- Data for Name: forum_posts; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.forum_posts (forum_post_id, ctime, forum_thread_id) FROM stdin;
\.


--
-- Data for Name: forum_role_grants; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.forum_role_grants (forum_section_id, user_id, start_time, granted_by) FROM stdin;
\.


--
-- Data for Name: forum_role_revocations; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.forum_role_revocations (forum_section_id, user_id, start_time, end_time, granted_by, revoked_by) FROM stdin;
\.


--
-- Data for Name: forum_sections; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.forum_sections (forum_section_id, key, ctime, display_name, display_name_mtime, locale, locale_mtime) FROM stdin;
\.


--
-- Data for Name: forum_threads; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.forum_threads (forum_thread_id, key, ctime, title, title_mtime, forum_section_id, is_pinned, is_pinned_mtime, is_locked, is_locked_mtime) FROM stdin;
\.


--
-- Data for Name: hammerfest_servers; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.hammerfest_servers (hammerfest_server) FROM stdin;
hammerfest.fr
hfest.net
hammerfest.es
\.


--
-- Data for Name: hammerfest_sessions; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.hammerfest_sessions (hammerfest_server, hammerfest_session_key, _hammerfest_session_key_hash, hammerfest_user_id, ctime, atime) FROM stdin;
\.


--
-- Data for Name: hammerfest_user_links; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.hammerfest_user_links (user_id, hammerfest_server, linked_at, hammerfest_user_id, linked_by) FROM stdin;
\.


--
-- Data for Name: hammerfest_users; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.hammerfest_users (hammerfest_server, username, hammerfest_user_id, archived_at) FROM stdin;
\.


--
-- Data for Name: oauth_access_tokens; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.oauth_access_tokens (oauth_access_token_id, oauth_client_id, user_id, ctime, atime) FROM stdin;
\.


--
-- Data for Name: oauth_clients; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.oauth_clients (oauth_client_id, key, ctime, display_name, display_name_mtime, app_uri, app_uri_mtime, callback_uri, callback_uri_mtime, secret, secret_mtime, owner_id) FROM stdin;
\.


--
-- Data for Name: old_dinoparc_sessions; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.old_dinoparc_sessions (dinoparc_server, dinoparc_session_key, _dinoparc_session_key_hash, dinoparc_user_id, ctime, atime, dtime) FROM stdin;
\.


--
-- Data for Name: old_hammerfest_sessions; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.old_hammerfest_sessions (hammerfest_server, hammerfest_session_key, _hammerfest_session_key_hash, hammerfest_user_id, ctime, atime, dtime) FROM stdin;
\.


--
-- Data for Name: old_oauth_client_app_uris; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.old_oauth_client_app_uris (oauth_client_id, start_time, app_uri) FROM stdin;
\.


--
-- Data for Name: old_oauth_client_callback_uris; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.old_oauth_client_callback_uris (oauth_client_id, start_time, callback_uri) FROM stdin;
\.


--
-- Data for Name: old_oauth_client_display_names; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.old_oauth_client_display_names (oauth_client_id, start_time, display_name) FROM stdin;
\.


--
-- Data for Name: old_oauth_client_secrets; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.old_oauth_client_secrets (oauth_client_id, start_time, secret) FROM stdin;
\.


--
-- Data for Name: old_twinoid_access_tokens; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.old_twinoid_access_tokens (twinoid_access_token, _twinoid_access_token_hash, twinoid_user_id, ctime, atime, dtime, expiration_time) FROM stdin;
\.


--
-- Data for Name: old_twinoid_refresh_tokens; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.old_twinoid_refresh_tokens (twinoid_refresh_token, _twinoid_refresh_token_hash, twinoid_user_id, ctime, atime, dtime) FROM stdin;
\.


--
-- Data for Name: old_twinoid_user_links; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.old_twinoid_user_links (user_id, twinoid_user_id, start_time, end_time) FROM stdin;
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.sessions (session_id, user_id, ctime, atime, data) FROM stdin;
\.


--
-- Data for Name: twinoid_access_tokens; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.twinoid_access_tokens (twinoid_access_token, _twinoid_access_token_hash, twinoid_user_id, ctime, atime, expiration_time) FROM stdin;
\.


--
-- Data for Name: twinoid_refresh_tokens; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.twinoid_refresh_tokens (twinoid_refresh_token, _twinoid_refresh_token_hash, twinoid_user_id, ctime, atime) FROM stdin;
\.


--
-- Data for Name: twinoid_user_links; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.twinoid_user_links (user_id, twinoid_user_id, linked_at, linked_by) FROM stdin;
\.


--
-- Data for Name: twinoid_users; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.twinoid_users (twinoid_user_id, name, archived_at) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: mysql
--

COPY public.users (user_id, ctime, display_name, display_name_mtime, email_address, email_address_mtime, username, username_mtime, password, password_mtime, is_administrator) FROM stdin;
\.


--
-- Name: _post_formatting_costs _post_formatting_costs_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public._post_formatting_costs
    ADD CONSTRAINT _post_formatting_costs_pkey PRIMARY KEY (forum_post_revision_id, formatting);


--
-- Name: announcements announcements_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_pkey PRIMARY KEY (announcement_id);


--
-- Name: dinoparc_servers dinoparc_servers_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.dinoparc_servers
    ADD CONSTRAINT dinoparc_servers_pkey PRIMARY KEY (dinoparc_server);


--
-- Name: dinoparc_sessions dinoparc_sessions_dinoparc_server_dinoparc_user_id_key; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.dinoparc_sessions
    ADD CONSTRAINT dinoparc_sessions_dinoparc_server_dinoparc_user_id_key UNIQUE (dinoparc_server, dinoparc_user_id);


--
-- Name: dinoparc_sessions dinoparc_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.dinoparc_sessions
    ADD CONSTRAINT dinoparc_sessions_pkey PRIMARY KEY (dinoparc_server, _dinoparc_session_key_hash);


--
-- Name: dinoparc_user_links dinoparc_user_links_dinoparc_server_dinoparc_user_id_key; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.dinoparc_user_links
    ADD CONSTRAINT dinoparc_user_links_dinoparc_server_dinoparc_user_id_key UNIQUE (dinoparc_server, dinoparc_user_id);


--
-- Name: dinoparc_user_links dinoparc_user_links_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.dinoparc_user_links
    ADD CONSTRAINT dinoparc_user_links_pkey PRIMARY KEY (user_id, dinoparc_server, dinoparc_user_id);


--
-- Name: dinoparc_user_links dinoparc_user_links_user_id_dinoparc_server_key; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.dinoparc_user_links
    ADD CONSTRAINT dinoparc_user_links_user_id_dinoparc_server_key UNIQUE (user_id, dinoparc_server);


--
-- Name: dinoparc_users dinoparc_users_dinoparc_server_username_key; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.dinoparc_users
    ADD CONSTRAINT dinoparc_users_dinoparc_server_username_key UNIQUE (dinoparc_server, username);


--
-- Name: dinoparc_users dinoparc_users_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.dinoparc_users
    ADD CONSTRAINT dinoparc_users_pkey PRIMARY KEY (dinoparc_server, dinoparc_user_id);


--
-- Name: forum_post_revisions forum_post_revisions_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_post_revisions
    ADD CONSTRAINT forum_post_revisions_pkey PRIMARY KEY (forum_post_revision_id);


--
-- Name: forum_posts forum_posts_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_posts
    ADD CONSTRAINT forum_posts_pkey PRIMARY KEY (forum_post_id);


--
-- Name: forum_role_grants forum_role_grants_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_role_grants
    ADD CONSTRAINT forum_role_grants_pkey PRIMARY KEY (forum_section_id, user_id);


--
-- Name: forum_role_revocations forum_role_revocations_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_role_revocations
    ADD CONSTRAINT forum_role_revocations_pkey PRIMARY KEY (forum_section_id, user_id, start_time);


--
-- Name: forum_sections forum_sections_key_key; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_sections
    ADD CONSTRAINT forum_sections_key_key UNIQUE (key);


--
-- Name: forum_sections forum_sections_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_sections
    ADD CONSTRAINT forum_sections_pkey PRIMARY KEY (forum_section_id);


--
-- Name: forum_threads forum_threads_key_key; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_threads
    ADD CONSTRAINT forum_threads_key_key UNIQUE (key);


--
-- Name: forum_threads forum_threads_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_threads
    ADD CONSTRAINT forum_threads_pkey PRIMARY KEY (forum_thread_id);


--
-- Name: hammerfest_servers hammerfest_servers_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.hammerfest_servers
    ADD CONSTRAINT hammerfest_servers_pkey PRIMARY KEY (hammerfest_server);


--
-- Name: hammerfest_sessions hammerfest_sessions_hammerfest_server_hammerfest_user_id_key; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.hammerfest_sessions
    ADD CONSTRAINT hammerfest_sessions_hammerfest_server_hammerfest_user_id_key UNIQUE (hammerfest_server, hammerfest_user_id);


--
-- Name: hammerfest_sessions hammerfest_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.hammerfest_sessions
    ADD CONSTRAINT hammerfest_sessions_pkey PRIMARY KEY (hammerfest_server, _hammerfest_session_key_hash);


--
-- Name: hammerfest_user_links hammerfest_user_links_hammerfest_server_hammerfest_user_id_key; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.hammerfest_user_links
    ADD CONSTRAINT hammerfest_user_links_hammerfest_server_hammerfest_user_id_key UNIQUE (hammerfest_server, hammerfest_user_id);


--
-- Name: hammerfest_user_links hammerfest_user_links_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.hammerfest_user_links
    ADD CONSTRAINT hammerfest_user_links_pkey PRIMARY KEY (user_id, hammerfest_server, hammerfest_user_id);


--
-- Name: hammerfest_user_links hammerfest_user_links_user_id_hammerfest_server_key; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.hammerfest_user_links
    ADD CONSTRAINT hammerfest_user_links_user_id_hammerfest_server_key UNIQUE (user_id, hammerfest_server);


--
-- Name: hammerfest_users hammerfest_users_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.hammerfest_users
    ADD CONSTRAINT hammerfest_users_pkey PRIMARY KEY (hammerfest_server, hammerfest_user_id);


--
-- Name: hammerfest_users hammerfest_users_server_username_key; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.hammerfest_users
    ADD CONSTRAINT hammerfest_users_server_username_key UNIQUE (hammerfest_server, username);


--
-- Name: oauth_access_tokens oauth_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.oauth_access_tokens
    ADD CONSTRAINT oauth_access_tokens_pkey PRIMARY KEY (oauth_access_token_id);


--
-- Name: oauth_clients oauth_clients_key_key; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.oauth_clients
    ADD CONSTRAINT oauth_clients_key_key UNIQUE (key);


--
-- Name: oauth_clients oauth_clients_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.oauth_clients
    ADD CONSTRAINT oauth_clients_pkey PRIMARY KEY (oauth_client_id);


--
-- Name: old_dinoparc_sessions old_dinoparc_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_dinoparc_sessions
    ADD CONSTRAINT old_dinoparc_sessions_pkey PRIMARY KEY (dinoparc_server, _dinoparc_session_key_hash, ctime);


--
-- Name: old_hammerfest_sessions old_hammerfest_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_hammerfest_sessions
    ADD CONSTRAINT old_hammerfest_sessions_pkey PRIMARY KEY (hammerfest_server, _hammerfest_session_key_hash, ctime);


--
-- Name: old_oauth_client_app_uris old_oauth_client_app_uris_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_oauth_client_app_uris
    ADD CONSTRAINT old_oauth_client_app_uris_pkey PRIMARY KEY (oauth_client_id, start_time);


--
-- Name: old_oauth_client_callback_uris old_oauth_client_callback_uris_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_oauth_client_callback_uris
    ADD CONSTRAINT old_oauth_client_callback_uris_pkey PRIMARY KEY (oauth_client_id, start_time);


--
-- Name: old_oauth_client_display_names old_oauth_client_display_names_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_oauth_client_display_names
    ADD CONSTRAINT old_oauth_client_display_names_pkey PRIMARY KEY (oauth_client_id, start_time);


--
-- Name: old_oauth_client_secrets old_oauth_client_secrets_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_oauth_client_secrets
    ADD CONSTRAINT old_oauth_client_secrets_pkey PRIMARY KEY (oauth_client_id, start_time);


--
-- Name: old_twinoid_access_tokens old_twinoid_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_twinoid_access_tokens
    ADD CONSTRAINT old_twinoid_access_tokens_pkey PRIMARY KEY (_twinoid_access_token_hash, ctime);


--
-- Name: old_twinoid_refresh_tokens old_twinoid_refresh_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_twinoid_refresh_tokens
    ADD CONSTRAINT old_twinoid_refresh_tokens_pkey PRIMARY KEY (_twinoid_refresh_token_hash, ctime);


--
-- Name: old_twinoid_user_links old_twinoid_user_links_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_twinoid_user_links
    ADD CONSTRAINT old_twinoid_user_links_pkey PRIMARY KEY (user_id, twinoid_user_id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (session_id);


--
-- Name: twinoid_access_tokens twinoid_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.twinoid_access_tokens
    ADD CONSTRAINT twinoid_access_tokens_pkey PRIMARY KEY (_twinoid_access_token_hash);


--
-- Name: twinoid_access_tokens twinoid_access_tokens_twinoid_user_id_key; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.twinoid_access_tokens
    ADD CONSTRAINT twinoid_access_tokens_twinoid_user_id_key UNIQUE (twinoid_user_id);


--
-- Name: twinoid_refresh_tokens twinoid_refresh_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.twinoid_refresh_tokens
    ADD CONSTRAINT twinoid_refresh_tokens_pkey PRIMARY KEY (_twinoid_refresh_token_hash);


--
-- Name: twinoid_refresh_tokens twinoid_refresh_tokens_twinoid_user_id_key; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.twinoid_refresh_tokens
    ADD CONSTRAINT twinoid_refresh_tokens_twinoid_user_id_key UNIQUE (twinoid_user_id);


--
-- Name: twinoid_user_links twinoid_user_links_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.twinoid_user_links
    ADD CONSTRAINT twinoid_user_links_pkey PRIMARY KEY (user_id, twinoid_user_id);


--
-- Name: twinoid_user_links twinoid_user_links_twinoid_user_id_key; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.twinoid_user_links
    ADD CONSTRAINT twinoid_user_links_twinoid_user_id_key UNIQUE (twinoid_user_id);


--
-- Name: twinoid_user_links twinoid_user_links_user_id_key; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.twinoid_user_links
    ADD CONSTRAINT twinoid_user_links_user_id_key UNIQUE (user_id);


--
-- Name: twinoid_users twinoid_users_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.twinoid_users
    ADD CONSTRAINT twinoid_users_pkey PRIMARY KEY (twinoid_user_id);


--
-- Name: users username__uniq; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT username__uniq UNIQUE (username);


--
-- Name: users users_email_address_key; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_address_key UNIQUE (email_address);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (user_id);


--
-- Name: announcements announcement__forum_thread__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcement__forum_thread__fk FOREIGN KEY (forum_thread_id) REFERENCES public.forum_threads(forum_thread_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: announcements announcement__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcement__user__fk FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dinoparc_sessions dinoparc_session__dinoparc_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.dinoparc_sessions
    ADD CONSTRAINT dinoparc_session__dinoparc_user__fk FOREIGN KEY (dinoparc_server, dinoparc_user_id) REFERENCES public.dinoparc_users(dinoparc_server, dinoparc_user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dinoparc_users dinoparc_user__dinoparc_server__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.dinoparc_users
    ADD CONSTRAINT dinoparc_user__dinoparc_server__fk FOREIGN KEY (dinoparc_server) REFERENCES public.dinoparc_servers(dinoparc_server) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_user_links dinoparc_user_link__dinoparc_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.dinoparc_user_links
    ADD CONSTRAINT dinoparc_user_link__dinoparc_user__fk FOREIGN KEY (dinoparc_server, dinoparc_user_id) REFERENCES public.dinoparc_users(dinoparc_server, dinoparc_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_user_links dinoparc_user_link__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.dinoparc_user_links
    ADD CONSTRAINT dinoparc_user_link__user__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dinoparc_user_links dinoparc_user_link_linked_by__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.dinoparc_user_links
    ADD CONSTRAINT dinoparc_user_link_linked_by__user__fk FOREIGN KEY (linked_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: email_verifications email_verification__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.email_verifications
    ADD CONSTRAINT email_verification__user__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: forum_role_grants forum_moderator__forum_section__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_role_grants
    ADD CONSTRAINT forum_moderator__forum_section__fk FOREIGN KEY (forum_section_id) REFERENCES public.forum_sections(forum_section_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: forum_role_grants forum_moderator__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_role_grants
    ADD CONSTRAINT forum_moderator__user__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: forum_role_grants forum_moderator_granter__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_role_grants
    ADD CONSTRAINT forum_moderator_granter__user__fk FOREIGN KEY (granted_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: forum_role_revocations forum_moderator_granter__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_role_revocations
    ADD CONSTRAINT forum_moderator_granter__user__fk FOREIGN KEY (granted_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: forum_role_revocations forum_moderator_revoker__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_role_revocations
    ADD CONSTRAINT forum_moderator_revoker__user__fk FOREIGN KEY (revoked_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: forum_posts forum_post__forum_thread__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_posts
    ADD CONSTRAINT forum_post__forum_thread__fk FOREIGN KEY (forum_thread_id) REFERENCES public.forum_threads(forum_thread_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: forum_post_revisions forum_post_revision__forum_revision__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_post_revisions
    ADD CONSTRAINT forum_post_revision__forum_revision__fk FOREIGN KEY (forum_post_id) REFERENCES public.forum_posts(forum_post_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: _post_formatting_costs forum_post_revision__forum_revision__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public._post_formatting_costs
    ADD CONSTRAINT forum_post_revision__forum_revision__fk FOREIGN KEY (forum_post_revision_id) REFERENCES public.forum_post_revisions(forum_post_revision_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: forum_post_revisions forum_post_revision__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_post_revisions
    ADD CONSTRAINT forum_post_revision__user__fk FOREIGN KEY (author_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: forum_role_revocations forum_role_revocation__forum_section__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_role_revocations
    ADD CONSTRAINT forum_role_revocation__forum_section__fk FOREIGN KEY (forum_section_id) REFERENCES public.forum_sections(forum_section_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: forum_role_revocations forum_role_revocation_user__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_role_revocations
    ADD CONSTRAINT forum_role_revocation_user__user__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: forum_threads forum_thread__forum_section__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.forum_threads
    ADD CONSTRAINT forum_thread__forum_section__fk FOREIGN KEY (forum_section_id) REFERENCES public.forum_sections(forum_section_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: hammerfest_sessions hammerfest_session__hammerfest_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.hammerfest_sessions
    ADD CONSTRAINT hammerfest_session__hammerfest_user__fk FOREIGN KEY (hammerfest_server, hammerfest_user_id) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: hammerfest_users hammerfest_user__hammerfest_server__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.hammerfest_users
    ADD CONSTRAINT hammerfest_user__hammerfest_server__fk FOREIGN KEY (hammerfest_server) REFERENCES public.hammerfest_servers(hammerfest_server) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_user_links hammerfest_user_link__hammerfest_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.hammerfest_user_links
    ADD CONSTRAINT hammerfest_user_link__hammerfest_user__fk FOREIGN KEY (hammerfest_server, hammerfest_user_id) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_user_links hammerfest_user_link__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.hammerfest_user_links
    ADD CONSTRAINT hammerfest_user_link__user__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: hammerfest_user_links hammerfest_user_link_linked_by__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.hammerfest_user_links
    ADD CONSTRAINT hammerfest_user_link_linked_by__user__fk FOREIGN KEY (linked_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: oauth_access_tokens oauth_access_token__oauth_client__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.oauth_access_tokens
    ADD CONSTRAINT oauth_access_token__oauth_client__fk FOREIGN KEY (oauth_client_id) REFERENCES public.oauth_clients(oauth_client_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: oauth_access_tokens oauth_access_token__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.oauth_access_tokens
    ADD CONSTRAINT oauth_access_token__user__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: old_dinoparc_sessions old_dinoparc_session__dinoparc_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_dinoparc_sessions
    ADD CONSTRAINT old_dinoparc_session__dinoparc_user__fk FOREIGN KEY (dinoparc_server, dinoparc_user_id) REFERENCES public.dinoparc_users(dinoparc_server, dinoparc_user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: old_hammerfest_sessions old_hammerfest_session__hammerfest_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_hammerfest_sessions
    ADD CONSTRAINT old_hammerfest_session__hammerfest_user__fk FOREIGN KEY (hammerfest_server, hammerfest_user_id) REFERENCES public.hammerfest_users(hammerfest_server, hammerfest_user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: old_oauth_client_app_uris old_oauth_client_app_uri__oauth_client__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_oauth_client_app_uris
    ADD CONSTRAINT old_oauth_client_app_uri__oauth_client__fk FOREIGN KEY (oauth_client_id) REFERENCES public.oauth_clients(oauth_client_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: old_oauth_client_callback_uris old_oauth_client_callback_uri__oauth_client__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_oauth_client_callback_uris
    ADD CONSTRAINT old_oauth_client_callback_uri__oauth_client__fk FOREIGN KEY (oauth_client_id) REFERENCES public.oauth_clients(oauth_client_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: old_oauth_client_display_names old_oauth_client_display_name__oauth_client__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_oauth_client_display_names
    ADD CONSTRAINT old_oauth_client_display_name__oauth_client__fk FOREIGN KEY (oauth_client_id) REFERENCES public.oauth_clients(oauth_client_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: old_oauth_client_secrets old_oauth_client_secret__oauth_client__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_oauth_client_secrets
    ADD CONSTRAINT old_oauth_client_secret__oauth_client__fk FOREIGN KEY (oauth_client_id) REFERENCES public.oauth_clients(oauth_client_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: old_twinoid_refresh_tokens old_twinoid_refresh_token__twinoid_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_twinoid_refresh_tokens
    ADD CONSTRAINT old_twinoid_refresh_token__twinoid_user__fk FOREIGN KEY (twinoid_user_id) REFERENCES public.twinoid_users(twinoid_user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: sessions session__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT session__user__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: twinoid_access_tokens twinoid_access_token__twinoid_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.twinoid_access_tokens
    ADD CONSTRAINT twinoid_access_token__twinoid_user__fk FOREIGN KEY (twinoid_user_id) REFERENCES public.twinoid_users(twinoid_user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: old_twinoid_access_tokens twinoid_access_token__twinoid_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_twinoid_access_tokens
    ADD CONSTRAINT twinoid_access_token__twinoid_user__fk FOREIGN KEY (twinoid_user_id) REFERENCES public.twinoid_users(twinoid_user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: twinoid_refresh_tokens twinoid_refresh_token__twinoid_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.twinoid_refresh_tokens
    ADD CONSTRAINT twinoid_refresh_token__twinoid_user__fk FOREIGN KEY (twinoid_user_id) REFERENCES public.twinoid_users(twinoid_user_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: twinoid_user_links twinoid_user_link__twinoid_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.twinoid_user_links
    ADD CONSTRAINT twinoid_user_link__twinoid_user__fk FOREIGN KEY (twinoid_user_id) REFERENCES public.twinoid_users(twinoid_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: old_twinoid_user_links twinoid_user_link__twinoid_user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_twinoid_user_links
    ADD CONSTRAINT twinoid_user_link__twinoid_user__fk FOREIGN KEY (twinoid_user_id) REFERENCES public.twinoid_users(twinoid_user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: old_twinoid_user_links twinoid_user_link__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.old_twinoid_user_links
    ADD CONSTRAINT twinoid_user_link__user__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: twinoid_user_links twinoid_user_link__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.twinoid_user_links
    ADD CONSTRAINT twinoid_user_link__user__fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: twinoid_user_links twinoid_user_link_linked_by__user__fk; Type: FK CONSTRAINT; Schema: public; Owner: mysql
--

ALTER TABLE ONLY public.twinoid_user_links
    ADD CONSTRAINT twinoid_user_link_linked_by__user__fk FOREIGN KEY (linked_by) REFERENCES public.users(user_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- PostgreSQL database dump complete
--

