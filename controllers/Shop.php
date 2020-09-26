<?php
namespace app;

require_once "_Controller.php";
require_once ABS_PATH . "app/Location.php";
require_once ABS_PATH . "app/Shop.php";

use function utils\gtsane as gtsane;

$view = new view("shop");
$shop = new Shop(gtsane("shop"), [new Location(gtsane("location"))]);
$shop->load_relatables();
$view->title = \sprintf("%s &mdash; Rent & Ride", $shop->name);
$view->shop = $shop;
$view->render();