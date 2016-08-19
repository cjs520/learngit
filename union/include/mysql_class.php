<?php
if(function_exists('mysqli_connect'))
{
    require_once 'include/db/mysqli.class.php';
}
else
{
    require_once 'include/db/mysql.class.php';
}