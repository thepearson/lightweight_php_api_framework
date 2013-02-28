<?php
/**
 * Handle the request
 *
 * @author craigp
 */
class Request {


  /**
   * Array of query data
   *
   * @var array
   */
  public $query = NULL;


  /**
   * Mixed array or raw data
   *
   * @var mixed
   */
  public $data = NULL;


  /**
   * String Method
   *
   * @var string
   */
  public $method = NULL;


  /**
   * String Client
   *
   * @var string
   */
  public $client = 'unknown';


  /**
   * String Class
   *
   * @var string
   */
  public $class = NULL;


  /**
   * String Request Handler
   *
   * @var string
   */
  public $handler = NULL;


  /**
   * String Relation
   *
   * @var string
   */
  public $relation = NULL;


  /**
   * String Relation
   *
   * @var string
   */
  public $relation_key = NULL;


  /**
   * String Relation
   *
   * @var string
   */
  public $key = NULL;


  /**
   * HTTP Request Headers
   *
   * @var array
   */
  public $headers;


  /**
   * Used to time response
   *
   * @var float
   */
  public $start_time;


  /**
   * Class construct
   */
  public function __construct() {
    $this->start_time = microtime(TRUE);
    $this->method = $_SERVER['REQUEST_METHOD'];
    $this->client = $_SERVER['REMOTE_ADDR'];
    $this->headers = apache_request_headers();
    $this->query = $_GET;

    // start request
    Log::i('Started request at ' . $this->start_time, 'request');

    $this->process_route();

    // set the post variable
    $this->data = empty($_POST) ? file_get_contents("php://input") : $_POST;

    if (!is_array($this->data) && empty($this->data)) {
      Log::i('No POST data looking at query params', 'request');

      if (isset($this->key)) {
        Log::i('Query data found', 'request');
        $this->data = array('key' => $this->key);
      }
      else {
        Log::i('No query data', 'request');
        $this->data = array();
      }
    }
    else if (!is_array($this->data) && strlen($this->data) > 1) {
      Log::i('Data appears to be JSON', 'request');
      //Log::i('Raw JSON: ' . var_dump($this->data), 'request');
      $this->data = json_decode($this->data);
      if ($this->data == NULL) {
        Log::i('Invalid JSON Data', 'request');
        throw new Exception('JSON data cannot be encoded');
      }
    }
  }


  /**
   * Process the url route and set the required
   * request properties for use by the service
   *
   * @access public
   * @params void
   * @return void
   */
  public function process_route() {
    // explode the url path
    $params = explode('/', trim($_SERVER['PATH_INFO'], '/'));

    $class = Factory_Service::get_class($params[0]);
    if (Factory_Service::exists($class)) {

      // Class is defined
      $this->class = $class;

      // lets see if there is an id, if so operate on entity
      if (isset($params[1]) && trim($params[1]) != '') {
        $this->key = $params[1];

        // Follow a Relationship ?
        if (isset($params[2]) && trim($params[2]) != '') {
          if (Factory_Service::method_exists($class, 'has_' . strtolower($params[2]))) {
            $this->relation = strtolower($params[2]);

            // check if there are any relationship params
            if (isset($params[3]) && trim($params[3]) != '') {
              $this->relation_key = $params[3];
              switch ($this->method) {
                case 'GET':
                  $this->handler = 'get_' . $this->relation;
                  break;
                case 'PUT':
                  $this->handler = 'update_' . $this->relation;
                  break;
                case 'DELETE':
                  $this->handler = 'delete_' . $this->relation;
                  break;
                default:
                  $this->class = 'nasty_hack';
                  break;
              }
            }
            else {
              switch ($this->method) {
                case 'GET':
                  $this->handler = 'list_' . $this->relation;
                  break;
                case 'POST':
                  $this->handler = 'add_' . $this->relation;
                  break;
                default:
                  $this->class = 'nasty_hack';
                  break;
              }
            }
          }
          else {
            if (Factory_Service::method_exists($class, strtolower($params[2]))) {
              $this->handler = strtolower($params[2]);
            }
            else {
              $this->class = 'nasty_hack';
            }
            // do nothing
            // throw new ApiException('Unknown relationship');
          }
        }
        else {
          switch ($this->method) {
            case 'GET':
              // return the $id record
              $this->handler = 'get';
              break;
            case 'POST':
              if (isset($this->query['_method'])) {
                switch(strtoupper($this->query['_method'])) {
                  case 'PUT':
                    // alias for PUT, to compensate for browser limitations
                    $this->handler = 'update';
                    break;
                  case 'DELETE':
                    // alias for DELETE, to compensate for browser limitations
                    $this->handler = 'delete';
                    break;
                  default:
                    $this->class = 'nasty_hack';
                    break;
                }
              }
              else {
                $this->class = 'nasty_hack';
              }
              break;
            case 'PUT':
              $this->handler = 'update';
              break;
            case 'DELETE':
              $this->handler = 'delete';
              break;
          }
        }
      }
      else {
        // operating on collection
        switch($this->method) {
          case 'GET':
            $this->handler = 'listing';
            break;
          case 'POST':
            $this->handler = 'create';
            break;
          case 'PUT':
          case 'DELETE':
            $this->class = 'nasty_hack';
            break;
        }
      }
    }
    else {
      $this->class = 'nasty_hack';
      // Collection class does not exist
      // TODO: Handle this
    }

    Log::i('Route processed. class: ' . $this->class .
        ' key:' . $this->key .
        ' relation: ' . $this->relation .
        ' handler: ' . $this->handler, 'request');
  }


  /**
   * Returns the request start time
   *
   * @access public
   * @param void
   * @return Float
   */
  public function getStartTime() {
    return $this->start_time;
  }


  /**
   * Returns the request method. POST, PUT, GET or DELETE
   *
   * @access public
   * @param void
   * @return string
   */
  public function getMethod() {
    return $this->method;
  }


  /**
   * Returns the requested collection class
   *
   * @access public
   * @param void
   * @return String
   */
  public function getClass() {
    return $this->class;
  }


  /**
   * Returns the class handler function
   *
   * @access public
   * @params void
   * @return string
   */
  public function getHandler() {
    return $this->handler;
  }


  /**
   * Returns an array of headers
   *
   * @return array
   */
  public function getHeader($header) {
    return (isset($this->headers[$header])) ? $this->headers[$header] : NULL;
  }


  /**
   * Returns an array of the request headers
   *
   * @access public
   * @param void
   * @return array
   */
  public function getHeaders() {
    return $this->headers;
  }


  /**
   * Returns request POST/PUT data
   *
   * @access public
   * @param void
   * @return Ambiguous <array, string>
   */
  public function getData() {
    return $this->data;
  }


  /**
   * returns an array of query parameters
   *
   * @access public
   * @param void
   * @return array
   */
  public function getQuery() {
    return $this->query;
  }


  /**
   * Returns the current collection key if any
   *
   * @access public
   * @param void
   * @return array
   */
  public function getKey() {
    return $this->key;
  }


  /**
   * Returns the collection relation if any
   *
   * @access public
   * @param void
   * @return array
   */
  public function getRelation() {
    return $this->relation;
  }


  /**
   * Returns the collection relation if any
   *
   * @access public
   * @param void
   * @return array
   */
  public function getRelationKey() {
    return $this->relaton_key;
  }


  /**
   * returns an array of query parameters
   *
   * @access public
   * @param void
   * @return array
   */
  public function getQueryParam($param) {
    return (isset($this->query[$param])) ? $this->query[$param] : NULL;
  }

}
