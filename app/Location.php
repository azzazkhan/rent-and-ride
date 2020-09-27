<?php
namespace app;

require_once "Model.php";
require_once "Shop.php";
use function utils\gtsane as gtsane;

class Location extends Model {
  protected static $table       = "locations";
  public    static $primary_col = "location_id";
  protected        $relatables  = array(Shop::class); // Has many shops
  protected        $uniques     = array("slug");
  public static    $fields      = array("name", "slug");
  public           $shops       = array(); // Has many shops

  // Overriden method, needed to store child model(s) in custom local property
  public function load_relatables() {
    // Load child model(s) instances
    $relatables = parent::load_relatables();
    // Store them in local properties
    $this->shops = gtsane("Shop", $relatables);
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