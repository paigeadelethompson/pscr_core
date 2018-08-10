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

namespace pscr\lib\http;

use pscr\lib\exceptions\invalid_argument_exception;
use pscr\lib\logging\logger;

/**
 * Class request
 * @package pscr\lib\http
 */
class request {

    /**
     * @var
     */
    private $router;
    /**
     * @var
     */
    private $data;

    /**
     * @param $name
     * @param $value
     * @throws invalid_argument_exception
     */
    public function __set($name, $value) {
        throw new invalid_argument_exception("can't set properties on request object, read only");
    }

    /**
     * @param $name
     * @return null
     */
    public function __get($name) {
        if(array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        else {
            return null;
        }
    }

    /**
     * request constructor.
     * @param $router
     */
    function  __construct($router) {
        $this->data = $_REQUEST;
        $this->router = $router;
    }

    /**
     * @return mixed
     */
    function get_selected_route_entry_file_name() {
        return $this->router->get_selected_route_entry_file_name();
    }

    /**
     * @return mixed
     */
    function get_selected_route_entry_class_name() {
        return $this->router->get_selected_route_entry_class_name();
    }

    /**
     * @return mixed
     */
    function get_selected_route_entry_path_name() {
        return $this->router->get_selected_route_entry_path_name();
    }

    /**
     * @return mixed
     */
    function get_selected_route_match() {
        return $this->router->get_selected_route_match();
    }

    /**
     * @return mixed
     */
    function get_selected_route_content_type() {
        return $this->router->get_selected_route_content_type();
    }
}