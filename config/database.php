<?php

// LOCAL CONNECTION
$local_host ="localhost";
$local_port ="5432";
$local_user ="postgres";
$local_dbname="combox";
$local_password="unicesmag";

// SUPABASE CONNECTION
$supa_host ="aws-0-us-west-2.pooler.supabase.com";
$supa_port ="5432";
$supa_user ="postgres.jrtlsodizgilnnlutbtz";
$supa_dbname="postgres";
$supa_password="unicesmag@@";

// LOCAL CONNECTION
$local_conn = pg_connect("
    host=$local_host
    port=$local_port
    dbname=$local_dbname
    user=$local_user
    password=$local_password
");

if (!$local_conn){
    die("Error Local connection xxx");
}else{
    echo ("<br> Local Connection success !!!");
}

// SUPABASE CONNECTION
$supa_conn = pg_connect("
    host=$supa_host
    port=$supa_port
    dbname=$supa_dbname
    user=$supa_user
    password=$supa_password
");

if (!$supa_conn){
    die("Error SUPA connection xxx");
}else{
    echo ("<br> SUPA Connection success !!!");
}

?>
