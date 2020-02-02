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
    "driver" => getenv("DATABASE_DRIVER") ?: "mysql",
    "host" => getenv("DATABASE_HOST") ?: "localhost",
    "port" => getenv("DATABASE_PORT") ?: "3306",
    "dbname" => getenv("DATABASE_DBNAME") ?: "auth",
    "username" => getenv("DATABASE_USERNAME") ?: "root",
    "passwd" => getenv("DATABASE_PASSWORD") ?: "",
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
    "facebook_page" => getenv("SOCIAL_FACEBOOKPAGE"),
    "facebook_author" => getenv("SOCIAL_FACEBOOKAUTHOR"),
    "facebook_appId" => getenv("SOCIAL_FACEBOOKAPPID"),
    "twitter_creator" => getenv("SOCIAL_TWITTERCREATOR"),
    "twitter_site" => getenv("SOCIAL_TWITTERSITE")
]);

/**
 * MAIL
 */
define("MAIL", [
    "host" => getenv("MAIL_HOST"),
    "port" => getenv("MAIL_PORT"),
    "user" => getenv("MAIL_USER="),
    "passwd" => getenv("MAIL_PASSWD"),
    "from_name" => getenv("MAIL_FROMNAME"),
    "from_email" => getenv("MAIL_FROMEMAIL")
]);

/**
 * SOCIAL LOGIN: FACEBOOK
 */
define("FACEBOOK_LOGIN", [
    "clientId" => getenv("FACEBOOK_CLIENTID"),
    "clientSecret" => getenv("FACEBOOK_CLIENTSECRET"),
    "redirectUri" => SITE["root"]."/facebook",
    "graphApiVersion" => getenv("FACEBOOK_GRAPHAPIVERSION")
]);

/**
 * SOCIAL LOGIN: GOOGLE
 */
define("GOOGLE_LOGIN", [
    "clientId" => getenv("GOOGLE_CLIENTID"),
    "clientSecret" => getenv("GOOGLE_CLIENTSECRET"),
    "redirectUri" => SITE["root"]."/google"
]);