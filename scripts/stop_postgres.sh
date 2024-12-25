#!/usr/bin/env bash

export PGDATA="$PWD/.nix/postgres_data"

function kill_postgres_cluster() {
  pg_ctl -D "$PGDATA" stop 2>/dev/null || \
  pg_ctl -D "$PGDATA" stop -m fast 2>/dev/null || \
  pkill postgres
}

kill_postgres_cluster
