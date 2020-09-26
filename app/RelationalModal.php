<?php
namespace app;
require_once 'utils/functions.php';
require_once "Database.php";

use \Exception;
use function utils\get_name_by_reflection as get_name_by_reflection;

class RelationalModel {
  protected static $indexes     = array(); // Foreign indexes/keys
  protected        $relatables  = array(); // Has one/many
  protected static $dependables = array(); // Belongs to one/many
  protected static $pivot; // Pivot table (if belongs to many)

  /**
   * Loads parent model(s) for current model and mounts them onto current object's
   * instance
   * 
   * @return array                  Returns an associative array of numeric arrays
   *                                whos keys are identical to parent model names
   */
  public function load_dependables() {
    if (! $this->has_dependables()) return; // No relations defined
    if (self::has_pivot_table()) { // Relations are defined in pivot table
      // Loop over each dependency
      foreach (static::$dependables as $dependency) {
        // Load parent IDs for current model from the pivot table
        $parent_ids = $this->get_parent_ids($dependency);
        // No parent record for current model
        if (count($parent_ids) == 0) $models = [];
        foreach ($parent_ids as $id)
          // Create a new instance of parent model
          $models[] = new $dependency($id);
        $class = get_name_by_reflection($dependency);
        // Use key name identical to parent model's class name, e.g "Shop"
        $instances[$class] = $models;
      }
      // array("Parent1" => [..., ..., ...], "Parent2" => [..., ..., ...])
      return $instances;
    }
    // Current model has single parent model(s)
    foreach(static::$dependables as $dependency) {
      // Fetch short class name by model reference
      $class = get_name_by_reflection($dependency);
      // Parent ID is already defined as a foreign index in current model's fields, the property names are identical to field names
      $instances[$class] = new $dependency(
        $this->{$dependency::$primary_col}
      );
    }
    // array("Location" => Location(...), "Zone" => Zone(...))
    return $instances;
  }

  /**
   * This method fetchs primary keys for passed model against current model's
   * primary key
   * 
   * @param Model $model            Parent model whos IDs are to be fetched
   * 
   * @return array                  An array of fetched parent IDs
   */
  private function get_parent_ids($model) {
    global $database;
    // SELECT `parent_id` FROM `pivot_table` WHERE `model_id` = n
    $query = $database->query(\sprintf("SELECT `%s` FROM `%s` WHERE `%s` = %s", $model::$primary_col, static::$pivot, static::$primary_col, $this->id()));
    // Return an empty array if no records were found
    if ($query->num_rows == 0) return [];
    // MySQL return an array of numeric array whos first element is the ID
    foreach ($query->fetch_all(MYSQLI_NUM) as $row)
      // Create a new array with IDs as direct element of it
      $ids[] = $row[0];
    // array(1, 2, 3, 4, ..., n)
    return $ids; // Return the filtered array
  }

  /**
   * This method loads child models for current model against current model's
   * primary key
   * 
   * @return array                  Returns an associative array of numeric arrays
   *                                whos keys are identical to child model's class
   *                                name
   */
  public function load_relatables() {
    if (! $this->has_relatables()) return; // No relations defined
    foreach ($this->relatables as $relatable) {
      // Get a suitable name
      $class = get_name_by_reflection($relatable);
      // Get all child models with reference to current model
      $instances[$class] = $relatable::referenced([$this]);
    }
    // array("Child1" => [..., ..., ...], "Child2" => [..., ..., ...])
    return $instances;
  }

