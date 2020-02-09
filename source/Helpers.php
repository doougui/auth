<?php

/**
 * @param string|null $param
 * @return string
 */
function site(string $param = null): string
{
    if ($param && !empty(SITE[$param])) {
        return SITE[$param];
    }

    return SITE["root"];
}

/**
 * @param string $imageUrl
 * @return string
 */
function RouteImage(string $imageUrl): string
{
    return "http://via.placeholder.com/1200x628/0984E3/FFFFFF?text={$imageUrl}";
}


/**
 * @param string $path
 * @param bool $time
 * @return string
 */
function asset(string $path, $time = true): string
{
    $file = SITE["root"]."/views/assets/{$path}";
    $fileOnDir = dirname(__DIR__, 1)."/views/assets/{$path}";

    if ($time && file_exists($fileOnDir)) {
        $file .= "?time=".filemtime($fileOnDir);
    }

    return $file;
}

/**
 * @param string|null $type
 * @param string|null $message
 * @return string|null
 */
function flash(string $type = null, string $message = null): ?string
{
    if ($type && $message) {
        $_SESSION["flash"] = [
            "type" => $type,
            "message" => $message
        ];

        return null;
    }

    if (!empty($_SESSION["flash"]) && $flash = $_SESSION["flash"]) {
        unset($_SESSION["flash"]);
        return "<div class=\"message {$flash["type"]}\">
                    {$flash["message"]}
                </div>";
    }

    return null;
}

function preg_array_key_exists(string $pattern, array $array): array {
    $keys = array_keys($array);
    return preg_grep($pattern, $keys);
}

function csrfToken(bool $verify = false, string $csrfToken = null): ?string {
    if (!$verify) {
        if (empty($_SESSION["csrf_token"])) {
            $_SESSION["csrf_token"] = md5(uniqid(rand(), true));
        }

        return $_SESSION["csrf_token"];
    }

    if (
        $verify
        && !empty($csrfToken)
        && !empty($_SESSION["csrf_token"])
        && $csrfToken == $_SESSION["csrf_token"]
    ) {
        return $_SESSION["csrf_token"];
    }

    return null;
}