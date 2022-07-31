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

namespace pscr\lib\model;

use pscr\lib\logging\logger;
use pscr\lib\exceptions\invalid_argument_exception;

/**
 * Class pscr_settings
 * @package pscr\lib\model
 */
abstract class pscr_settings
{
    /**
     * @var array
     */
    protected $data;

    /**
     * pscr_settings constructor.
     */
    public function __construct() {
        $section = get_called_class();
        $data = parse_ini_file(PSCR_PROJECT_ROOT . './settings/settings.ini', true);
        if(array_key_exists($section, $data)) {
            $this->data = $data[$section];
        }
        else {
            $this->data = array();
            logger::_()->info($this,"cant find settings section in settings.ini", $section);
        }
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        throw new invalid_argument_exception("settings is readonly");
    }

    /**
     * @param $name
     * @return mixed
     * @throws invalid_argument_exception
     */
    public function __get($name)
    {
       if(isset($this->data[$name])) {
           if(in_array($name, $this->data)) {
              return new \ArrayObject($this->data[$name],
                                     \ArrayObject::ARRAY_AS_PROPS);
           }
            return $this->data[$name];
        }
        else {
           throw new invalid_argument_exception();
        }
    }
}
