<?php
namespace app;

require_once "Model.php";
require_once "Shop.php";
require_once "Car.php";
use function utils\gtsane as gtsane;
use function utils\dump as dump;

class Application extends Model {
  protected static $table       = "applications";
  public    static $primary_col = "app_id";
  // Belongs to one car and one shop
  protected static $dependables = array(Car::class, Shop::class);
  protected static $indexes     = array("car_id", "shop_id");
  public static    $fields      = array(
                                    "name", "email", "contact", "nic_number",
                                    "address", "car_id", "shop_id", "submitted_at"
                                  );
  public           $shop; // Blongs to one location
  public           $car; // Belongs to one car

  /**
   * @abstract                Abstract overriden method of parent `Models` class
   */
  protected function mount(array $data): void {
    $this->identifier   = gtsane(static::$primary_col, $data);
    $this->name         = gtsane("name", $data);
    $this->email        = gtsane("email", $data) ?? NULL;
    $this->contact      = gtsane("contact", $data);
    $this->nic_number   = gtsane("nic_number", $data);
    $this->address      = gtsane("address", $data);
    $this->car_id       = gtsane("car_id", $data);
    $this->shop_id      = gtsane("shop_id", $data);
    // Submission date timestamp
    $this->submitted_at = !\is_null(gtsane("submitted_at", $data)) ? (\strtotime(gtsane("submitted_at", $data))) : NULL;
  }

  public function load_dependables() {
    $dependencies = parent::load_dependables();
    $this->shop = gtsane("Shop", $dependencies);
    $this->car = gtsane("Car", $dependencies);
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