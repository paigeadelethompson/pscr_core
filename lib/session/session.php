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

use pscr\lib\exceptions\not_implemented_exception;

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
        return $this->backend->retrieve($this->cookies['pscr_session_id'], $key);
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
            //logger::_()->info($this, $this->cookies);
            $this->cookies = array("pscr_session_id" => hash('sha256', rand() . time()));
            logger::_()->info($this, "new session", $this->cookies);
            $this->pscr_session_id = $this->cookies['pscr_session_id'];
        }
        else if($this->backend->retrieve($this->cookies['pscr_session_id'], 'pscr_session_id') == null)
        {
            logger::_()->info($this, "session timed out, creating new", $this->cookies);
            $this->cookies = array("pscr_session_id" => hash('sha256', rand() . time()));
            logger::_()->info($this, "new cookies", $this->cookies);
            $this->pscr_session_id = $this->cookies['pscr_session_id'];
        }
        else
        {
            logger::_()->info($this, "session already exists", $this->cookies['pscr_session_id']);
        }
    }

    function send_cookies_to_client() {
        foreach($this->cookies as $key => $value) {
            logger::_()->info($this, "sendig cookie", $key, $value);
            setcookie($key, $value, time()+3600);
        }
    }

    function authenticated() {
        if($this->authenticated == false)
            return false;
        return true;
    }

    function unauthenticated_one_way_encrypt($key, $value) {
        $this->_one_way_encrypt($key, $value, ($this->salt) ? $this->salt : $this->settings->salt);
        return $this;
    }

    function unauthenticated_two_way_encrypt($key, $value) {
        if(!openssl_public_encrypt($value, $crypted, $this->settings->server_pub_key))
            throw new \Exception("failed to encrypt value");

        $this->backend->store($this->cookies['pscr_session_id'],
                              $key,
                              $crypted);
        return $this;
    }

    function one_way_encrypt($key, $value) {
        if($this->authenticated()) {
            $this->_one_way_encrypt($key, $value, $this->salt);
            return $this;
        }
        else
        {
            throw new \Exception("can't one_way_encrypt not authenticated");
        }
    }

    function two_way_encrypt($key, $value) {
        if($this->authenticated()) {
            if(!openssl_public_encrypt($value, $crypted, $this->settings->user_pub_key))
                throw new \Exception("failed to encrypt value");
        }
        else {
            throw new \Exception("can't two-way encrypt not authenticated");
        }
    }

    function set_private_key($value) {
        $this->backend->store($this->cookies['pscr_session_id'],
                              'user_priv_key',
                              $value);
        return $this;
    }

    function set_public_key($value) {
        $this->backend->store($this->cookies['pscr_session_id'],
                              'user_pub_key',
                              $value);
        return $this;
    }

    function generate_two_way_key_pair() {
        $key_pair = $this->settings->generate_key_pair();
        $this->set_private_key($key_pair['priv_key'])
            ->set_public_key($key_pair['pub_key']);
        return $this;
    }

    function generate_salt() {
        $this->salt = $this->settings->generate_salt();
        return $this;
    }

    function retrieve_salt() {
        return $this->salt;
    }
    
    function unauthenticated_decrypt($key) {
        return $this->_decrypt($this->server_priv_key, $key);
    }

    function decrypt($key) {
        if($this->authenticated())
            return $this->_decrypt($this->user_priv_key, $key);
    }

    private function _decrypt($encrpytion_key, $key) {
        $crypted = $this->backend->retrieve($this->cookies['pscr_session_id'], $key);
        if($crypted == null) {
            logger::_()->info($this, "requested encrypted value is null", $key);
            return null;
        }
        if(openssl_private_decrypt($crypted, $decrypted, $encryption_key))
            return $decrypted;
        else
            throw new \Exception("failed to decrypt value");
    }

    private function _one_way_encrypt($key, $value, $salt) {
        $options = array(
            'cost' => $this->settings->cost,
        );

        if($salt == null) {
            throw new \Exception('no salt set');
        }

        $this->backend->store($this->cookies['pscr_session_id'],
                              $key,
                              password_hash($value . $salt,
                                            PASSWORD_BCRYPT,
                                            $options));
    }
}
