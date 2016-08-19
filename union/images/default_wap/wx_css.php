<?php
error_reporting(0);
ob_start();
$v=floatval($_GET['v']);
$folder=dirname(__FILE__);
$public_css_file=$folder.'/public.css';
$mvm_css_file=$folder.'/mvm.css';

$css_content=file_get_contents($public_css_file);
$css_content=str_replace(array('.jpg','.png','.gif'),array(".jpg?v=$v",".png?v=$v",".gif?v=$v"),$css_content);
echo $css_content;

$css_content=file_get_contents($mvm_css_file);
$css_content=str_replace(array('.jpg','.png','.gif'),array(".jpg?v=$v",".png?v=$v",".gif?v=$v"),$css_content);
echo $css_content;
ob_end_flush();
exit;