  /**
   * Fetchs unique (single) record based on passed reference key and models for
   * current model (composite unique index specified)
   * 
   * @param string $reference       The key to search for
   * @param array $models           The model to combine composite unique indexes
   *                                with
   * 
   * @return array                  Returns an associative array if record was
   *                                found for passed reference keyword and model
   *                                else an empty array
   * @throws Exception              Throws an exception if passed refrence models
   *                                are not defined as a dependency for current
   *                                model or no reference models are passed
   */
  protected function unique_by_reference(string $reference, array $models): array {
    // Check if passed models are defined as dependencies of current model
    $models = self::check_dependables($models);
    if (! $models || count($models) == 0)
      throw new Exception("Invalid reference passed to search through composite unique index!");
    global $database;
    // Get all composite unqiue indexes names with initial (@) symbol stripped
    $composite_uniques = $this->composite_uniques(true);
    // SELECT * FROM `shops` WHERE
    // `slug`    = 'frontier-motors' AND `location_id` = 1 OR
    // `address` = 'frontier-motors' AND `location_id` = 1
    foreach ($composite_uniques as $unique_index) {
      $clause = []; // Reset to an empty array for each iteration
      $clause[] = \sprintf("`%s` = '%s'", $unique_index, $reference);
      foreach ($models as $model)
        $clause[] = \sprintf("`%s` = '%s'", $model::$primary_col, $model->id());
      $conditional_query[] = \implode(" AND ", $clause);
    }
    $query = $database->query(\sprintf("SELECT * FROM `%s` WHERE %s LIMIT 1", static::$table, \implode(" OR ", $conditional_query)));
    // Terminate the script if no records were found (cannot instantiate)
    if ($query->num_rows == 0)
      die(\sprintf("No record found for current model against passed search keyword (%s)", $reference));
    return $query->fetch_assoc(); // Return the fetched (raw) data
  }

  public static function referenced(array $models): array {
    $models = self::check_dependables($models);
    if (! $models)
      throw new Exception("Cannot fetch by reference: Invalid reference passed for current model!");
    if (! static::check_table())
      throw new Exception("No table explicitly defined for current model!");
    $models = self::check_dependables($models);
    global $database;
    if (self::has_pivot_table()) {
      $referenced_ids = self::get_relational_ids($models);
      if (count($referenced_ids) == 0) return [];
      foreach ($referenced_ids as $id)
        $conditional_query[] = \sprintf('%s', $id);
      $conditional_query = \implode(", ", $conditional_query);
      $query = $database->query(\sprintf("SELECT * FROM `%s` WHERE `%s` IN (%s)", static::$table, static::$primary_col, $conditional_query));
      return $query->num_rows == 0 ? [] : $query->fetch_all(MYSQLI_ASSOC);
    }
    // Add where clause for each reference passed i.e. (WHERE `parent1_id` = '' AND `parent2_id` = '')
    foreach($models as $model)
      $conditional_query[] = \sprintf("`%s` = %d", $model::$primary_col, $model->id());
    // Merge into one SQL conditional statement
    $conditional_query = \implode(" AND ", $conditional_query);
    $query = $database->query(\sprintf("SELECT * FROM `%s` WHERE %s", static::$table, $conditional_query));

    // If data is found then return an array of associative array else an empty array
    return $query->num_rows > 0 ? $query->fetch_all(MYSQLI_ASSOC) : [];
  }

  private static function get_relational_ids(array $models): array {
    global $database;
    // Add where clause for each reference passed i.e. (WHERE `parent1_id` = '' AND `parent2_id` = '')
    foreach($models as $model)
      $conditional_query[] = \sprintf("`%s` = %d", $model::$primary_col, $model->id());
    // Merge into one SQL conditional statement
    $conditional_query = \implode(" AND ", $conditional_query);
    $query = $database->query(\sprintf("SELECT `%s` FROM `%s` WHERE %s", static::$primary_col, static::$pivot, $conditional_query));
    $ids = [];
    foreach ($query->fetch_all(MYSQLI_NUM) as $row)
      $ids[] = $row[0];
    return $ids;
  }
  
