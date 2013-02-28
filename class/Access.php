<?php
abstract class Access {


  /**
   * Request object
   *
   * @var Request
   */
  public $request;


  public $acls = array();

  /**
   *
   * @param Request $request
   */
  public function __construct(Request $request) {
    $this->request = $request;
  }

  /**
   * Abstract function for providing objects
   * the ability to define access controls
   */
  public function add_acl(AccessControl $ac) {
    $this->acls[] = $ac;
  }

  /**
   * Run through all acls and see if we have access;
   *
   * @access public
   * @return boolean
   */
  public function has_access() {
    foreach ($this->acls as $acl) {
      if ($acl->has_access() == FALSE) {
        return FALSE;
      }
    }
    return TRUE;
  }


  /**
   * Static factory method
   *
   * @param Request $request
   * @return Access
   */
  public static function get(Request &$request) {
    $class = 'Access_' + $request;
    return new $class($request);
  }
}
