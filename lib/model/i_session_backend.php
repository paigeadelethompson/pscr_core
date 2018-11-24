<?php
/**
 * Created by PhpStorm.
 * User: erratic
 * Date: 7/31/2018
 * Time: 3:53 PM
 */

namespace pscr\lib\model;


interface i_session_backend
{
    function delete($session_id);
    function store($session_id, $key, $data);
    function retrieve($session_id, $key);
    function close();
}
