<?php
namespace app;

require_once "Model.php";
require_once "Shop.php";
use function utils\gtsane as gtsane;
use function utils\dump   as dump;

class Car extends Model {
  protected static $table       = "cars";
  public    static $primary_col = "car_id";
  protected        $uniques     = array("slug");
  protected static $dependables = array(Shop::class); // Belongs to one location
  public           $fields      = array(
                                    "name", "specifications", "daily_price",
                                    "weekly_price", "slug", "tags"
                                  );
  protected static $pivot       = "car_shop";
  public           $shops; // Blongs to many shops

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

  public function load_dependables() {
    $dependencies = parent::load_dependables();
    $this->location = gtsane("Location", $dependencies);
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