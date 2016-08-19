<?php
$art_title = filtering($art_title);
$arr = $db->get_all("SELECT uid,board_subject,headnum FROM  `{$tablepre}bmain` WHERE supplier_id='$page_member_id' AND `board_subject` LIKE '%$art_title%'");

echo json_encode($arr);