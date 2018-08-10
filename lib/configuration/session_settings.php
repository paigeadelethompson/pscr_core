<?php
/**
 * Created by PhpStorm.
 * User: erratic
 * Date: 7/31/2018
 * Time: 3:52 PM
 */

namespace pscr\lib\configuration;

use pscr\lib\exceptions\invalid_argument_exception;
use pscr\lib\logging\logger;
use pscr\lib\model\pscr_settings;

class session_settings extends pscr_settings
{
    function __construct() {
        $this->data = array();
        $section = get_called_class();
        $data = parse_ini_file(PSCR_PROJECT_ROOT . './settings/settings.ini', true);

        if(array_key_exists($section, $data)) {
            $this->data = new \ArrayObject($data[$section], \ArrayObject::ARRAY_AS_PROPS);
        }
        else {
            logger::_()->info($this, "missing session settings section in settings.ini");
        }
    }
}