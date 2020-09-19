<?php

require_once "app/utils/functions.php";
require_once "app/Location.php";
require_once "app/Shop.php";
require_once "app/Car.php";

use function utils\dump as dump;
use app\Location        as Location;
use app\Shop            as Shop;
use app\Car             as Car;

$shop = new Shop(3);
$shop->load_relatables();
// $shop_new = new Shop("frontier-motors", [new Location(1)]);
// $shop_new->load_dependables();
// printf("%s &mdash; %s<br />\n", $shop->name, $shop->location->name);