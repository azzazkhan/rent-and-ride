<?php

require_once "app/utils/functions.php";
require_once "app/Location.php";
require_once "app/Shop.php";

use function utils\dump as dump;
use app\Location        as Location;
use app\Shop            as Shop;

$location = new Location(1);
$shop = new Shop("frontier-motors", [$location]);
// $shop->load_relatables();
$shop->load_dependables();
printf("%s &mdash; %s<br />\n", $shop->name, $shop->location->name);