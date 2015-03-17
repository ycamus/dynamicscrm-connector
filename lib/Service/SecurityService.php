<?php

use \Exception;

/**
 * Class SecurityService
 *
 * @package Sixdg\DynamicsCRMConnector\Service
 */
class SecurityService
{
    /**
     * @var connector\lib\Helper\SoapRequester
     */
    private $soapRequester;

    /**
     * @param RequestBuilder $requestBuilder
     * @param SoapRequester  $soapRequester
     */
    public function __construct(
        RequestBuilder $requestBuilder,
        SoapRequester $soapRequester
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->soapRequester = $soapRequester;
    }

    /**
     * @param string $adfs         Url of adfs server
     * @param string $crm          Ip address of crm server
     * @param string $discoveryUrl Url of discovery server
     * @param string $username     Username to connect to CRM
     * @param string $password     Password to connect to CRM
     *
     * @return array|bool
     * @throws \Exception
     */
    public function login($adfs, $crm, $discoveryUrl, $username, $password)
    {
        $requestBuilder = $this->requestBuilder->reset();

        $request = $requestBuilder->setServer($adfs)
            ->setCrm($crm)
            ->setDiscoveryUrl($discoveryUrl)
            ->setUsername($username)
            ->setPassword($password)
            ->getRequest('LoginRequest');

        $loginXML = $request->getXML($username, $password);

        try {
            $securityXML = $this->soapRequester->sendRequest($request, $loginXML, true);
        } catch (Exception $ex) {
            throw $ex;
        }
        if(is_object($securityXML)){
 			return false;

		}

        return $this->getSecurityToken($securityXML);
    }

    /**
     * @param string $securityXML
     *
     * @return array
     */
    private function getSecurityToken($securityXML)
    {
    	if(is_object($securityXML))return false;;
        $securityDOM = new \DOMDocument();
        if (!$securityDOM->loadXML($securityXML)) {
            return false;
        }

        return array(
            'securityToken' => $this->getRequestedSecurityToken($securityDOM),
            'binarySecret'  => $securityDOM->getElementsbyTagName("BinarySecret")->item(0)->textContent,
            'keyIdentifier' => $securityDOM->getElementsbyTagName("KeyIdentifier")->item(0)->textContent,
        );
    }

    /**
     * @param DOMElement $securityDOM
     *
     * @return mixed
     */
    private function getRequestedSecurityToken($securityDOM)
    {
        $requestTokenString = $securityDOM->saveXML(
            $securityDOM->getElementsByTagName("RequestedSecurityToken")->item(0)
        );

        $matches =array();
        preg_match(
            '/<trust:RequestedSecurityToken>(.*)<\/trust:RequestedSecurityToken>/',
            $requestTokenString,
            $matches
        );

        return $matches[1];
    }
}
