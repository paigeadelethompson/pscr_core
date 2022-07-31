<?php

namespace pscr\lib\logging;
use pscr\lib\model\i_log_writer;


/**
 * Class php_default_logging
 * @package pscr\lib\logging
 */
class stderr_log_writer implements i_log_writer
{
    private $out_buf;
  
    function __construct() {
        if(!file_exists("/dev/stderr")) {
            die("need /dev/stderr for this logging extension.");
        }
        $this->out_buf = fopen("/dev/stderr", "a+");
    }
  
    /**
     * @param $msg
     * @param $level
     */
    public function log($msg, $level)
    {
        fwrite($this->out_buf, $level . " : " . $msg . " \n");
    }

    function __destruct() {
        fclose($this->out_buf);
    }
}
