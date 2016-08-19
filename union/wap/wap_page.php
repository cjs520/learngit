<?php
$module=dhtmlchars($module);
if(!$module) exit;

require 'wap/include/header.inc.php';
$mod_file="wap/wap_page/{$module}.inc.php";
if(file_exists($mod_file)) include $mod_file;

include template($module);
footer(false);