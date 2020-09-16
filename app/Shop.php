<?php
namespace app;

require_once "Model.php";
require_once "Location.php";
use function utils\gtsane as gtsane;

class Shop extends Model {
  protected static $table       = "shops";
  public    static $primary_col = "shop_id";
  protected        $uniques     = array("slug");
  protected static $dependables = array(Location::class); // Belongs to one location
  protected static $indexes     = array("location_id");
  public           $fields      = array(
                                    "name", "phone", "address", "slug",
                                    "location_id"
                                  );
  protected        $shops       = array(); // Has many shops

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