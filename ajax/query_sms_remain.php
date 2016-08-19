<?php
if($m_check_uid!=1) exit('这不是您该来的地方，请联系管理员');
echo  sms_send('','查询余额','sms_count2.do');
exit;