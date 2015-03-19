<?php

/**
 * Class SoapRequester
 *
 * @package connector\lib\Helper;
 */
class SoapRequester
{
    public static $soapEnvelope = 'http://www.w3.org/2003/05/soap-envelope';

    public static $soapFaults =array(
        'http://www.w3.org/2005/08/addressing/soap/fault',
        'http://schemas.microsoft.com/net/2005/12/windowscommunicationfoundation/dispatcher/fault',
        'http://schemas.microsoft.com/xrm/2011/Contracts/Services/IOrganizationService/ExecuteOrganizationServiceFaultFault',
        'http://schemas.microsoft.com/xrm/2011/Contracts/Services/IOrganizationService/CreateOrganizationServiceFaultFault',
        'http://schemas.microsoft.com/xrm/2011/Contracts/Services/IOrganizationService/RetrieveOrganizationServiceFaultFault',
        'http://schemas.microsoft.com/xrm/2011/Contracts/Services/IOrganizationService/UpdateOrganizationServiceFaultFault',
        'http://schemas.microsoft.com/xrm/2011/Contracts/Services/IOrganizationService/DeleteOrganizationServiceFaultFault',
        'http://schemas.microsoft.com/xrm/2011/Contracts/Services/IOrganizationService/RetrieveMultipleOrganizationServiceFaultFault',
    );
	private $requestBuilder;
    protected $timeout = 60;

    protected $responder = null;

    
    public function construct(\RequestBuilder $RequestBuilder){
    	$this->requestBuilder=$RequestBuilder;
    }
    /**
     * @param DynamicsCRMResponse $responder
     */
    public function setResponder(\DOMDocument $responder)
    {
        $this->responder = $responder;
    }

    /**
     * @param string $uri
     * @param string $request
     *
     * @return mixed
     * @throws \Exception
     */
    public function sendRequest($Request, $SoapBody)
    {
    	$this->Request=$Request;
    	if($this->Request->isNtlm()){
    		$headers = $this->getNtlmHeaders($Request->getAction());
        $ch = $this->getCurlNtlmHandle($this->Request, $headers, $SoapBody);
    	}else{
    		$headers = $this->getHeaders($this->Request->getTo(), $SoapBody);
    		$ch = $this->getCurlHandle($this->Request->getTo(), $headers, $SoapBody);
    	}
        $responseXml = curl_exec($ch);
       
        try {
            $this->hasError($ch, $responseXml);
        } catch (\Exception $ex) {
        	$Return = new CrmResponse();
        	$Return->Error = True;
			$Return->ErrorCode = 0;
			$Return->ErrorMessage = $ex->getMessage();
			$Return->Result = False;
			$Return->MoreRecords=false;
            return $Return;
        }
        
        curl_close($ch);

        if ($this->responder) {
            $this->responder->loadXML($responseXml);

            return $this->responder;
        }

        return $responseXml;
    }

    /**
     * @param string $uri
     * @param string $request
     *
     * @return array
     */
    private function getHeaders($uri, $request)
    {
        $urlDetails = parse_url($uri);
        return array(
            "POST " . $urlDetails['path'] . " HTTP/1.1",
            "Host: " . $urlDetails['host'],
            'Connection: Keep-Alive',
            'Content-type: application/soap+xml; charset=UTF-8',
            'Content-length: ' . strlen($request)
        );
    }

    /**
     * @param string $uri
     * @param string $request
     *
     * @return array
     */
    private function getNtlmHeaders($action)
    {
    	return array (
    			'Method: POST',
    			'Connection: Keep-Alive',
    			'User-Agent: PHP-SOAP-CURL',
    			'Content-Type: text/xml; charset=utf-8',
    			'SOAPAction: "'.$action.'"'
    	);
    }
    
    
    /**
     * @param string $uri
     * @param array  $headers
     * @param string $request
     *
     * @return resource
     */
    private function getCurlHandle($uri, $headers, $request)
    {
    	 $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
    	
        return $ch;
    }
    
    private function getCurlNtlmHandle($Request, $headers, $SoapBody)
    {
    
    		$SoapBody=str_ireplace('soap/envelope/"/>','soap/envelope/" >',$SoapBody);
    		$ch = curl_init ();
    		curl_setopt ( $ch, CURLOPT_URL, $Request->getTo().'/web' );
    		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
    		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
    		curl_setopt ( $ch, CURLOPT_UNRESTRICTED_AUTH,true);
    		curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION,true);
    		curl_setopt ( $ch, CURLOPT_POST, true );
    		curl_setopt ( $ch, CURLOPT_POSTFIELDS,$SoapBody );
    		curl_setopt ( $ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM );
    		curl_setopt ( $ch, CURLOPT_USERPWD, $Request->requestBuilder->getUsername().':'.$Request->requestBuilder->getPassword());
    		
