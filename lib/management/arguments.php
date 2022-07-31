<?php

use pscr\lib\exceptions\not_implemented_exception;

namespace pscr\lib\management;

use pscr\lib\model\i_argument_parser;

/**
 * Class arguments
 * @package pscr\lib\management
 */
class arguments implements i_argument_parser {

    /**
     * @param $caller
     * @param $arguments
     */
    function usage($caller, $arguments)
    {
        throw new not_implemented_exception();
    }

    /**
     * @param $caller
     * @param $arguments
     */
    function execute($caller, $arguments)
    {
        $module_classname = 'pscr\\lib\\management\\modules\\' . $arguments[0];
        \pscr\lib\logging\logger::_()->info($this, "management", $module_classname);
        if (class_exists($module_classname)) {
            if (is_a(new $module_classname(), 'pscr\lib\model\i_argument_parser')) {
                $module = (new $module_classname());
                $module->execute($caller, array_slice($arguments, 1));
            }
        }
    }
}

?>
