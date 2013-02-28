<?php
class Response {


  /**
   * Any data returned from the service
   *
   * @var $Mixed
   */
  public $body;


  /**
   * Array of HTTP headers
   *
   * @var array
   */
  public $headers;


  /**
   * Is this a JSONP callback
   *
   * @var string
   */
  public $callback = NULL;


  /**
   * Allow service to set meta information
   *
   * @access protected
   * @var Array
   */
  public $meta = NULL;


  /**
   * Data to be returned
   *
   * @access protected
   * @var Array
   */
  public $data = NULL;


  /**
   * Respond to the client
   *
   * @access public
   * @param String $format
   * @return void
   */
  public function respond($format = 'json') {
    if (isset($this->meta)) {
      $data = array(
        'meta' => $this->meta,
        'objects' => isset($this->data) ? $this->data : NULL
      );
    }
    else {
      $data = isset($this->data) ? $this->data : NULL;
    }

    switch ($format) {
      case 'json':
      default:
        $this->setHeader('Content-Type', 'application/json');
        $data = json_encode($data);
        break;
    }

    $this->outputHeaders();
    if ($this->data !== NULL) {
      if ($this->callback !== NULL) {
        print $this->callback . '(' . $data . ')';
      }
      else {
        print $data;
      }
    }
  }


  /**
   * Set a response HTTP header
   *
   * @param String $header
   * @param String $value
   * @param Boolean $append
   * @return boolean
   */
  public function setHeader($header, $value, $append = FALSE) {
    if ($append != FALSE) {
      if (array_key_exists($header, $this->headers)) {
        $this->headers[$header] = $this->headers[$header] . ',' . $value;
        return TRUE;
      }
    }
    $this->headers[$header] = $value;
    return TRUE;
  }


  /**
   * Output raw headers to the client
   *
   * @return void
   */
  public function outputHeaders() {
    foreach ($this->headers as $header => $value) {
      if (substr($header, 0, 2) == '__') {
        header($value);
      }
      else {
        header($header . ': ' . $value);
      }
    }
  }


  /**
   * Get the callback
   *
   * @return string
   */
  public function getCallback() {
    return $this->callback;
  }


  /**
   * Get the http headers
   *
   * @return multitype:
   */
  public function getHeaders() {
    return $this->headers;
  }


  /**
   * Set a JSONP callback
   *
   * @access public
   * @param string $callback
   * @return Void
   */
  public function setCallback($callback) {
    $this->callback = $callback;
  }


  /**
   * Set the data response
   *
   * @access public
   * @param Mixed $data
   * @param String $format
   * @return $Mixed
   */
  public function setData($data) {
    $this->data = $data;
  }


  /**
   * Set the meta for response, used in listings where we may
   * require information about paging etc
   *
   * @access public
   * @param Array $meta
   * @return void
   */
  public function setMeta($meta) {
    $this->meta = $meta;
  }


  /**
   * Get the data
   *
   * @return $Mixed
   */
  public function getData() {
    return $this->data;
  }
}
