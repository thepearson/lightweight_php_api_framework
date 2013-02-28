<?php
class AccessControl_HttpBasicAuth implements AccessControl {

  public $username;
  public $password;


  public function __construct(Request $request, $username, $password) {
    parent::__construct($request);
    $this->username = $username;
    $this->password = $password;
  }


  public function has_access() {
    if (isset($this->request->headers['Authorization'])) {
      list($type, $b64encoded) = explode(' ', $this->request->headers['Authorization']);
      if (trim(strtolower($type)) == 'basic') {
        list($username, $password) = explode(':', base64_decode($b64encoded));
        if ($this->username == $username && $this->password == md5($password)) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }
}
