<?php
namespace app;

require_once "utils/functions.php";
require_once "Database.php";
require_once "RelationalModal.php";

use \Exception                            as Exception;
use function utils\insane                 as insane;
use function utils\dump                   as dump;

abstract class Model extends RelationalModel {
  protected        $identifier; // Primary key (set by `mount` method)
  protected static $table; // Table, where model records are stored
  // Primary index/key column's name, defaults to "id"
  public    static $primary_col = "id";
  // Unique indexes or composite unique indexes with foreign key columns
  protected        $uniques     = array();
  // All field names (excluding primary key)
  public           $fields      = array();

  /**
   * Creates new instance of model and mounts provided data (after validating)
   * or fetchs data from database by provided reference.
   * 
   * @param mixed $ref      This parameter accepts one of 3 following values.
   *                        1. Primary key (int) of model which uniqely identifies
   *                           the model throughout the table.
   *                        2. Search phrase (string) - Example slug of record
   *                           or username.
   *                           Note: This param type requires the second parameter
   *                           to be passed!
   *                        3. Custom data (array) - If you already a row from
   *                           MySQL then pass the `associative array` to load
   *                           that values on current instance.
   * @param array $models   (Optional) Reference models required for fetching
   *                        models that have composite index on unique fields.
   * @return void           Creates instance of child model class.
   * @throws Exception      Throws an exception if passed reference/data
   *                        validation fails.
   */
  public function __construct($ref, array $models = []) {
    // Make sure the passed reference/data matches our parameter type
    if (
      (\is_numeric($ref) && (int) $ref <= 0) ||
      (\is_string($ref) && \strlen($ref) == 0) ||
      (\is_array($ref) && ! $this->verify_fields($ref))
    )
      throw new Exception("Cannot instantiate model! Error: Passed reference type does not fullfil specified rules.");
    
    // Make sure elements passed in model references are of type model
    if (count($models) > 0)
        if (! self::are_valid_models($models))
          throw new Exception("Cannot instantiate model! Error: Passed reference model(s) are invalid model(s)!");
    
    // Make sure that child classes follows our specified rules
    $this->sanitize(); // Initial bootstrap validations

    // We can proceed, verifying our data
    // Primary key is passed, we can fetch the exact record
    if (\is_numeric($ref))
      // Mount the fetched data on current object
      $this->mount($this->fetch_by_id($ref));

    // Raw data already verified on initial validation of this method, mount it
    elseif (\is_array($ref)) $this->mount($ref);

    // A search string is passed, exact record can be fetched, if the model has independ unique key(s)
    // Maybe the model has a composite unique index with an index field, pass the reference model as well
    elseif (\is_string($ref))
      // Mount the fetched data on current object
      $this->mount($this->fetch_by_unique_columns($ref, $models));
    
    // The reference passed is of unknown type
    else
      throw new Exception("Cannot instantiate model! Error: Unknown reference data passed!");
  }

  /**
   * Fetchs the exact model record from database by using passed primary key (ID)
   * 
   * @param int $id           Primary key (ID) to make base of search query
   * 
   * @return array            Returns raw fetched data from database in an
   *                          (associative) array
   * @throws Exception        Throws exception if no records found
   */
  private function fetch_by_id(int $id): array {
    global $database;
    $query = $database->query(\sprintf("SELECT * FROM `%s` WHERE `%s` = %d LIMIT 1", static::$table, static::$primary_col, $id));
    // No record found, object cannot be created!
    if ($query->num_rows == 0)
      die(\sprintf("No records found against model ID (%s)", $id));
    // Return as associative array having key names indentical to column names
    return $query->fetch_assoc();
  }

