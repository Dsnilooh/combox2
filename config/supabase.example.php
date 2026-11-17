<?php

$supa_host = getenv('SUPABASE_DB_HOST') ?: 'db.jrtlsodizgilnnlutbtz.supabase.co';
$supa_port = getenv('SUPABASE_DB_PORT') ?: '5432';
$supa_user = getenv('SUPABASE_DB_USER') ?: 'postgres';
$supa_dbname = getenv('SUPABASE_DB_NAME') ?: 'postgres';
$supa_password = getenv('SUPABASE_DB_PASSWORD') ?: 'unicesmag@@';

$supa_conn = pg_connect(
    "host=$supa_host port=$supa_port dbname=$supa_dbname " .
    "user=$supa_user password=$supa_password sslmode=require"
);
