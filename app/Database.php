<?php
namespace app;

// Force PHP to report all errors, remove these lines in product mode!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Database {
  // Default server configuration for local servers
  private const DB_HOST =  "127.0.0.1";
  private const DB_USER =  "root";
  private const DB_PASS =  "";

  // Set your database name
  private const DB_NAME = "rent_and_ride";
  private $connection; // MySQL connection will be stored here
  
  /**
   * Establishes a new connection to the database server and stores the connection
   * in a private property.
   * 
   * @return void             Creates instance of database
   */
  public function __construct() {
    $mysqli = @new \mysqli(self::DB_HOST, self::DB_USER, self::DB_PASS, self::DB_NAME);
    if ($mysqli->connect_errno)
      die(\sprintf("Could not connect to database!<br />\nError: <strong>%s</strong>", $mysqli->connect_error));
    $this->connection = $mysqli;
  }

  /**
   * Executes passed query on current database.
   * @param string $sql       The SQL statement to be queried on current database.
   * @return object           The mysqli_result object of current query 
   */
  public function query(string $sql) {
    $query = @$this->connection->query($sql);
    if (! $query)
      die(
        "Database query execution failed!<br />\n" .
        \sprintf("Error: <strong>%s</strong>", $this->connection->error)
      );
    return $query;
  }
}

$database = new Database();