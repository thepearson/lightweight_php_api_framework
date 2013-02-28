<?php
define('CONFIG_FILE', 'demo/config.ini');

ini_set('memory_limit', '2M');
$debug = TRUE;

/**
 * Autoload function
 */
spl_autoload_register(function($class_name) {

  // determine class path
  if (strpos($class_name, '_') == TRUE) {
    $class_name = str_replace('_', DIRECTORY_SEPARATOR, $class_name);
  }

  // ensure file exiss first
  if (file_exists(realpath(dirname(__FILE__)) . "/class/" . $class_name . ".php")) {
    // load class file
    require_once(realpath(dirname(__FILE__)) . "/class/" . $class_name . ".php");

    // ensure class was loaded
    if (!class_exists(str_replace(DIRECTORY_SEPARATOR, '_', $class_name), FALSE)) {
      if (!interface_exists(str_replace(DIRECTORY_SEPARATOR, '_', $class_name), FALSE)) {
        trigger_error("Unable to load class: $class_name", E_USER_WARNING);
      }
    }
  }
});


// process the config sections
$config = Config::getInstance();
$ini = parse_ini_file(CONFIG_FILE, TRUE);
foreach ($ini as $section => $values) {
  $config->set($section, $values);
}

// set up the request and process the response
$request = new Request();

// authorized?
//$acl = Access::get($request);
//if (!$acl->has_access()) {
//  $response = new Response();
//  $response->setHeader('__UAUTHORIZED', Http::Response(Http::UAUTHORIZED));
//  $response->respond();
//  exit;
//}

// get the service to run
$service = Factory_Service::get($request);

if ($service !== NULL) {

  // process the service
  $service->run();

  // get the response
  $response = $service->getResponse();

  // set start time
  if ($debug === TRUE) {
    $response->setHeader('X-Response-Time', microtime(TRUE) - $request->getStartTime());
  }

  // output the response
  $response->respond();

}
else {
  $response = new Response();
  $response->setHeader('__BAD_REQUEST', Http::Response(Http::BAD_REQUEST));
  $response->respond();
}

// quit
exit;
