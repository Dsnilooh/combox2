<?php

$supa_host = 'aws-0-us-west-2.pooler.supabase.com';
$supa_port = '5432';
$supa_user = 'postgres.jrtlsodizgilnnlutbtz';
$supa_dbname = 'postgres';
$supa_password = 'unicesmag@@';

$supa_conn = pg_connect(
    "host=$supa_host " .
    "port=$supa_port " .
    "dbname=$supa_dbname " .
    "user=$supa_user " .
    "password=$supa_password " .
    "sslmode=require"
);