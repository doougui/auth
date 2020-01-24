<?php

/**
 * SITE CONFIG
 */
define("SITE", [
    "name" => "Auth",
    "desc" => "Sistema de autenticação",
    "domain" => "localhost",
    "locale" => "pt_BR",
    "root" => "http://localhost/auth"
]);

/**
 * SITE MINIFY
 */
if ($_SERVER["SERVER_NAME"] == "localhost") {
    require __DIR__."/Minify.php";
}

/**
 * DATABASE CONNECTION
 */
define("DATA_LAYER_CONFIG", [
    "driver" => "mysql",
    "host" => "localhost",
    "port" => "3306",
    "dbname" => "auth",
    "username" => "root",
    "passwd" => "",
    "options" => [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_CASE => PDO::CASE_NATURAL
    ]
]);

/**
 * SOCIAL
 */
define("SOCIAL", [
    "facebook_page" => "doougui",
    "facebook_author" => "Douglas Pinheiro Goulart",
    "facebook_appId" => "1234567890",
    "twitter_creator" => "oDougui",
    "twitter_site" => "oDougui"
]);

/**
 * MAIL
 */
define("MAIL", [

]);

/**
 * SOCIAL LOGIN: FACEBOOK
 */
define("FACEBOOK_LOGIN", [

]);

/**
 * SOCIAL LOGIN: GOOGLE
 */
define("GOOGLE_LOGIN", [

]);