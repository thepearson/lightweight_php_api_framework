<?php
/**
 * @author craigp
 * web service base class
 */
class Service {

  /**
   * Service class prefix
   *
   * @var String
   */
  const PREFIX = 'Service_';

  /**
   * The Request
   *
   * @var Request
   */
  public $request;

  /**
   * The Response
   *
   * @var Response
   */
  public $response;

  /**
   * Database mapper
   *
   * @var Mapper
   */
  public $mapper;


  /**
   * Class construct
   *
   * @param String $method
   * @param Array $params
   */
  function __construct(Request $request, Response $response) {
    $this->request = $request;
    $this->response = $response;

    // see if we are to wrap the data in a callback
    if (($callback = $this->request->getQueryParam('callback')) !== NULL) {
      $response->setCallback($callback);
    }

    if (($response->callback === NULL) && (($jsonp = $this->request->getQueryParam('jsonp')) !== NULL)) {
      $response->setCallback($jsonp);
    }
  }


  /**
   * Returns the mapper if required
   *
   * @access protected
   * @param void
   * @return Mapper
   */
  public function getMapper() {
    // ensure instance of mapper
    if (!$this->mapper instanceof Mapper) {
      $class = 'Mapper_' . $this->request->class;
      $this->mapper = new $class();
    }
    return $this->mapper;
  }


  /**
   * This will run the method
   *
   * @access public
   * @params void
   * @return void
   */
  public function run() {
    Log::i('Running service handler ' . $this->request->getHandler(), 'service');
    if (method_exists($this, $this->request->getHandler())) {
      $method = $this->request->getHandler();
      $result = $this->$method();
    }
  }


  /**
   * Return the HTTP response object
   *
   * @access public
   * @param void
   * @return Response
   */
  public function getResponse() {
    return $this->response;
  }


  /**
   * Returns a generic web service object
   *
   * @param array $params
   * @return Object
   */
  public function create() {
    $model = $this->getMapper()->getModel();
    $model = Model::fromStdClass(new $model(), $this->request->getData());
    //$model_key = $this->getMapper()->getKey();
    if ($this->getMapper()->load($model->{$this->getMapper()->getKey()}) === NULL) {
      $model = $this->getMapper()->save($model);
      if ($model !== NULL) {
        $this->response->setData($model);
        $this->response->setHeader('__CREATED', Http::Response(Http::CREATED));
        return TRUE;
      }
    }
    $this->response->setHeader('__NOT_FOUND', Http::Response(Http::NOT_FOUND));
    return FALSE;
  }


  /**
   * Deletes object
   *
   * @param array $params
   * @return Object
   */
  public function delete() {
    // check if the key is set in the request
    if ($this->request->getKey() !== NULL) {
      if ($this->getMapper()->delete($this->request->getKey())) {
        $this->response->setHeader('__NO_CONTENT', Http::Response(Http::NO_CONTENT));
        return TRUE;
      }
    }
    $this->response->setHeader('__NOT_FOUND', Http::Response(Http::NOT_FOUND));
    return FALSE;
  }


  /**
   * Returns a generic web service object
   *
   * @param array $params
   * @return Object
   */
  public function update() {

    // first ensure we are actually updating a valid id
    if ($existing = $this->getMapper()->load($this->request->getKey()) !== NULL) {

      // get some variables that we need to make the query
      $model = $this->getMapper()->getModel();
      $key = $this->getMapper()->getKey();

      // populate the model from the passed in stdClass
      $model = Model::fromStdClass(new $model(), $this->request->getData());

      // If the model is passed in without the key, we know it's
      // for the key defined in the URL so lets use it.
      if (!isset($model->{$key})) {
        $model->{$key} = $this->request->getKey();
      }

      // make sure that the model passed in and the url key are the same
      if ($this->request->getKey() == $model->{$key}) {
        $response = $this->getMapper()->save($model);
        if ($response !== NULL) {
          $this->response->setData($response);
          $this->response->setHeader('__OK', Http::Response(Http::OK));
          return TRUE;
        }
      }
      else {
        $this->response->setHeader('__UNPROCESSABLE_ENTITY', Http::Response(Http::UNPROCESSABLE_ENTITY));
        return FALSE;
      }
    }
    $this->response->setHeader('__NOT_FOUND', Http::Response(Http::NOT_FOUND));
    return FALSE;
  }


  /**
   * Returns a generic web service object
   *
   * @param array $params
   * @return Object
   */
  public function get() {
    // ensure that the request key is set
    if ($this->request->getKey() !== NULL) {

      // get the model from db
      $model = $this->getMapper()->load($this->request->getKey());

      if (isset($model)) {
        $this->response->setData($model);
        $this->response->setHeader('__OK', Http::Response(Http::OK));
        return TRUE;
      }
    }
    $this->response->setHeader('__NOT_FOUND', Http::Response(Http::NOT_FOUND));
    return FALSE;
  }


  /**
   * Returns a listing of the current collection
   *
   * @access public
   * @param void
   */
  public function listing() {

    $this->getMapper()->tableExists();

    // process any paging params
    $offset = 0;
    $limit = 10;
    $order = $this->getMapper()->getKey();
    $asc = TRUE;

      // process any offset requirements
    if ($this->request->getQueryParam('offset') !== NULL && is_numeric($this->request->getQueryParam('offset'))) {
      $offset = $this->request->getQueryParam('offset');
    }

    // process any limit requirements
    if ($this->request->getQueryParam('limit') !== NULL && is_numeric($this->request->getQueryParam('limit'))) {
      $limit = $this->request->getQueryParam('limit');
    }

    // process any order parameters
    if ($this->request->getQueryParam('order') !== NULL) {
      if (property_exists($this->mapper->getModel(), $this->request->getQueryParam('order'))) {
        $order = $this->request->getQueryParam('order');
      }
    }

    // process any asccending/decending requirements
    if ($this->request->getQueryParam('desc') !== NULL) {
      $asc = FALSE;
    }

    // ensure we arent passing in keys in query params ?keys=1,2,3,4 etc
    if ($this->request->getQueryParam('keys') !== NULL) {
      // List by id
      $result = $this->getMapper()->load_multiple(explode(',', $this->request->getQueryParam('keys')), $offset, $limit, $order, $asc);
      $data = $result['list'];
      $total = $result['total'];
    }
    else {
      // List all
      $result = $this->getMapper()->listing(NULL, $offset, $limit, $order, $asc);
      $data = $result['list'];
      $total = $result['total'];
    }

    // set the data
    $this->response->setMeta(array(
      'order' => $order,
      'offset' => $offset,
      'limit' => $limit,
      'direction' => ($asc == TRUE) ? 'ASC' : 'DESC',
      'count' => count($data),
      'total' => $total
    ));
    $this->response->setData($data);

    // Set response
    if (!empty($data)) {
      $this->response->setHeader('__OK', Http::Response(Http::OK));
      return TRUE;
    }
    $this->response->setHeader('__NOT_FOUND', Http::Response(Http::NOT_FOUND));
    return TRUE;
  }
}
