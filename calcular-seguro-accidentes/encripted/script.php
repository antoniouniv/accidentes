<?php

class SymmetricEncryption
{
    private $cipher;
    private $salt;
    private $password;
    private $init_vector;
    private $removable_fields;

    public function __construct($cipher = 'aes-128-cbc')
    {
        $this->cipher = $cipher;
        //Read file origin config data
        $xml = simplexml_load_file($_SERVER['DOCUMENT_ROOT'] . '/calcular-seguro-accidentes/encripted/CT_ConfigQnB.xml');

        $this->salt = $xml->TemplateInvocation[2]->TemplateParameters->Parameters->salt;//"SALT_VALUE";
        $this->password = $xml->TemplateInvocation[2]->TemplateParameters->Parameters->password; //"PASSWORD_VALUE";
        $this->init_vector = $xml->TemplateInvocation[2]->TemplateParameters->Parameters->init_vector; //"IV_VALUE_16_BYTE";

        //Removable fields (not apply encrypt/decrypt)
        $this->removable_fields = explode(",", $xml->TemplateInvocation[3]->TemplateParameters->Parameters->removable_fields);
    }

    private function getKeySize()
    {
        if (preg_match("/([0-9]+)/i", $this->cipher, $matches)) {
            return $matches[1] >> 3;
        }
        return 0;
    }

    private function derived()
    {
        $AESKeyLength = $this->getKeySize();
        $AESIVLength = openssl_cipher_iv_length($this->cipher);

        $pbkdf2 = hash_pbkdf2("SHA1", $this->password, mb_convert_encoding($this->salt, 'UTF-16LE'), 65536, $AESKeyLength + $AESIVLength, TRUE);
        $derived = new stdClass();
        $derived->key = substr($pbkdf2, 0, $AESKeyLength);
        $derived->iv = $this->init_vector;//substr($pbkdf2, $AESKeyLength, $AESIVLength);//$this->init_vector;

        return $derived;
    }

    function encrypt($message)
    {
        $derived = $this->derived();
        $enc = openssl_encrypt(mb_convert_encoding($message, 'UTF-16', 'UTF-8'), $this->cipher, $derived->key, NULL, $derived->iv);
        return $enc;
    }

    function decrypt($message)
    {
        $derived = $this->derived();
        $dec = openssl_decrypt($message, $this->cipher, $derived->key, NULL, $derived->iv);
        return mb_convert_encoding($dec, 'UTF-8', 'UTF-16');
    }
}


?>