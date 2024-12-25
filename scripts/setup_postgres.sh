#!/usr/bin/env bash

export PGDATA="$PWD/.nix/postgres_data"
export PGHOST="$PWD/.nix/postgres"
export LOG_PATH="$PWD/.nix/postgres/log"
export PGDATABASE="postgres"
export PGPORT=5432

if [ ! -d $PGDATA ]; then
  mkdir -p $PGDATA
fi

if [ ! -d $PGHOST ]; then
  mkdir -p $PGHOST
fi

if [ ! -d $LOG_PATH ]; then
  mkdir -p $LOG_PATH
fi

if [ ! -d $PGDATA/base ]; then
  echo 'Initializing postgresql database...'
  initdb $PGDATA --auth=trust >/dev/null
fi

if ! pg_ctl status > /dev/null; then
  pg_ctl -D $PGDATA -l $LOG_PATH/server.log -o "--unix_socket_directories='$PGHOST'" start
fi

function create_user_and_database() {
  local database=$1
  echo "Creating user and database '$database'..."
  psql -d postgres <<EOF
    DO \$\$
    BEGIN
      CREATE USER "$database";
      EXCEPTION WHEN DUPLICATE_OBJECT THEN
      RAISE NOTICE 'User "$database" already exists';
    END
    \$\$;

    SELECT 'CREATE DATABASE "$database"'
    WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = '$database')\gexec

    GRANT ALL PRIVILEGES ON DATABASE "$database" TO "$database";
EOF
}

# Create the required databases
create_user_and_database "mush"
create_user_and_database "etwin.dev"

echo "PostgreSQL is ready with databases 'mush' and 'etwin.dev'"
