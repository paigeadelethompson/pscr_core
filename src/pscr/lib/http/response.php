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

use pscr\lib\logging\logger;
use pscr\lib\exceptions\not_implemented_exception;
use pscr\lib\session\session;

/**
 * Class response
 * @package pscr\lib\http
 */
class response
{
    /**
     * @var array
     */
    private $headers;
    /**
     * @var
     */
    private $request;
    /**
     * @var
     */
    private $response_body;

    /**
     * response constructor.
     * @param $request
     */
    function __construct($request)
    {
        $this->headers = array();
        $this->request = $request;
    }

    /**
     * @param $key
     * @param $value
     */
    function set_header($key, $value) {
        $this->headers[$key] = $value;
    }

    /**
     *
     */
    function send_to_client() {
        $this->send_headers_to_client();
        print $this->response_body;
    }

    /**
     *
     */
    function send_headers_to_client() {
        session::_()->send_cookies_to_client();
        foreach($this->headers as $key => $value) {
            if($key == "Location")
            {
                logger::_()->info($this, "sending header", array($key, $value));
                http_response_code($value[1]);
                header($key . ':' . $value[0]);
            }
            else {
                logger::_()->info($this, "sending header", array($key, $value));
                header($key . ':' . $value);
            }
        }
    }

    /**
     * @param $content
     */
    function set_response_body($content) {
        $this->response_body = $content;
    }

    /**
     * @return array
     */
    function get_headers() {
        return $this->headers;
    }

    /**
     * @return mixed
     */
    function get_body() {
        return $this->content_body;
    }

    function add_redirect($location, $permanent=false) {
        $this->headers["Location"] = array($location, ($permanent) ? 301 : 302);
    }

    function get_header($key)
    {
        if(isset($this->headers[$key]))
            return $this->headers[$key];
        logger::_()->info($this, "requested header isn't set", $key);
        return null;
    }

    function enable_error_response()
    {
        http_response_code(500);
    }

    function enable_not_found_response()
    {
        http_response_code(404);
    }
}
