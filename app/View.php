<?php
namespace app;
require_once "utils/functions.php";
use \Exception            as Exception;
use function utils\insane as insane;

if (! defined("ABS_PATH")) define("ABS_PATH", dirname(__FILE__) . "/../");

class View {
  // Directory path where our views will be stored
  private static $template_dir  = ABS_PATH . "views/";
  private        $template_vars = array(); // Template variables
  private        $template_name; // Template name
  private        $template; // Template file path (validated)

  /**
   * @param string $template  Name of the template file without .php extension.
   * @param string $path      Path to the template file relative to root of the
   *                          the server without trailing slash.
   * 
   * @return void
   */
  public function __construct(string $template, string $path = null) {
    if (empty($template) || \is_null($template))
      throw new Exception("Template name is required for rendering templates!");
    $this->template_name = $template;
    
    if (! empty($path) && ! \is_null($path))
      $this->update_path($path);
    
    $tempalte_file = self::$template_dir . $template . ".php";
    self::verify($tempalte_file, $template);

    $this->template = $tempalte_file;
  }

  public function render(): void {
    require_once self::$template_dir . "components/header.php";
    require_once $this->template;
    require_once self::$template_dir . "components/footer.php";
  }

  /**
   * Directly render a template without instantiating the View object.
   * 
   * Note: ($this) object context and custom properties will not be available
   * in this mode!
   * 
   * @param string $template    Name of the template file without .php extension.
   * @param string $t_path      (Optional) Directory path to the template file
   *                            relative to app's root directory.
   */
  public static function d_render(string $template, string $t_path = ""): void {
    if (empty($template) || \is_null($template))
      throw new Exception("Template name is required for rendering templates!");
    
    if (! empty($t_path) && ! \is_null($t_path))
      $path = ABS_PATH . $t_path . "/";
    else
      $path = self::$template_dir;
    
    $template_path = $path . $template . ".php";
    self::verify($template_path, $template);
    require_once $path . "components/header.php";
    require_once $template_path;
    require_once $path . "components/footer.php";
  }

  private function update_path(string $path): void {
    self::$template_dir = ABS_PATH . $path . "/";
  }
  private static function verify(string $path, string $template = null): void {
    if (! file_exists($path))
      throw new Exception(
        \sprintf(
          "No template file found for template named (%s)",
          $template ?? "undefined"
        )
      );
  }
  public function __set($name, $value) {
    $this->template_vars[$name] = $value;
  }
  public function __get($name) {
    return insane($name, $this->template_vars) ? $this->template_vars[$name] : false;
  }
}