  /** 
   * =====================================================================
   *    UTILITY METHODS
   * =====================================================================
   */
  /**
   * This method checks if each passed models is present in explicitly defined
   * dependencies of current model and also checks if explicitly defined foreign
   * index/key matches with the primary index/key of passed model if specefied
   * 
   * @param array $models               The models to apply check on
   * @param bool  $ignore_index         (Optional) If passed false then explicitly
   *                                    defined foreign index/keys will not be
   *                                    matched with provided model's primary
   *                                    index/key.
   *                                    Default is true
   * 
   * @return mixed                      Returns passed models (duplicates removed)
   *                                    in an array of a falsy value if any check
   *                                    fails
   */
  private static function check_dependables(array $models, bool $ignore_index = false) {
    if (count($models) == 0) return false; // An empty array is passed
    $models = \array_unique($models, SORT_REGULAR); // Remove duplicate models
    $check = false; // Only to store model states
    foreach ($models as $model) { // Iterate over each (passed) dependency
      // Iterate over each (explicitly defined) dependency of current model
      foreach (static::$dependables as $dependency)
        // If the model matches a dependency then there's no point in continuing the  loop
        if ($model instanceof $dependency) { $check = true; break; }
      // Every single (passed) model must be present in current model's (explicitly defined) dependables!
      if (! $check) return false;
    }
    // Ignore matching foreign index/keys with (passed) model's primary index/key
    // If pivot table is passed then indexes will be defined inside pivot table
    if ($ignore_index || self::has_pivot_table()) return $models;
    foreach ($models as $model)
      // No foreign index/key matches with the (passed) model's primary index/key
      if (! \in_array($model::$primary_col, static::$indexes, true)) return false;
    // return the sanitized models
    return $models; // Everthing is okay :)
  }

  /**
   * This method checks wether the current model has any (has one/many) relationship
   * with other models or not.
   * 
   * @return bool                 If the current model has relationship with other
   *                              models then it will return true else false
   * @throws Exception            If anythings else rather than a model is found
   *                              inside the relatables then an exception will be
   *                              thrown
   */
  protected function has_relatables(): bool {
    return count($this->relatables) == 0 ? false : true;
  }
  
  /**
   * This method checks wether the current model dependends upon other models i.e.
   * does current model has (belongs to one/many) relationship with other models?
   * 
   * @return bool                 If the current model has relationship with other
   *                              models then it will return true else false
   * @throws Exception            If anythings else rather than a model is found
   *                              inside the dependables then an exception will be
   *                              thrown
   */
  protected static function has_dependables(): bool {
    return count(static::$dependables) > 0 ? true : false;
  }

  /**
   * Checks if foreign indexes/keys are explcitly defined for current model or not.
   * 
   * @return array                  Returns an array containing names of foreign
   *                                keys/indexes defined for current model or an
   *                                empty array if no indexes are defined
   * @throws Exception              Throws an exception if an empty string is passed
   *                                in foreign indexes array or anything else is
   *                                found rather than a string
   */
  private function indexes(): array {
    $indexes = \array_unique(static::$indexes, SORT_REGULAR);
    if (count($indexes) == 0) return [];
    foreach($indexes as $index)
      if (! \is_string($index) || \strlen($index) == 0)
        throw new Exception("Invalid foreign index/key names defined for current model!");
    return $indexes;
  }

  /**
   * Checks wether the `pivot` table is explicitly defined on child instance
   * 
   * @return bool           Returns true (Script terminates otherwise)
   * @throws Exception      Throws an exception if pivot table name is not
   *                        explicitly defined on child instance
   */
  private static function has_pivot_table(): bool {
    return (! static::$pivot || ! is_string(static::$pivot) || \strlen(static::$pivot) == 0) ? false : true;
  }

  /**
   * This method runs at very beginning (after the constructor method) and make
   * sures that there's no errors is explicitly defined fields for current model.
   * 
   *! Note: This method is not invoked a static method is called on current object!
   * 
   * @return void
   * @throws Exception              Throws an exception if found anything that's
   *                                prohibited
   */
  protected function sanitize(): void {
    $pivot = static::$pivot;
    // If pivot table is defined then it must be a valid table name
    if (
      ! \is_null($pivot) && ! empty($pivot) &&
      (! \is_string($pivot) || \strlen($pivot) == 0)
    )
      throw new Exception("Invalid value defined for pivot table name, value must be of type string!");
    // These methods throw exceptions on their own
    $this->indexes(); // Checks if every index is of type string
    // Checks if every reference defined is inherited from Model class
    $this->has_relatables(); 
    static::has_dependables();
    // Everything is fine :)
  }
}