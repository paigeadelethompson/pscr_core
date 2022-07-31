<?php
/*
 * Author: Paige A. Thompson (paigeadele@gmail.com)
 * Copyright (c) 2018, Netcrave Communications
 * All rights reserved.
 *
 *
 * Author: Trevor A. Thompson (trevorat@gmail.com)
 * Copyright (c) 2007, Progressive Solutions Inc.
 * All rights reserved.
 *
 * - Redistribution and use of this software in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 * Redistributions of source code must retain the above
 * copyright notice, this list of conditions and the
 * following disclaimer.
 *
 * - Redistributions in binary form must reproduce the above
 * copyright notice, this list of conditions and the
 * following disclaimer in the documentation and/or other
 * materials provided with the distribution.
 *
 * - Neither the name of Progressive Solutions Inc. nor the names of its
 * contributors may be used to endorse or promote products
 * derived from this software without specific prior
 * written permission of Progressive Solutions Inc.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A
 * PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR
 * TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace pscr\lib\logging;

use pscr\lib\configuration\logger_settings;
use pscr\lib\logging\php_default_logging;

use pscr\lib\exceptions\pscr_exception;

/**
 * Class logger
 * @package pscr\lib\logging
 */
class logger
{
    /**
     * @var
     */
    private static $instance;
    /**
     * @var logger_settings
     */
    private $settings;

    /**
     * @var array
     */
    private $data;

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
    	if($name == "_logger") {
            if(!array_key_exists($name, $this->data)) {
                if(php_sapi_name() == "cli")
                {
                    $this->data["_logger"] = new \pscr\lib\logging\php_default_logging();
                }
                else {
                    $this->data["_logger"] = new $this->settings->type();
                }
			}
			return $this->data["_logger"];
		}
        else if(array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        else {
            throw new InvalidArgumentException($name);
        }
    }

    /**
     * @return logger
     */
    public static function _()
    {
        if (self::$instance === null) {
            self::$instance = new logger();
        }
        return self::$instance;
    }

    /**
     * logger constructor.
     */
    private function __construct()
    {
    	$this->data = array();
    	$this->settings = new logger_settings();
    	//error_reporting($this->settings->php_error_reporting_level);
        error_reporting(E_ALL);
        set_error_handler(function($error_number, $error_string, $error_file, $error_line) {
            switch($error_number) {
                case E_USER_NOTICE:
                case E_NOTICE:
                    logger::_()->info("PHP set_error_handler", array(
                        "message" => $error_string,
                        "file" => $error_file,
                        "line" => $error_line));
                    break;
                case E_USER_WARNING:
                case E_WARNING:
                    logger::_()->warn("PHP set_error_handler", array(
                        "message" => $error_string,
                        "file" => $error_file,
                        "line" => $error_line));
                    break;
                case E_USER_ERROR:
                //case E_FATAL:
                case E_ERROR:
                    logger::_()->error("PHP set_error_handler", array(
                        "message" => $error_string,
                        "file" => $error_file,
                        "line" => $error_line));
                    break;
                default:
                    logger::_()->warn("PHP set_error_handler (unspecified error type)", array(
                        "message" => $error_string,
                        "file" => $error_file,
                        "line" => $error_line));
            }
        });
    }

    /**
     * @param $array
     * @return string
     */
    function get_log_string($array) {
        $ret = "";
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $ret .= ' ' . print_r($value, true);
            }
           else  if(is_object($value))
            {
                $ret .= ' ' . print_r($value, true);
            }
            else {
                $ret .= ' ' . $value;
            }
        }
        return $ret;
    }

    /**
     * @param $sender
     * @param mixed ...$params
     */
    public function info($sender, ...$params) {
        $from = "";
        if(is_object($sender))
        {
            $from = get_class($sender);
        }
        if(is_string($sender))
        {
            $from .= $sender;
        }
        $from .= '() -> ';
        if($this->settings->log_info_messages) {
            $this->_logger->log($from . $this->get_log_string($params), E_USER_NOTICE);
		}
	}

    /**
     * @param $sender
     * @param mixed ...$params
     */
    public function warn($sender, ...$params) {
        $from = "";
        if(is_object($sender))
        {
            $from = get_class($sender);
        }
        $from .= '() -> ';
        if($this->settings->log_warn_messages) {
            $this->_logger->log($from . $this->get_log_string($params), E_USER_WARNING);
        }
	}

    /**
     * @param $sender
     * @param mixed ...$params
     */
    public function error($sender, ...$params) {
        $from = "";
        if(is_object($sender))
        {
            $from = get_class($sender);
        }
        $from .= '() -> ';
        if($this->settings->log_error_messages) {
            $this->_logger->log($from . $this->get_log_string($params), E_USER_ERROR);
        }
        throw new pscr_exception("logged error");
	}
}

?>
