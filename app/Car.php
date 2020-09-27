<?php
namespace app;

require_once "Model.php";
require_once "Shop.php";
require_once "Application.php";

class Car extends Model {
  protected static $table       = "cars";
  public    static $primary_col = "car_id";
  protected        $uniques     = array("slug");
  protected static $dependables = array(Shop::class);
  protected        $relatables  = array(Application::class);
  public static    $fields      = array(
                                    "name", "specifications", "daily_price",
                                    "weekly_price", "slug", "tags"
                                  );
  // Relationship with prent is defined in saperate pivot table
  protected static $pivot       = "car_shop";
  public           $shops; // Blongs to many shops
  public           $applications; // Has many applications

  // Overriden method, needed to store child model(s) in custom local property
  public function load_relatables() {
    // Load child model(s) instances
    $relatables = parent::load_relatables();
    // Store them in local properties
    $this->applications = gtsane("Application", $relatables);
  }

  // Overriden method, needed to store parent model(s) in custom local property
  public function load_dependables() {
    // Load parent model(s) instances
    $dependencies = parent::load_dependables();
    // Store them in local properties
    $this->shops = gtsane("Shop", $dependencies);
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