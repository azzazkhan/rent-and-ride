<?php
namespace app;

require_once "Model.php";
require_once "Shop.php";
require_once "Application.php";
use function utils\gtsane as gtsane;
use function utils\dump   as dump;

class Car extends Model {
  protected static $table       = "cars";
  public    static $primary_col = "car_id";
  protected        $uniques     = array("slug");
  protected static $dependables = array(Shop::class); // Belongs to one location
   // Has many applications
  protected        $relatables  = array(Application::class);
  public static    $fields      = array(
                                    "name", "specifications", "daily_price",
                                    "weekly_price", "slug", "tags"
                                  );
  protected static $pivot       = "car_shop";
  public           $shops; // Blongs to many shops
  public           $applications; // Has many applications

  /**
   * @abstract                Abstract overriden method of parent `Models` class
   */
  protected function mount(array $data): void {
    $this->identifier     = gtsane(static::$primary_col, $data);
    $this->name           = gtsane("name", $data);
    $this->specifications = gtsane("specifications", $data) ?? NULL;
    $this->daily_price    = gtsane("daily_price", $data) ?? NULL;
    $this->weekly_price   = gtsane("weekly_price", $data) ?? NULL;
    $this->slug           = gtsane("slug", $data);
    $this->tags           = gtsane("tags", $data) ?? NULL;
  }

  public function load_relatables() {
    $relatables = parent::load_relatables();
    $this->applications = gtsane("Application", $relatables);
  }

  public function load_dependables() {
    $dependencies = parent::load_dependables();
    $this->shops = gtsane("Shop", $dependencies);
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