<?php
class Log_File extends Log {

  /**
   * Construct takes, filename
   *
   * @param String $file
   */
  public function __construct($file) {
    $this->file = $file;
  }

  /**
   * (non-PHPdoc)
   * @see Log::write()
   */
  public function write($data) {
    file_put_contents($this->file, $data, FILE_APPEND);
  }
}
