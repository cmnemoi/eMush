#!/bin/bash

set -e
set -u

psql -v ON_ERROR_STOP=1 --username "postgres" <<-EOSQL
        CREATE USER "mysql" WITH PASSWORD 'password';
        CREATE DATABASE "mush" WITH OWNER "mysql";
        GRANT ALL PRIVILEGES ON DATABASE "mush" TO "mysql";
        ALTER ROLE mysql CREATEDB;
    
        CREATE USER "etwin.dev" WITH PASSWORD 'password';
        CREATE DATABASE "etwin.dev" WITH OWNER "etwin.dev";
        GRANT ALL PRIVILEGES ON DATABASE "etwin.dev" TO "etwin.dev";
        
        \c etwin.dev
        ALTER SCHEMA public OWNER TO "etwin.dev";
        GRANT ALL ON SCHEMA public TO "etwin.dev";
EOSQL