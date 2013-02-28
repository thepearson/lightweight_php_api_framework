<?php
abstract class Log {

  abstract public function write($data);



  public static function i($message, $route) {
    $log = new Log_File('/tmp/hostplot.log');
    $log->Log($_SERVER['REMOTE_ADDR'] . ' ' . $message, $route);
  }

  /**
   * Write the log
   *
   * @param String $message
   * @param String $level
   */
  public function Log($message, $route, $level = 'debug') {
    $this->write(strtoupper($level) . ' ' . $route . ' ' . $message . PHP_EOL);
  }
}
