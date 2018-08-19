<?php
/**
 * Created by PhpStorm.
 * User: erratic
 * Date: 7/31/2018
 * Time: 4:01 PM
 */

namespace pscr\lib\session;
use pscr\lib\model;
use pscr\lib\logging\logger;

class default_session_backend implements model\i_session_backend
{
    function __construct() {
        session_start();
        logger::_()->info($this, "php session", session_id());
    }

    function delete($session_id)
    {
        unset($_SESSION[$session_id]);
    }

    function store($session_id, $key, $data)
    {
        logger::_()->info($this, "storing session var", $session_id, $key, $data);
        $_SESSION[$session_id][$key] = $data;
    }

    function retrieve($session_id, $key)
    {
        logger::_()->info($this, "retrieving session var", $session_id, $key);
        return $_SESSION[$session_id][$key];
    }
}
