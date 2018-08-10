<?php
/**
 * Created by PhpStorm.
 * User: erratic
 * Date: 7/31/2018
 * Time: 4:01 PM
 */

namespace pscr\lib\session;
use pscr\lib\model;

class default_session_backend implements model\i_session_backend
{

    function delete($session_id)
    {
        unset($_SESSION[$session_id]);
    }

    function store($session_id, $key, $data)
    {
        $_SESSION[$session_id] = $data;
    }

    function retrieve($session_id, $key)
    {
        return $_SESSION[$session_id];
    }
}