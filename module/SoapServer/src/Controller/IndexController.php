<?php

namespace SoapServer\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Soap;
use SoapServer\Service\Movies;

ini_set("soap.wsdl_cache_enabled", 0);

class IndexController extends AbstractActionController
{

    public function __construct(public Movies $movies)
    {
    }

    public function indexAction()
    {
        $serwer = new Soap\Server('http://localhost/pi2_Lab2_soap/public/soap-server/wsdl');
        $serwer->setObject($this->movies);
        $serwer->handle();

        return $this->getResponse();
    }

    public function wsdlAction()
    {
        $autodiscover = new Soap\AutoDiscover();
        $autodiscover->setClass(Movies::class);
        $autodiscover->setUri('http://localhost/pi2_Lab2_soap/public/soap-server');
        $autodiscover->handle();

        return $this->getResponse();
    }
}