  /** 
   * =====================================================================
   *   TODO: Write documentation for this method
   * =====================================================================
   */
  /**
   * Fetchs unique (single) record of model through explicitly specified
   * independent unique indexes or composite unique indexes with foregin indexes
   * through reference of passed models.
   * 
   * @param string $reference
   * @param array $models           (Optional)
   * 
   * @return array
   * @throws Exception
   */
  protected function fetch_by_unique_columns(string $reference, array $models = []): array {
    if (! $this->has_uniques())
      throw new Exception("No independent unique or composite unique index explicitly defined for current model!");
    // If model references are passed then only composite unique indexes will be used with foreign indexes to search for record
    if (count($models) > 0) {
      // Invalid models passed
      if (! self::are_valid_models($models))
        throw new Exception("Invalid models passed as reference with unique model search!");
      // Model reference passed for model which does not have any composite index
      if (count($this->composite_uniques()) == 0)
        throw new Exception("Reference models passed for model which does not has any explicitly defined composite unique index with foreign index");
      return $this->unique_by_reference($reference, $models);
    }
    /**
     * ===================================================================
     * No reference model passed, continue with independent unique indexes
     * ===================================================================
     */
    // Model does not have any independent unique index explicitly defined
    if (count($this->independent_uniques()) == 0)
      throw new Exception("No independent unique index explicitly defined for current model!");
    // Everthing is OK, we're good to go :)
    global $database;
    // Get independent unique indexes of current model
    $uniques = $this->independent_uniques();
    foreach ($uniques as $field)
      $conditions[] = \sprintf("`%s` = '%s'", $field, $reference);
    // Join all conditions for WHERE statement and add "AND" clause between them.
    $conditions = \implode(" OR ", $conditions);
    $query = $database->query(\sprintf("SELECT * FROM `%s` WHERE %s LIMIT 1", static::$table, $conditions));
    // No record found for passed search reference (terminate the script)
    if ($query->num_rows == 0)
      die(\sprintf(
        "No records found against model search (<strong>%s</strong>)", $reference
      ));
    return $query->fetch_assoc();
  }

  /**
   * Fetchs all records of current model from database.
   * 
   * @static            Child class should override and call this method
   *                    explicitly to fetch data, then create self instances for
   *                    each record
   * 
   * @return array      Returns an array of (associative) arrays containg
   *                    model(s) data whos keys correspond to fields of current
   *                    model OR simply an (empty) array if no records were found
   */
  public static function all(): array {
    self::check_table(); // Make sure table is explicitly defined in child class
    global $database;
    $query = $database->query(\sprintf("SELECT * FROM `%s`", static::$table));
    
    // If no records are found then return an empty array, else return an array of (associative) arrays containg model(s) data whos keys correspond to fields of current model
    return $query->num_rows > 0 ? $query->fetch_all(MYSQLI_ASSOC) : [];
  }

  /**
   * Returns primary key value of current model
   * 
   * @return int          Return primary key of current model
   */
  public function id(): int { return $this->identifier; }

  /** 
   * =====================================================================
   *    ABSTRACT METHODS
   * =====================================================================
   */
  /**
   * Sets current object's corresponding property value to passed validated data.
   * @abstract mount      Must need to be explicitly defined in child class
   *                      because each model have independent and different
   *                      publically accessible properties
   * @param array $data   Raw data in an (associative) array whos element count
   *                      must be equal to the number of fields
   *                      (including primary key) and it's key name should be
   *                      identical to field names of current model.
   * 
   * @return void         Mounts data onto current model instance.
   */
  abstract protected function mount(array $data): void;
  
  /** 
   * =====================================================================
   *    UTILITY METHODS
   * =====================================================================
   */
  /**
   * Checks wether the `table` is explicitly defined on child instance
   * 
   * @return bool           Returns true (Script terminates otherwise)
   * @throws Exception      Throws an exception if table name is not explicitly
   *                        defined on child instance
   */
  protected static function check_table(): bool {
    return (! static::$table || ! is_string(static::$table) || \strlen(static::$table) == 0) ? false : true;
  }

  /**
   * Checks if passed (associative) array has same number of elements as the
   * fields defined for current model and also checks if all keys of that array
   * matches with the field names of current model.
   * 
   * @param array $row          The (associative) array containing data of
   *                            current model's fields
   * @return bool               Returns FALSE if number of elements in passed
   *                            array are not equal to the number of fields or
   *                            if the keys of passed array does not match with
   *                            the fields of current model, and a TRUE if array
   *                            passes all validations
   * @throws Exception          Throws an exception if fields are not explicitly
   *                            defined on child class
   *                            
   */
  protected function verify_fields(array $row): bool {
    // Get all field names (including primary key) and match field count with elements in passed array
    $fields = $this->fields; \array_unshift($fields, static::$primary_col);
    if (count($fields) != count($row)) return false;

    // Each key name must correspond to model's field name!
    foreach ($fields as $field)
      if (! insane($field, $row)) return false;
    return true; // Everything is fine :)
  }

