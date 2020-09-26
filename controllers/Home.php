<?php
namespace app;
require_once "_Controller.php";
require_once ABS_PATH . "app/Location.php";

$view = new view("home");
$view->locations = Location::all();
$view->render();