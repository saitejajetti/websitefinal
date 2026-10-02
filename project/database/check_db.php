<?php

$db = new SQLite3(__DIR__ . "/database/grocery..db");

$result = $db->querySingle("PRAGMA integrity_check;");

echo $result;

$db->close();
?>