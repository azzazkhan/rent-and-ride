<?php
namespace app;

require_once "utils/functions.php";
require_once "Database.php";
require_once "RelationalModal.php";

use \Exception            as Exception;
use function utils\insane as insane;
use function utils\gtsane as gtsane;

abstract class Model extends RelationalModel {
  protected        $identifier; // Primary key (set by `mount` method)
  protected static $table; // Table, where model records are stored
  // Primary index/key column's name, defaults to "id"
  public    static $primary_col = "id";
  // Unique indexes or composite unique indexes with foreign key columns
  protected        $uniques     = array();
  // All field names (excluding primary key)
  public static    $fields      = array();

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
   * 
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
    
    // Make sure that child classes follows our rules!
    $this->sanitize(); // Initial bootstrap validations

    // We can proceed, verifying our data
    // Primary key is passed, we can fetch the exact record
    if (\is_numeric($ref)) // Mount the fetched data on current object
      $this->mount($this->fetch_by_id($ref));

    // Raw data already verified on initial validation of this method, mount it
    else if (\is_array($ref)) $this->mount($ref);

    // A search string is passed, exact record can be fetched, if the model has independ unique key(s)
    // Maybe the model has a composite unique index with an index field, pass the reference model as well
    else if (\is_string($ref))
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
      die(\sprintf("No records found for model against primary identifier (%s)", $id));
    // Return as associative array having key names indentical to column names
    return $query->fetch_assoc();
  }

  /**
   * Fetchs unique (single) record of model through explicitly specified
   * independent unique indexes or composite unique indexes with foregin indexes
   * through reference of passed models.
   * 
   * @param string $reference       The unique key used to fetch record. e.g slug
   * @param array $models           If there is a composite unique index of a
   *                                column with a foreign key/index then pass
   *                                the reference models.
   * 
   * @return array                  Returns raw data retrieved for current model
   *                                with provided reference keyword and models
   * @throws Exception              Throws an exception if no independent/composite
   *                                unique indexes are defined for current model
   *                                or passed reference models are not defined
   *                                as a dependency for current model
   */
  protected function fetch_by_unique_columns(string $reference, array $models = []): array {
    // Make sure that at least one independent/composite unique index is defined for current model
    if (! $this->has_uniques())
      throw new Exception("No independent unique or composite unique index explicitly defined for current model!");
    // If model references are passed then only composite unique indexes will be used with foreign indexes to search for record
    // If composite unique indexes are not defined then ignore the passed reference models
    if (count($models) > 0 && count($this->composite_uniques()) > 0) {
      // Invalid models passed
      if (! self::are_valid_models($models))
        throw new Exception("Invalid models passed as reference with unique model search!");
      return $this->unique_by_reference($reference, $models);
    }
    /**
     * ===================================================================
     * No reference model passed or composite unique index defined!
     * ===================================================================
     */
    // Model does not have any independent unique index explicitly defined which means that current model doesn't have capability of retrieving unique record through non-primary index
    if (count($this->independent_uniques()) == 0)
      throw new Exception("No independent unique index explicitly defined for current model!");
    // Everthing is OK, we're good to go :)
    global $database;
    // Get independent unique indexes of current model
    $uniques = $this->independent_uniques();
    // SELECT * FROM `table` WHERE `unique_field_1` = "passed_phrase" OR `unique_field_2` = "passed_phrase" ... LIMIT 1
    foreach ($uniques as $field)
      $conditions[] = \sprintf("`%s` = '%s'", $field, $reference);
    // Join all conditions for WHERE statement and add "OR" clause between them.
    $conditions = \implode(" OR ", $conditions);
    $query = $database->query(\sprintf("SELECT * FROM `%s` WHERE %s LIMIT 1", static::$table, $conditions));
    // No record found for passed search reference
    if ($query->num_rows == 0)
      // We're terminating the script but a visual cloud be displayed if constructor method is a bit tweaked
      die(\sprintf(
        "No records found against model search (<strong>%s</strong>)", $reference
      ));
    // Return as associative array having key names indentical to column names
    return $query->fetch_assoc();
  }

  /**
   * This method fetchs **raw data** of all entries for current model from the
   * database. It is recommended to override this method to create self instances
   * for each record fetched and mount the data on it (if required)
   * 
   * Note that `sanitize` method will not be called as this is a static method!
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
   * Inserts passed data into the database for current model
   * 
   * @param array $data             The data to be inserted in database in an
   *                                associative array who's key must match
   *                                with the field names of current model
   * @param bool $primary_key       Specifiy wether primary key is also passed
   *                                or not (if not then `NULL` will be passed
   *                                as the value)
   * 
   * @return bool                   Return TRUE if query executed successfully else
   *                                FALSE
   */
  public static function create(array $data, bool $primary_key = false) {
    // Check if all fields are specified in passed data
    if (! static::verify_fields($data, !$primary_key))
      throw new Exception("Cannot insert model! Incompaitable data passed!");

    global $database;
    $fields_defined = static::$fields; // Get all fields defined for current model
    // If primary key is passed then add it as well
    if ($primary_key) \array_unshift($fields_defined, static::$primary_col);
    // INSERT INTO `table` (`field_1`, `field_2` ... `field_n`) VALUES ('value_1', 'value_2' ... 'value_n')
    // Arrange the field names and values into proper SQL syntax
    foreach ($fields_defined as $field) {
      $fields[] = "`$field`";
      $values[] = \sprintf("'%s'", gtsane($field, $data) ?? NULL);
    }
    // Merge all elements of array into a single string saperated by commas (,)
    $fields = \implode(", ", $fields);
    $values = \implode(", ", $values);
    // Execute the query and return the result
    return $database->query(\sprintf("INSERT INTO `%s` (%s) VALUES (%s)", static::$table, $fields, $values));
  }

  /** 
   * =====================================================================
   *    THE MOUNT METHOD
   * =====================================================================
   */
  /**
   * Sets current object's property to passed **validated** data's corresponding
   * key.
   * 
   * @param array $data   Raw data in an (associative) array whos element count
   *                      must be equal to the number of fields
   *                      (including primary key) and it's key name should be
   *                      identical to field names of current model.
   * 
   * @return void         Mounts data onto current model instance.
   */
  protected function mount(array $data): void {
    // Set the value for primary key in `identifier` property
    $this->identifier  = gtsane(static::$primary_col, $data);
    unset($data[static::$primary_col]); // Remove primary key from passed data
    // Set properties of current model identical to passed field names
    foreach($data as $field => $value)
      // Properties names will be identical to column names
      $this->{$field} = $value;
  }
  
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
   * @param bool $ignore_pk     Ignores the primary key field existence check in
   *                            passed data set
   * 
   * @return bool               Returns FALSE if number of elements in passed
   *                            array are not equal to the number of fields or
   *                            if the keys of passed array does not match with
   *                            the fields of current model, and a TRUE if array
   *                            passes all validations
   * @throws Exception          Throws an exception if fields are not explicitly
   *                            defined on child class
   *                            
   */
  protected static function verify_fields(array $row, bool $ignore_pk = false): bool {
    $fields = static::$fields; // Get all field names defined for current model
    // Add primary key at beginning of retrieved field names if `ignore_pk` is false
    if (! $ignore_pk) \array_unshift($fields, static::$primary_col);
    // Number of elements in passed array should be equal to number of fields defined for current model (primary key exception)
    if (count($fields) != count($row)) return false;

    // Each key name must correspond to model's field name (primary key exception)!
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
    if (count($this->uniques) > count(static::$fields))
      throw new Exception("Number of explicitly defined unique indexes is greater than fields defined for current model!");
    // Iterate over each explicitly defined unique index
    foreach ($this->uniques as $unique_field)
      // Each independent/composite unique index must be explicitly defined as a field of current model!
      // If the unique index's name does matches then it might be a composite unique index, omit the initial symbol (@) and try again
      if (! (
        \in_array($unique_field, static::$fields, true) ||
        \in_array(\substr($unique_field, 1), static::$fields, true)
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
   *! Note: This method is not invoked when static method is called on current
   *! class!
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
    if (! \is_array(static::$fields) || count(static::$fields) == 0)
      throw new Exception("No fields explicitly defined for current model!");
    // Explicitly defined fields must not contain any duplicates!
    if (count(static::$fields) > count(array_unique(static::$fields)))
      throw new Exception("Duplicate fields found current model's fields!");
    // Each field explicitly defined must be in string format
    foreach (static::$fields as $field)
      if (! \is_string($field) || \strlen($field) == 0)
        throw new Exception("Explicitly defined fields must be in string formate!");
    // This method independently throws exception, no conditions required
    $this->has_uniques();
    parent::sanitize(); // Call relational sanitization
  }
}