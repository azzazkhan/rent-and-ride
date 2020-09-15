<?php

require_once "app/utils/functions.php";
require_once "app/Location.php";

use function utils\dump as dump;
use app\Location        as Location;

$location = new Location("saddar");
$location->load_relatables();
$location->load_dependables();
dump($location);