<?php
require_once 'include/cart.class.php';
$cart = new cart();
$list=$cart->cart_simple_list(4);
exit(json_encode($list));