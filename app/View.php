<?php
namespace app;
require_once "utils/functions.php";
use \Exception            as Exception;
use function utils\insane as insane;

if (! defined("ABS_PATH")) define("ABS_PATH", dirname(__FILE__) . "/../");

class View {
  // Directory path where our views will be stored
  private static $directory = "views"; // Template directory
  private        $variables = array(); // Template variables
  private        $template; // Template file path
  private static $header    = "partials/header"; // Header file
  private static $footer    = "partials/footer"; // Footer file

  /**
   * Checks if passed template file exists and loads the content of that file.
   * 
   * @param string $template        Name of the template file without (.php)
   *                                extension
   * 
   * @return void                   Sets template file path on local property
   * @throws Exception              Throws an exception if empty or invalid
   *                                template name is passed
   */
  public function __construct(string $template) {
    if (\strlen($template) == 0) // Give error if empty string is passed
      throw new Exception("Template name is required for rendering templates!");
    // Merge templates directory and template file name with absolute path
    $file_path = \sprintf("%s/%s/%s.php", ABS_PATH, self::$directory, $template);
    self::verify($file_path, $template); // Check if guesed file exist
    $this->template = $file_path; // Store the guesed path on a local property
  }

  /**
   * Imports the template, header and footer files with respect to object context.
   * 
   * @return void                   Imports template and partial files
   */
  public function render(): void {
    // Load header partial template file
    require_once \sprintf("%s/%s/%s.php", ABS_PATH, self::$directory, self::$header);
    require_once $this->template; // Load template content file
    // Load footer partial template file
    require_once \sprintf("%s/%s/%s.php", ABS_PATH, self::$directory, self::$footer);
  }

  /**
   * Instantly import the template, header and footer files with template name only.
   * Note that object context and custom properties will not be available when
   * using this method!
   * 
   * @param string $template    Name of the template file without (.php) extension
   * 
   * @return void               Loads template and partial template files
   */
  public static function d_render(string $template): void {
    if (\strlen($template) == 0) // Give error if empty string is passed
      throw new Exception("Template name is required for rendering templates!");
    // Merge templates directory and template file name with absolute path
    $file_path = \sprintf("%s/%s/%s.php", ABS_PATH, self::$directory, $template);
    self::verify($file_path, $template); // Check if guesed file exist
    // Load header partial template file
    require_once \sprintf("%s/%s/%s.php", ABS_PATH, self::$directory, $header);
    require_once $file_path; // Load template content file
    // Load footer partial template file
    require_once \sprintf("%s/%s/%s.php", ABS_PATH, self::$directory, $footer);
  }

  /**
   * Call this function to make sure if the file exists is specified path before
   * proceeding.
   * 
   * @return void
   * @throws Exception              Throws an exception if passed `path` does
   *                                not refer to an existing file
   */
  private static function verify(string $path, string $name = null): void {
    if (! file_exists($path))
      throw new Exception(
        \sprintf("No template file found for template named (%s)", $name ?? "undefined")
      );
  }

  // Magic method, sets public properties to local `variables` array
  public function __set($name, $value) { $this->variables[$name] = $value; }
  
  // Magic method, loads values from local `variables` array when trying to access public properties
  public function __get($name) {
    return insane($name, $this->variables) ? $this->variables[$name] : NULL;
  }
}