  /**
   * Checks wether unique indexes are explicitly defined on current model or not.
   * 
   * @return bool               Returns true if uniqes indexes are explicitly
   *                            defined for current model, else returns a false
   * @throws Exception
   */
  protected function has_uniques(): bool {
    // No unique index explicitly defined for current model
    if (! \is_array($this->uniques) || count($this->uniques) == 0) return false;
    // Number of defined unique indexes exceeds the total number of defined fields (primary index/key not included)
    if (count($this->uniques) > count($this->fields))
      throw new Exception("Number of explicitly defined unique indexes is greater than fields defined for current model!");
    // Iterate over each explicitly defined unique index
    foreach ($this->uniques as $unique_field)
      // Each independent/composite unique index must be explicitly defined as a field of current model!
      // If the unique index's name does matches then it might be a composite unique index, omit the initial symbol (@) and try again
      if (! (
        \in_array($unique_field, $this->fields, true) ||
        \in_array(\substr($unique_field, 1), $this->fields, true)
      )) // The independent/composite unique index is not explicitly defined as a field of current model!
        throw new Exception(\sprintf("Explicitly defined unique index (%s) not found in fields defined for current model!", $unique_field));
    // Everthing is fine :)
    return true; 
  }

  /**
   * Checks if current model has any explicitly defined independent uniques indexes.
   * 
   * @return array                  Returns an empty array if the current model
   *                                has no explicitly defined independed unique
   *                                indexes or an array containing names of
   *                                independent unique indexes if found
   */
  protected function independent_uniques(): array {
    // No independent/composite unique indexes defined for current model!
    if (! $this->has_uniques()) return [];
    $independents = [];
    // Iterate over each explicitly defined independent unique index
    foreach ($this->uniques as $field)
      // If name of the unique index does not starts with (@) then it's an independent index
      if (\substr($field, 0, 1) != "@") $independents[] = $field;
    return $independents;
  }
  
  /**
   * Checks if current model has any explicitly defined composite uniques indexes.
   * 
   * @param bool $trim              (Optional) If passed true then initial (@)
   *                                symbol will be removed from the names of
   *                                composite unique indexes in returned array
   * 
   * @return array                  Returns an empty array if the current model
   *                                has no explicitly defined composite unique
   *                                indexes or an array containing names of
   *                                composite unique indexes if found
   */
  protected function composite_uniques($trim = false): array {
    // No independent/composite unique indexes defined for current model!
    if (! $this->has_uniques()) return [];
    $independents = [];
    // Iterate over each explicitly defined composite unique index
    foreach ($this->uniques as $field)
      // If name of the unique index starts with (@) then it's a composite index
      if (\substr($field, 0, 1) == "@")
        // If $trim is passed true then remove the initial (@) symbol
        $independents[] = $trim == true ? \substr($field, 1) : $field;
    return $independents;
  }

  /**
   * Checks if objects/reference in passed array are inherited from Model class or
   * not
   * 
   * @return bool                   Returns true if all objects/reference in
   *                                passed array of type model else false
   */
  protected static function are_valid_models(array $models): bool {
    // Empty array passed
    if (count($models) == 0) return false;
    foreach ($models as $model) // Iterate over each passed model
      // Each passed object must need be inherited from Model class!
      if (! $model instanceof self) return false;
    return true; // Everything is fine :)
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
    // Make sure the table is explicitly defined for current model
    if (! self::check_table())
      throw new Exception("No table explicitly defined for current model!");
    // Make sure if value for primary index/key is defined then it must be an intiger
    if (! is_null($this->identifier) && ! \is_numeric($this->identifier))
      throw new Exception("Primary index/key must be a numeric value");
    // Make sure that at least one field is explicitly defined for current model
    if (! \is_array($this->fields) || count($this->fields) == 0)
      throw new Exception("No fields explicitly defined for current model!");
    // Explicitly defined fields must not contain any duplicates!
    if (count($this->fields) > count(array_unique($this->fields)))
      throw new Exception("Duplicate fields found current model's fields!");
    // Each field explicitly defined must be in string format
    foreach ($this->fields as $field)
      if (! \is_string($field) || \strlen($field) == 0)
        throw new Exception("Explicitly defined fields must be in string formate!");
    // This method independently throws exception, no conditions required
    $this->has_uniques();
    parent::sanitize(); // Call relational sanitization
  }
}