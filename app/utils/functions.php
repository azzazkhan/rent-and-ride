<?php
namespace utils;
define('DEF_ARRAY', $_GET);

/**
 * Checks wether a key is present in an array or not and also checks
 * if the value for specified key is null or not (if specified)
 * 
 * @param string $key     The key to search in passed array
 * @param array  $arr     The array to search in default is the array defined in
 *                        `DEF_ARRAY` constant
 * @param bool   $strict  If passed true then empty check will be applied default
 *                        is set to false
 * 
 * @return bool
 */
function insane(string $key, array $arr = DEF_ARRAY, bool $strict = false): bool {
  if (\array_key_exists($key, $arr)) {
    if ($strict == true && (empty($arr[$key]) || \is_null($arr[$key])))
      return false;
    return true;
  }
  return false;
}

/**
 * Checks wether a key is present in an array and also the value of
 * that key matches with the specified one (if value exists in array)
 * 
 * @param string $key     The key to search in passed array
 * @param mixed  $val     The value to be matched with the value passed against
 *                        key in passed array.
 * @param array  $arr     The array to search in default is the array defined in
 *                        `DEF_ARRAY` constant
 * 
 * @return bool
 */
function mvsane(string $key, $val, array $arr = DEF_ARRAY): bool {
  return insane($key, $arr, true) && $arr[$key] == $val ? true : false;
}

/**
 * Fetchs the value against specified key in the array if the key exists
 * in array.
 * 
 * @param string $key     The key to search in passed array
 * @param array  $arr     The array to search in default is the array defined in
 *                        `DEF_ARRAY` constant
 * 
 * @return mixed          Returns the value in array against passed key or a
 *                        boolean false
 */
function gtsane(string $key, array $arr = DEF_ARRAY) {
  return insane($key, $arr, true) ? $arr[$key] : false;
}

/**
 * Gets short name from provided fully qualified class reference.
 * 
 * @param string $reference     The fully qualified class name
 * 
 * @return string               Returns the short name of passed class reference
 */
function get_name_by_reflection(string $reference) {
  $reflection = new \ReflectionClass($reference);
  return $reflection->getShortName();
}

/**
 * Applies `var_dump` method on passed data and wraps the result inside `<pre>`
 * tags. This function is created mainly for debugging purpose.
 * 
 * @param mixed $data       The data whos information is needed
 * 
 * @return void             Prints the formatted result
 */
function dump($data) {
  print("<pre>\n");
    var_dump($data);
  print("</pre>\n");
}