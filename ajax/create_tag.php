<?php
if(!$m_check_id) exit('ERR');

$content=strip_tags($content);
require_once 'include/scsw.class.php';

echo scsw::create_tag($content,5);