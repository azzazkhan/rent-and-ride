<?php
namespace app;

require_once "_Controller.php";
require_once ABS_PATH . "app/Application.php";

use function utils\insane as insane;
use function utils\gtsane as gtsane;

// We only accept post requests
if ($_SERVER["REQUEST_METHOD"] != "POST")
  header("Location: /"); // Redirect to homepage
if (gtsane("action") == "create") {
  $fields = array("customer_name", "customer_email", "customer_contact", "customer_nicn", "customer_address", "car_id", "shop_id");
  // Check if all required fields are present in request
  foreach ($fields as $field)
    if (! insane($field, $_POST)) // Give error if any field is missing
      die("Incomplete data passed!");

  $store = Application::create([
    "name" => gtsane("customer_name", $_POST),
    "email" => gtsane("customer_email", $_POST) ?? NULL,
    "contact" => gtsane("customer_contact", $_POST),
    "nic_number" => gtsane("customer_nicn", $_POST),
    "address" => gtsane("customer_address", $_POST),
    "car_id" => gtsane("car_id", $_POST),
    "shop_id" => gtsane("shop_id", $_POST),
    "submitted_at" => NULL, // This will be override by Application model
  ]);
  if (! $store)
    die("Cloud not add application data into database!");
  // If application was added succesfully then redirect to homepage
  header("Location: /");
}

// No car and shop details passed
if (! insane("car_name", $_POST, true) ||! insane("shop_id", $_POST, true))
  header("Location: /"); // Redirect to homepage

$view = new view("booking");
$car  = new Car(gtsane("car_name", $_POST)); // Car has independent unique `slug`
$shop = new Shop((int) gtsane("shop_id", $_POST));
$shop->load_dependables(); // Load location details of shop
$view->title = \sprintf("Rent (%s) &mdash; Rent & Ride", $car->name);
$view->car = $car;
$view->shop = $shop;
$view->render();