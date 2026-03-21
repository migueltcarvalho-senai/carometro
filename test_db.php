<?php
try {
    $c = new PDO('mysql:host=localhost;port=3306;charset=utf8', 'root', '');
    echo "3306 OK\n";
    $c->exec("USE carometrodb");
    echo "DB carometrodb exists on 3306\n";
} catch(Exception $e) { echo "3306 FAIL: " . $e->getMessage() . "\n"; }

try {
    $c = new PDO('mysql:host=localhost;port=3307;charset=utf8', 'root', '');
    echo "3307 OK\n";
    $c->exec("USE carometrodb");
    echo "DB carometrodb exists on 3307\n";
} catch(Exception $e) { echo "3307 FAIL: " . $e->getMessage() . "\n"; }
?>