        	return $ch;
    }
    
    /**
     * @param resource $ch
     * @param string   $responseXML
     *
     * @throws \Exception
     */
    private function hasError($ch, $responseXML)
    {
        $this->testCurlResponse($ch, $responseXML);

        if ($responseXML) {
            $responseDOM = new \DOMDocument();
            try{
            $responseDOM->loadXML($responseXML);
            }catch(\Exception $e){
            	throw new \Exception($responseXML);
            }
            if($this->Request->isNtlm()===false){
            $this->testIsValidSoapResponse($responseDOM, $responseXML);
            $this->testIsValidSoapHeader($responseDOM, $responseXML);
            $this->testActionIsNotError($responseDOM, $responseXML);
            }

            return;
        }

        throw new \Exception('No response found');
    }

    /**
     * @param resource $ch
     * @param string   $responseXML
     *
     * @throws \Exception
     */
    private function testCurlResponse($ch, $responseXML)
    {
        if ($responseXML === false) {
            throw new \Exception('cURL Error: ' . curl_error($ch));
        }
    }

    /**
     * @param \DOMDocument $responseDOM
     * @param string       $responseXML
     *
     * @throws \Exception
     */
    private function testIsValidSoapResponse(\DOMDocument $responseDOM, $responseXML)
    {
        if ($responseDOM->getElementsByTagNameNS(SoapRequester::$soapEnvelope, 'Envelope')->length < 1) {
            throw new \Exception('Invalid SOAP Response: HTTP Response ' . $responseXML . PHP_EOL . $responseXML . PHP_EOL);
        }
    }

    /**
     * @param string $responseDOM
     *
     * @return mixed
     */
    private function getEnvelope($responseDOM)
    {
        return $responseDOM->getElementsByTagNameNS(SoapRequester::$soapEnvelope, 'Envelope')->item(0);
    }

    /**
     * @param \DOMElement $envelope
     *
     * @return mixed
     */
    private function getHeader($envelope)
    {
        return $envelope->getElementsByTagNameNS(SoapRequester::$soapEnvelope, 'Header')->item(0);
    }

    /**
     * @param \DOMElement $header
     *
     * @return mixed
     */
    private function getAction($header)
    {
        return $header->getElementsByTagNameNS('http://www.w3.org/2005/08/addressing', 'Action')->item(0);
    }

    /**
     * @param \DOMDocument $responseDOM
     * @param string       $responseXML
     *
     * @throws \Exception
     */
    private function testIsValidSoapHeader(\DOMDocument $responseDOM, $responseXML)
    {
        $envelope = $this->getEnvelope($responseDOM);
        $header = $this->getHeader($envelope);

        if (!$header) {
            throw new \Exception('Invalid SOAP Response: No SOAP Header!' . PHP_EOL . $responseXML . PHP_EOL);
        }
    }

    /**
     * @param \DOMDocument $responseDOM
     *
     * @throws \Exception
     */
    private function testActionIsNotError(\DOMDocument $responseDOM)
    {
        $envelope = $this->getEnvelope($responseDOM);
        $header = $this->getHeader($envelope);
        $actionString = $this->getAction($header)->textContent;

        if (in_array($actionString, self::$soapFaults)) {
            throw $this->getSoapFault($responseDOM);
        }
    }

    /**
     * @param \DOMDocument $responseDOM
     *
     * @return \Exception
     */
    private function getSoapFault(\DOMDocument $responseDOM)
    {
        return new \SoapFault($this->getSoapFaultCode($responseDOM), $this->getSoapFaultMessage($responseDOM));
    }

    /**
     * @param \DomDocument $responseDOM
     *
     * @return string
     */
    private function getSoapFaultCode(\DomDocument $responseDOM)
    {
        /**
         * TODO Change to use xpath
         */
        $hierarchy =array('Envelope', 'Body', 'Fault', 'Code', 'Value');
        $item = $responseDOM;
        foreach ($hierarchy as $currentLevel) {
            $item = $item->getElementsByTagNameNS('http://www.w3.org/2003/05/soap-envelope', $currentLevel)->item(0);
        }

        return $item->nodeValue;
    }

    /**
     * @param \DOMDocument $responseDOM
     *
     * @return string
     */
    private function getSoapFaultMessage(\DOMDocument $responseDOM)
    {
        /**
         * TODO Change to use xpath
         */
        $hierarchy = array('Envelope', 'Body', 'Fault', 'Reason', 'Text');
        $item = $responseDOM;
        foreach ($hierarchy as $currentLevel) {
            $item = $item->getElementsByTagNameNS('http://www.w3.org/2003/05/soap-envelope', $currentLevel)->item(0);
        }

        return $item->nodeValue;
    }
}
