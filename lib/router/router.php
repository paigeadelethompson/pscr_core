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

namespace pscr\lib\router;


use pscr\lib\configuration\router_settings;
use pscr\lib\exceptions\invalid_argument_exception;
use pscr\lib\exceptions\not_implemented_exception;
use pscr\lib\exceptions\route_not_found_exception;
use pscr\lib\http\request;
use pscr\lib\logging\logger;
use pscr\lib\model\i_router;

/**
 * Class router
 * @package pscr\lib\router
 */
class router implements i_router
{
    /**
     * @var
     */
    private static $instance;
    /**
     * @var router_settings
     */
    private $settings;
    /**
     * @var
     */
    private $renderer;
    /**
     * @var
     */
    private $parent;
    /**
     * @var
     */
    private $selected_route;
    /**
     * @var
     */
    private $request;

    /**
     * @return router
     */
    public static function _()
    {
        if (self::$instance === null) {
            self::$instance = new router();
        }
        return self::$instance;
    }

    /**
     * router constructor.
     * @param string $routes_file
     */
    private function __construct($routes_file = PSCR_PROJECT_ROOT . './settings/routes.ini')
    {
        $this->settings = new router_settings($routes_file);
    }

    /**
     * @param $router
     * @return $this
     */
    private function set_parent($router) {
        $this->parent = $router;
        return $this;
    }

    /**
     * @param $route
     * @return $this
     */
    private function set_selected($route) {
        if($this->parent != null)
            $this->selected_route = array($this->parent->get_selected_route(), $route);
        else
            $this->selected_route = $route;
        return $this;
    }

    /**
     * @return string
     */
    function get_selected_route_entry_file_name() {
        $path = $this->get_selected_route()[0][1]['root_dir'] . '/';
        $path .= $this->get_selected_route()[1][1]['entry'];
        return PSCR_PROJECT_ROOT . $path;
    }

    /**
     * @return mixed
     */
    function get_selected_route_entry_class_name() {
        return $this->get_selected_route()[1][0];
    }

    /**
     * @return string
     */
    function get_selected_route_entry_path_name() {
        $path = $this->get_selected_route()[0][1]['root_dir'] . '/';
        $path .= $this->get_selected_route()[1][1]['path'];
        return PSCR_PROJECT_ROOT . $path;
    }

    /**
     * @return mixed
     */
    function get_selected_route_match() {
        return $this->get_selected_route()[1][2][0];
    }

    /**
     * @return mixed
     */
    function get_selected_route() {
        return $this->selected_route;
    }

    /**
     * @return mixed
     */
    function get_selected_route_content_type() {
        return $this->get_selected_route()[1][1]['content_type'];
    }


    /**
     * @param $renderer
     * @return $this
     */
    private function create_renderer($renderer) {
        $this->request = new request($this);
        $this->renderer = new $renderer();
        $this->renderer->set_request($this->request);
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_renderer() {
        return $this->renderer;
    }

    /**
     * @param $uri
     * @return mixed
     * @throws invalid_argument_exception
     * @throws route_not_found_exception
     */
    function get_route($uri) {
        $ret = "";
        //preg_match("/404/", $uri, $matches);
        //die(print_r(array($matches, $uri)));
        foreach($this->settings->get_settings_array() as $key => $value) {
            if(array_key_exists("url_match", $value)) {
                $matches = array();
                preg_match($value['url_match'], $uri, $matches);

                if(count($matches) > 0) {

                    logger::_()->info($this, "found matches for route", $uri, $matches);

                    // if route entry has root_dir property, then it should have a routes.ini file.
                    if(array_key_exists('root_dir', $value)) {
                        // set the route entry name as the selected route for this router
                        $this->set_selected(array($key, $value));
                        // create new router instance with app/sub-projects route.ini, set $this as it's parent, and return the return value of get_route
                        // if another route is found who's regex matches the uri it will be over-written.
                        $ret = (new router(PSCR_PROJECT_ROOT . $value['root_dir'] . '/./settings/routes.ini'))->set_parent($this);
                    }
                    // if this route entry has a property named type
                    else if(array_key_exists('type', $value)) {
                        // if the value of type is a class
                        if(class_exists($value['type'])) {
                            // set the current route as the selected route and create a renderer instance from the value of type
                            $ret = $this->set_selected(array($key, $value, $matches))->create_renderer($value['type']);
                        }
                        else {
                            throw new invalid_argument_exception("invalid class specified in route");
                        }
                    }
                    else {
                        throw new invalid_argument_exception("no class type specified for route");
                    }
                }
                else {
                    // regex does not match
                    continue;
                }
            }
            else {
                throw new invalid_argument_exception("no url_match (regex) specified for route");
            }
        }
        // if we have a content renderer then return
        if(is_a($this->renderer, "pscr\\lib\\model\\i_content_renderer")) {
            return $ret;
        }
        // if we got another router, return the return value of get_route with the expectation that it will return
        // a content renderer or throw a route not found exception.
        else if(get_class($ret) == "pscr\\lib\\router\\router") {
            return $ret->get_route($uri);
        }
        // no route was found for the given path (no regexes matched.)
        else {
            throw new route_not_found_exception();
        }
    }
}
