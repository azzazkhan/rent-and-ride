<?php
namespace app;

require_once "Model.php";
require_once "Location.php";
require_once "Car.php";
require_once "Application.php";
use function utils\gtsane as gtsane;

class Shop extends Model {
  protected static $table       = "shops";
  public    static $primary_col = "shop_id";
  protected        $uniques     = array("@slug");
  protected static $dependables = array(Location::class);
  protected        $relatables  = array(Car::class, Application::class);
  protected static $indexes     = array("location_id");
  public static    $fields      = array(
                                    "name", "phone", "address", "slug",
                                    "location_id"
                                  );
  public           $location;               // Blongs to one location
  public           $cars         = array(); // Has many cars
  public           $applications = array(); // Has many applications

  // Overriden method, needed to store child model(s) in custom local property
  public function load_relatables() {
    // Load child model(s) instances
    $relatables = parent::load_relatables();
    // Store them in local properties
    $this->cars = gtsane("Car", $relatables);
    $this->applications = gtsane("Application", $relatables);
  }

  // Overriden method, needed to store parent model(s) in custom local property
  public function load_dependables() {
    // Load parent model(s) instances
    $dependencies = parent::load_dependables();
    // Store them in local properties
    $this->location = gtsane("Location", $dependencies);
  }

  // Overriden method, needed to create self instance for each record
  public static function referenced(array $models): array {
    $data = parent::referenced($models); // Fetch raw data from database
    // Return an empty array if no records were found
    if (count($data) == 0) return [];
    // Create a new instance of self and mount the data on it
    foreach($data as $row) $instances[] = new self($row);
    return $instances; // Return the created instances in an array
  }

  // Overriden method, needed to create self instance for each record
  public static function all(): array {
    $rows = parent::all(); // Fetch raw data from database
    // Return an empty array if no records were found
    if (count($rows) == 0 || empty($rows) || \is_null($rows)) return [];
    // Create a new instance of self and mount the data on it
    foreach($rows as $row) $models[] = new self($row);
    return $models; // Return the created instances in an array
  }
}