-- Drop everything from the Database, be careful before calling this script: there's no going back
DROP SCHEMA public CASCADE;
CREATE SCHEMA IF NOT EXISTS public;
CREATE EXTENSION IF NOT EXISTS pgcrypto;
