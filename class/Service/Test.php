<?php
class Service_Test extends Service {

  /**
   * Returns a generic web service object
   *
   * @param array $params
   * @return Object
   */
  public function create() {
    $this->response->setData(1);
    $this->response->setHeader('__CREATED', Http::Response(Http::CREATED));
    return TRUE;
  }


  /**
   * Deletes object
   *
   * @param array $params
   * @return Object
   */
  public function delete() {
    $this->response->setData(1);
    $this->response->setHeader('__NO_CONTENT', Http::Response(Http::NO_CONTENT));
    return TRUE;
  }


  /**
   * Returns a generic web service object
   *
   * @param array $params
   * @return Object
   */
  public function update() {
    $this->response->setData(1);
    $this->response->setHeader('__OK', Http::Response(Http::OK));
    return TRUE;
  }


  /**
   * Returns a generic web service object
   *
   * @param array $params
   * @return Object
   */
  public function get() {
    $this->response->setData(1);
    $this->response->setHeader('__OK', Http::Response(Http::OK));
    return TRUE;
  }


  /**
   * Returns a listing of the current collection
   *
   * @access public
   * @param void
   */
  public function listing() {
    $this->response->setData(1);
    $this->response->setHeader('__OK', Http::Response(Http::OK));
    return TRUE;
  }
}
