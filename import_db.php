<?php
try {
    $c = new PDO('mysql:host=localhost;port=3307;charset=utf8', 'root', '');
    $c->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = file_get_contents(__DIR__ . '/script banco.sql');
    $c->exec($sql);
    echo "Database imported successfully.\n";
} catch(Exception $e) { 
    echo "FAIL: " . $e->getMessage() . "\n"; 
}
?>
