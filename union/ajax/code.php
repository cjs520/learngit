<?php

require_once MVMMALL_ROOT.'include/captcha.class.php';
$Captcha = new  Captcha();
if ($main_settings['mm_code_width'] && $main_settings['mm_code_height']){
    $Captcha->mCheckImageWidth  = $main_settings['mm_code_width'];
    $Captcha->mCheckImageHeight = $main_settings['mm_code_height'];
}
$Captcha->OutCheckImage();