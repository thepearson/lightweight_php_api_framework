<?php
class Config {

  /**
   * This instance
   *
   * @var Config
   */
  private static $registry = NULL;


  /**
   * Data, container
   *
   * @var array
   */
  private $data = array();


  /**
   * Static construct
   */
  private function __construct() {
    $this->data = array();
  }

  /**
   * Public instance getter
   *
   * return $this - Config
   */
  public static function getInstance() {
    if (is_null(self::$registry)) {
      self::$registry = new Config();
    }
    return self::$registry;
  }


  /**
   * Setter
   *
   * @param String $variable
   * @param Mixed $value
   */
  public function set($variable, $value) {
    $this->data[$variable] = $value;
  }


  /**
   * Getter
   *
   * @param String $variable
   * @return multitype:|NULL
   */
  public function get($variable) {
    if (isset($this->data[$variable])) {
      return $this->data[$variable];
    }
    return NULL;
  }
}
