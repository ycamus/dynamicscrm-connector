<?php

use \DOMElement;


/**
 * Class AbstractSoapRequest
 *
 * @package connector\lib\Request
 */
abstract class AbstractSoapRequest extends AbstractCrmRequest
{
    protected $server;
    protected $action;
    protected $to;
    protected $ntlm;

    protected $xmlns = 'http://www.w3.org/2000/xmlns/';
    protected $envelopeNS = 'http://www.w3.org/2003/05/soap-envelope';
    protected $envelopeNtlmNS = 'http://schemas.xmlsoap.org/soap/envelope/';
    protected $contractNS = 'http://schemas.microsoft.com/xrm/2011/Contracts';
    protected $serviceNS = 'http://schemas.microsoft.com/xrm/2011/Contracts/Services';

    protected $xmlSchemaNS = 'http://www.w3.org/2001/XMLSchema-instance';

    /**
     * @var DOMHelper
     */
    protected $domHelper;
    /**
     * @var TimeHelper
     */
    protected $timeHelper;

    /**
     * @var mixed
     */
    protected $securityToken;

    private $tempCreatedTimestamp;

    /**
     * @param RequestBuilder $builder
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(RequestBuilder $builder)
    {
        if (!$this->to || !$this->action) {
            throw new \InvalidArgumentException("_action and _to must be set");
        }
		$this->ntlm=$builder->getNtlm();
        $this->domHelper = $builder->getDomHelper();
        $this->timeHelper = $builder->getTimeHelper();
        $this->server = $builder->getServer();
        if ($builder->getOrganization()) {
            $this->organization = $builder->getOrganization();
        }
    }

     /**
     * @return string
     */
    public function getXML()
    {
        $createRequest = new \DOMDocument();

        $request = $this->getEnvelope();
        $node = $createRequest->importNode($request, true);
        $createRequest->appendChild($node);

        $xml = $createRequest->saveXML();

        return $xml;
    }

    /**
     * @return string
     */
    public function getEmptyEnveloppe()
    {
    	$createRequest = new \DOMDocument();
    
    	$envelope = $this->getSoapEnvelope();
    	if($this->getHeader())   	$envelope->appendChild($this->getHeader());
    	$node = $createRequest->importNode($envelope, true);
    	$createRequest->appendChild($node);
    
    	$xml = $createRequest->saveXML();
    
    	return $xml;
    }
    
    abstract protected function getBody();

    /**
     * @return DOMElement
     */
    protected function getEnvelope()
    {
        $envelope = $this->getSoapEnvelope();
        $envelope->appendChild($this->getHeader());
        $envelope->appendChild($this->getBody());

        return $envelope;
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        if (substr($this->server, -1) !== '/') {
            $this->server .= "/";
        }
		if($this->organization!='false' ) return $this->server . $this->organization . '/' . $this->to;
		else  return $this->server . $this->to;
    }

    /**
     * @return DOMElement
     */
    public function getSoapEnvelope()
    {
        $wsSecurity = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';
        if(!$this->getNtlm()){
      		  	$envelope = $this->domHelper->createElementNS($this->envelopeNS, 's:Envelope');
         		$envelope->setAttributeNS($this->xmlns, 'xmlns:a', 'http://www.w3.org/2005/08/addressing');
                $envelope->setAttributeNS($this->xmlns, 'xmlns:u', $wsSecurity);
       		 }else {
       		 	$envelope = $this->domHelper->createElementNS($this->envelopeNtlmNS, 's:Envelope');
       		 }
        return $envelope;
    }

    /**
     * @return DOMElement
     */
    public function getSoapHeader()
    {
        $action = $this->domHelper->createElement('a:Action', $this->action);
        $action->setAttribute('s:mustUnderstand', 1);

        $address = $this->domHelper->createElement('a:Address', 'http://www.w3.org/2005/08/addressing/anonymous');
        $replyTo = $this->domHelper->createElement('a:ReplyTo');
        $replyTo->appendChild($address);

        $to = $this->domHelper->createElement('a:To', $this->getTo());
        $to->setAttribute('s:mustUnderstand', 1);

        $header = $this->domHelper->createElement('s:Header');
        $header->appendChild($action);
        $header->appendChild($replyTo);
        $header->appendChild($to);

        return $header;
    }

    /**
     * @return DOMElement
     */
    public function getSoapNtlmHeader()
    {
    	$action = $this->domHelper->createElement('a:Action', $this->action);
    	$action->setAttribute('s:mustUnderstand', 1);

    	$header = $this->domHelper->createElement('s:Header');
    	$header->appendChild($action);
    
    	return $header;
    }
    
    /**
     * @return DOMElement
     */
    protected function getSoapSecurity()
    {
        $uri = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

        $security = $this->domHelper->createElementNS($uri, 'o:Security');
        $security->setAttribute('s:mustUnderstand', 1);
        $security->appendChild($this->getSoapTimeStamp());

        return $security;
    }

    /**
     * @param array $securityToken
     *
     * @return mixed
     */
    protected function getRequestedSecurityToken($securityToken)
    {
        $requestSecurityToken = $this->domHelper->createDocumentFragment();
        $requestSecurityToken->appendXML($securityToken['securityToken']);

        return $requestSecurityToken;
    }

