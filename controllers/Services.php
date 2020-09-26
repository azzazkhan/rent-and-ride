<?php
namespace app;

require_once "_Controller.php";

$view = new view("services");
$view->title = "Services &mdash; Rent & Ride";
$view->render();