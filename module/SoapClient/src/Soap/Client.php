<?php

namespace SoapClient\Soap;

use Laminas\Soap\Client as SoapClient;

class Client extends SoapClient
{
    public function __construct($uri = null, $options = null)
    {
        $uri = 'http://localhost/pi2_Lab2_soap/public/soap-server/wsdl';

        parent::__construct($uri, $options);
    }


}