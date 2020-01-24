<?php

/**
 * SITE CONFIG
 */
define("SITE", [
    "name" => "auth_php",
    "desc" => "Auth system built in PHP",
    "domain" => "localhost",
    "locale" => "en_US",
    "root" => "http://localhost/login_upinside"
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
    "github_creator" => "doougui",
    "twitter_creator" => "oDougui",
    "linkedin_creator" => "douglaspigoulart",
    "facebook_appId" => ""
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