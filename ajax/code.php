<?php
require_once MVMMALL_ROOT.'include/captcha.class.php';
$Captcha = new  Captcha();
if ($mm_code_width && $mm_code_height)
{
    $Captcha->mCheckImageWidth = $mm_code_width;
    $Captcha->mCheckImageHeight = $mm_code_height;
}
$Captcha->OutCheckImage();