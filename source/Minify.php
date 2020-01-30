<?php

use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;

$dir = dirname(__DIR__, 1)."/views/assets";

$umask = umask(0);

/**
 * CSS
 */
if (!is_dir("{$dir}/css/dist")) {
    mkdir("{$dir}/css/dist/");
}

$minCSS = new CSS();
$minCSS->add("{$dir}/css/style.css");
$minCSS->add("{$dir}/css/form.css");
$minCSS->add("{$dir}/css/button.css");
$minCSS->add("{$dir}/css/message.css");
$minCSS->add("{$dir}/css/load.css");
$minCSS->minify("{$dir}/css/dist/style.min.css");

/**
 * JS
 */
if (!is_dir("{$dir}/js/dist")) {
  mkdir("{$dir}/js/dist/");
}

$minJS = new JS();
$minJS->add("{$dir}/js/jquery.js");
$minJS->add("{$dir}/js/jquery-ui.js");
$minJS->minify("{$dir}/js/dist/scripts.min.js");