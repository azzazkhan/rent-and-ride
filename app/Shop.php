<?php
namespace app;

require_once "Model.php";
require_once "Location.php";
require_once "Car.php";
require_once "Application.php";
use function utils\gtsane as gtsane;
use function utils\dump as dump;

class Shop extends Model {
  protected static $table       = "shops";
  public    static $primary_col = "shop_id";
  protected        $uniques     = array("@slug");
  protected static $dependables = array(Location::class); // Belongs to one location
  // Has many cars and many applications
  protected        $relatables  = array(Car::class, Application::class);
  protected static $indexes     = array("location_id");
  public static    $fields      = array(
                                    "name", "phone", "address", "slug",
                                    "location_id"
                                  );
  public           $location; // Blongs to one location
  public           $cars         = array(); // Has many cars
  public           $applications = array(); // Has many applications

  /**
   * @abstract                Abstract overriden method of parent `Models` class
   */
  protected function mount(array $data): void {
    $this->identifier  = gtsane(static::$primary_col, $data);
    $this->name        = gtsane("name", $data);
    $this->phone       = gtsane("phone", $data);
    $this->address     = gtsane("address", $data) ?? NULL;
    $this->slug        = gtsane("slug", $data);
    $this->location_id = gtsane("location_id", $data);
  }

  public function load_relatables() {
    $relatables = parent::load_relatables();
    $this->cars = gtsane("Car", $relatables);
    $this->applications = gtsane("Application", $relatables);
  }

  public function load_dependables() {
    $dependencies = parent::load_dependables();
    $this->location = gtsane("Location", $dependencies);
  }

  public static function referenced(array $models): array {
    $data = parent::referenced($models);
    if (count($data) == 0) return [];
    $instances = [];
    foreach($data as $row)
      $instances[] = new self($row);
    return $instances;
  }

  // Overriden method, needed to create self instance for each record
  public static function all(): array {
    $rows = parent::all(); // Fetch records from database
    // Return an empty array if no records were found
    if (count($rows) == 0 || empty($rows) || \is_null($rows)) return [];
    // Create a new instance of self and mount the data on it
    foreach($rows as $row) $models[] = new self($row);
    return $models; // Return the created instances in an array
  }
}