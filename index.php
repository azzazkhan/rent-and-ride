<?php

require_once "app/utils/functions.php";
require_once "app/Location.php";
require_once "app/Shop.php";

use function utils\dump as dump;
use app\Location        as Location;
use app\Shop            as Shop;

$shop = new Shop("toyota-motors");
// $shop->load_relatables();
$shop->load_dependables();
dump($shop);