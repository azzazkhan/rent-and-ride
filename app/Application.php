<?php
namespace app;

require_once "Model.php";
require_once "Shop.php";
require_once "Car.php";

class Application extends Model {
  protected static $table       = "applications";
  public    static $primary_col = "app_id";
  protected static $dependables = array(Car::class, Shop::class);
  protected static $indexes     = array("car_id", "shop_id");
  public static    $fields      = array(
                                    "name", "email", "contact", "nic_number",
                                    "address", "car_id", "shop_id", "submitted_at"
                                  );
  public           $shop; // Blongs to one location
  public           $car; // Belongs to one car

  // Override method, needed to convert MySQL date-timestamp to UNIX timestamp
  protected function mount(array $data): void {
    parent::mount($data);
    // Change the submitted_at's value from datetime to timestamp
    $this->submitted_at = !\is_null(gtsane("submitted_at", $data)) ? (\strtotime(gtsane("submitted_at", $data))) : NULL;
  }

  // Override method, used to override submission time in passed data with MySQL timestamp
  public static function create(array $data, bool $primary_key = false) {
    // Override the "submitted_at" key's value in passed with MySQLs date-timestamp in current time
    $data["submitted_at"] = date("Y-m-d H:i:s");
    return parent::create($data, $primary_key);
  }

  // Overriden method, needed to store parent model(s) in custom local property
  public function load_dependables() {
    // Load parent model(s) instances
    $dependencies = parent::load_dependables();
    // Store them in local properties
    $this->shop = gtsane("Shop", $dependencies);
    $this->car = gtsane("Car", $dependencies);
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