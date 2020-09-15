<?php
namespace app;

require_once "Model.php";
use function utils\gtsane as gtsane;

class Location extends Model {
  protected static $table = "locations";
  public    static $primary_col = "location_id";
  protected        $uniques     = array("slug");
  public           $fields      = array("name", "slug");
  protected        $shops       = array(); // Has many shops

  /**
   * @abstract                Abstract overriden method of parent `Models` class
   */
  protected function mount(array $data): void {
    $this->identifier = gtsane(static::$primary_col, $data);
    $this->name       = gtsane("name", $data);
    $this->slug       = gtsane("slug", $data);
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