    /**
     * @return DOMElement
     */
    private function getSoapTimeStamp()
    {
        $timestampNameSpace = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';

        $created = $this->domHelper->createElement('u:Created', $this->timeHelper->getCurrentTime());
        $expires = $this->domHelper->createElement('u:Expires', $this->timeHelper->getExpiryTime());

        $timestamp = $this->domHelper->createElementNS($timestampNameSpace, 'u:Timestamp');
        $timestamp->setAttribute('u:Id', '_0');
        $timestamp->appendChild($created);
        $timestamp->appendChild($expires);

        $this->tempCreatedTimestamp = $timestamp;

        return $timestamp;
    }

    protected function getHeader()
    {
    	if($this->getNtlm()){
    		$header =  false;
	      	}else{
	      		$header = $this->getSoapHeader();
	      		$header->appendChild($this->getSecurity($this->securityToken));
	      		 }
        return $header;
    }

     /**
     * @throws \RuntimeException
     *
     * @return DOMElement
     */
    private function getSecurity()
    {
        $security = $this->getSoapSecurity();

        if (!$this->securityToken) {
            throw new \RuntimeException("Request requires security token none set. Add to requests constructor");
        }

        $security->appendChild($this->getRequestedSecurityToken($this->securityToken));
        $security->appendChild($this->getSoapSecurityToken($this->securityToken));

        return $security;
    }

    /**
     * @param array $securityToken
     *
     * @return DOMElement
     */
    private function getSoapSecurityToken($securityToken)
    {
        $canonicalizationMethod = $this->domHelper->createElement('CanonicalizationMethod');
        $canonicalizationMethod->setAttribute('Algorithm', 'http://www.w3.org/2001/10/xml-exc-c14n#');

        $signatureMethod = $this->domHelper->createElement('SignatureMethod');
        $signatureMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#hmac-sha1');

        $signedInfo = $this->domHelper->createElement('SignedInfo');
        $signedInfo->appendChild($canonicalizationMethod);
        $signedInfo->appendChild($signatureMethod);

        $reference = $this->getReference($securityToken, $signedInfo);
        $signedInfo->appendChild($reference);

        $signature = $this->domHelper->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'Signature');
        $signature->appendChild($signedInfo);

        $signature->appendChild($this->getSignatureValue($securityToken, $signedInfo));
        $signature->appendChild($this->getKeyInfo($securityToken));

        return $signature;
    }

    /**
     * @param array  $securityToken
     * @param string $signedInfo
     *
     * @return DOMElement
     */
    private function getSignatureValue($securityToken, $signedInfo)
    {
        $signedInfoString = $signedInfo->ownerDocument->saveXML($signedInfo);
        /**
         * Namespace required, however creating the DOM the way the code currently does won't allow duplicate
         * namespaces which is required to make sure the HASH below matches what CRM expects.
         */
        $signedInfoString = str_replace(
            '<SignedInfo>',
            '<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#">',
            $signedInfoString
        );

        $secret = base64_decode($securityToken['binarySecret']);
        $signature = base64_encode(hash_hmac('sha1', $signedInfoString, $secret, true));

        $signatureValue = $this->domHelper->createElement('SignatureValue', $signature);

        return $signatureValue;
    }

    /**
     * @return DOMElement
     */
    private function getReference()
    {
        $digestMethod = $this->domHelper->createElement('DigestMethod');
        $digestMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');

        $transform = $this->domHelper->createElement('Transform');
        $transform->setAttribute('Algorithm', 'http://www.w3.org/2001/10/xml-exc-c14n#');

        $transforms = $this->domHelper->createElement('Transforms');
        $transforms->appendChild($transform);

        $reference = $this->domHelper->createElement('Reference');
        $reference->setAttribute('URI', '#_0');
        $reference->appendChild($transforms);
        $reference->appendChild($digestMethod);
        $reference->appendChild($this->getDigestValue());

        return $reference;
    }

    /**
     * @return DOMElement
     */
    private function getDigestValue()
    {
        $time = $this->tempCreatedTimestamp->ownerDocument->saveHTML($this->tempCreatedTimestamp);
        $digestValue = base64_encode(sha1($time, true));
        $digestValue = $this->domHelper->createElement('DigestValue', $digestValue);

        return $digestValue;
    }

    /**
     * @param array $securityToken
     *
     * @return DOMElement
     */
    private function getKeyInfo($securityToken)
    {
        $keyIdentifier = $this->domHelper->createElement('o:KeyIdentifier', $securityToken['keyIdentifier']);
        $keyIdentifier->setAttribute(
            'ValueType',
            'http://docs.oasis-open.org/wss/oasis-wss-saml-token-profile-1.0#SAMLAssertionID'
        );

        $securityTokenReferenceNS = 'http://docs.oasis-open.org/wss/oasis-wss-wssecurity-secext-1.1.xsd';
        $securityTokenReferenceValue = 'http://docs.oasis-open.org/wss/oasis-wss-saml-token-profile-1.1#SAMLV1.1';
        $securityTokenReference = $this->domHelper->createElement('o:SecurityTokenReference');
        $securityTokenReference->setAttributeNS($securityTokenReferenceNS, 'k:TokenType', $securityTokenReferenceValue);
        $securityTokenReference->appendChild($keyIdentifier);

        $keyInfo = $this->domHelper->createElement('KeyInfo');
        $keyInfo->appendChild($securityTokenReference);

        return $keyInfo;
    }

     /**
     *
     * @param  string                                               $name
     * @return \Sixdg\DynamicsCRMConnector\Requests\RetrieveRequest
     */
    public function setEntityName($name)
    {
        $this->entityName = $name;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }
	public function getNtlm() {
		return $this->ntlm;
	}
	public function setNtlm($ntlm) {
		$this->ntlm = $ntlm;
		return $this;
	}
	public function isNtlm() {
		return $this->getNtlm();
	}
	
}
