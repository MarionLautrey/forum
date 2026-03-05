<?php
    $host = 'localhost';
    $bdd = 'Forum';
    $user = 'root';
    $passwd = 'sgwn9fv2';
    try {
        $cnn = new PDO("mysql:host=$host;dbname=$bdd;charset=utf8", $user, $passwd, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    }
    catch(PDOException $e) {
        echo 'Erreur : '.$e->getMessage();
    }

date_default_timezone_set('UTC');
?>