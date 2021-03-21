<?php
# Соединение с БД
function connectToBase()
{
    # Читаем конфиг
    $hostname 	= 'localhost';
    $username 	= 'root';
    $password 	= '';
    $dbName 	= 'exchange';
    $charset 	= 'utf8';

    # Cоздать соединение
    $dsn = "mysql:host=$hostname;dbname=$dbName;charset=$charset";
    $opt = array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
    );
    $pdo = new PDO($dsn, $username, $password, $opt);

    return $pdo;
}