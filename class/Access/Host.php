<?php
class Access_Host extends Access {

  public function __construct(Request $request) {
    parent::__construct($request);

    // if you are coming from the web server, then give access
    
    print_r($request); exit;
  }

}
