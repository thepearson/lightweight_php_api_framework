<?php
/**
 * Factory used to create the class
 *
 * @author craigp
 */
class Factory_Service {

  /**
   * Factory loader. Loads desired service class based on request
   *
   * @static
   * @param Request $request
   * @param Response $response
   * @return
   */
  static function get(Request $request, Response $response = NULL) {

    // If no response defined create an empty one
    if (!isset($response)) {
      $response = new Response();
    }

    // Load the service
    $class = Service::PREFIX . $request->getClass();

    // ensure that the class exists
    if (class_exists($class)) {

      // log factory
      Log::i('Loading service ' . $class, 'service_factory');

      // return the service class
      return new $class($request, $response);
    }

    // no service class found return null
    // let instatiator handle errors
    return NULL;
  }


  /**
   * Helper method to handle camel cased classes
   *
   * @static
   * @param String $class
   * @return String
   */
  static function get_class($class) {
    if (strpos($class, '_')) {
      $classNames = explode('_', $class);
      $class = '';
      foreach ($classNames as $name) {
        $class .= ucfirst($name);
      }
      return $class;
    }
    else {
      return ucfirst($class);
    }
  }

  /**
   * Returns true if class exists
   * this is set as a static method so we can keep the service stuff all in
   * the same place
   *
   * @param String $class
    *     Service Class name, without the service prefix
   */
  static function exists($class) {
    return class_exists(Service::PREFIX . $class);
  }


  /**
   * Returns true if a service method esists
   *
   * @param string $class
   * @param string $method
   * @return boolean
   */
  static function method_exists($class, $method) {
    if (Factory_Service::exists($class)) {
      $class = Service::PREFIX . ucfirst($class);
      return method_exists($class, $method);
    }
    return FALSE;
  }
}
