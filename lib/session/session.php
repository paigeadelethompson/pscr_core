<?php
/**
 * Created by PhpStorm.
 * User: erratic
 * Date: 7/31/2018
 * Time: 3:52 PM
 */

namespace pscr\lib\session;

use pscr\lib\configuration\session_settings;
use pscr\lib\logging\logger;
use pscr\lib\settings;


class session
{
    private $cookies;
    private $backend;
    private $settings;
    private static $instance;

    public static function _() {
        if(session::$instance == null)
            session::$instance = new session();
        return session::$instance;
    }

    function __get($key) {
        $this->backend->retrieve($this->cookies['pscr_session_id'], $key);
    }

    function __set($key, $value) {
        $this->backend->store($this->cookies['pscr_session_id'], $key, $value);
    }

    private function __construct() {
        $this->settings = new session_settings();
        $this->cookies = $_COOKIE;
        $this->backend = new $this->settings->type();

        if(!array_key_exists("pscr_session_id", $this->cookies))
        {
            logger::_()->info($this, $this->cookies);
            $this->cookies = array("pscr_session_id" => hash('sha256', rand() . time()));
            logger::_()->info($this, "new session", $this->cookies);
        }
        else
        {
            logger::_()->info($this, "session already exists", $this->cookies['pscr_session_id']);
        }
    }

    function send_cookies_to_client() {
        foreach($this->cookies as $key => $value) {
            logger::_()->info($this, "sending existing cookie", $key, $value);
            setcookie($key, $value, time()+3600);
        }
    }

    function authenticated() {
        if($this->authenticated == null)
            return false;

        return $this->authenticated;
    }
}
