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
    public function __get($name)
    {
        if($name == 'server_priv_key' || $name == 'server_pub_key')
        {
            if(array_key_exists($name, $this->data)) {
                return $this->data[$name];
            }
            else {
                $this->retrieve_unauthenticated_key_pair();
                return $this->data[$name];
            }
        }
        else if($this->data->offsetExists($name)) {
            return $this->data[$name];
        }
        else {
            throw new invalid_argument_exception();
        }
    }

    public function generate_key_pair() {
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );

        logger::_()->info($this, "generating unauthenticated key pair", $options);

        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $priv_key);
        $pub_key = openssl_pkey_get_details($res)['key'];
        return array('pub_key' => $pub_key, 'priv_key' => $priv_key);
    }

    private function retrieve_unauthenticated_key_pair() {
        if(isdir("/tmp/pscr")) {
            $files = glob("/tmp/pscr/server_*_key");
        }

        if(isset($files) && is_array($files)) {
            die(print_r($files));
        }

        $key_pair = $this->generate_key_pair();

        $this->data['server_priv_key'] = str_replace("\n", "", $key_pair['priv_key']);
        $this->data['server_pub_key'] = str_replace("\n", "", $key_pair['pub_key']);

        $priv_key_file = tempnam("/tmp/pscr", "priv_key");
        $pub_key_file = tempnam("/tmp/pscr", "pub_key");

        file_put_contents($priv_key_file, $privKey);
        file_put_contents($pub_key_file, $pubKey);

        logger::_()->info($this, "keypair saved to tmp files", $priv_key_file, $pub_key_file);
    }

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
