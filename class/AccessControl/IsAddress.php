<?php
class AccessControl_IsAddress implements AccessControl {
  public $request;
  public $addresses;

  /**
   * Class construct
   *
   * @param Request $request
   * @param unknown_type $address
   */
  public function __construct(Request $request, $address) {
    $this->request = $request;
    $this->addresses = is_array($address) ? $address : array($address);
  }


  /**
   * (non-PHPdoc)
   * @see AccessControl::has_access()
   */
  public function has_access() {
    return in_array($this->request['client'], $this->addresses);
  }
}
