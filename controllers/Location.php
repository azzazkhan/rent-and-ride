<?php
namespace app;

require_once "_Controller.php";
require_once ABS_PATH . "app/Location.php";

use function utils\gtsane as gtsane;

$view = new view("location");
$location = new Location(gtsane("location"));
$location->load_relatables();
$view->location = $location;
$view->title = \sprintf("Shops in %s &mdash; Rend & Ride", $location->name);
$view->render();