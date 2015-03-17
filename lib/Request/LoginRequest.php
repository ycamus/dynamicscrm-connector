<?php
use \DOMElement;

/**
 * Class LoginRequest
 *
 * @package connector\lib\Request
 */
class LoginRequest extends AbstractSoapRequest
{
    protected $username;
    protected $password;

    protected $discoveryUrl;
    protected $crm;

    protected $action = 'http://docs.oasis-open.org/ws-sx/ws-trust/200512/RST/Issue';
    protected $to = 'services/trust/13/usernamemixed';

    /**
     * @param RequestBuilder $requestBuilder
     */
    public function __construct(RequestBuilder $requestBuilder)
    {
        $this->discoveryUrl = $requestBuilder->getDiscoveryUrl();
        $this->username = $requestBuilder->getUsername();
        $this->password = $requestBuilder->getPassword();
        $this->crm = $requestBuilder->getCrm();

        parent::__construct($requestBuilder);
    }

    /**
     * @return mixed|string
     */
    public function getTo()
    {
        if (substr($this->server, -1) !== '/') {
            $this->server .= "/";
        }

        return $this->server . $this->to;
    }

    /**
     * @return mixed|string
     */
    public function getXML()
    {
        $loginRequest = $this->domHelper->createNewDomDocument();
        $node = $loginRequest->importNode($this->getLoginRequest(), true);
        $loginRequest->appendChild($node);

        return $loginRequest->saveXML();
    }

    /**
     * @return DOMElement
     */
    private function getLoginRequest()
    {
        $envelope = $this->getSoapEnvelope();
        $envelope->appendChild($this->getHeader(null));
        $envelope->appendChild($this->getBody());

        return $envelope;
    }

    /**
     * @return DOMElement
     */
    protected function getHeader()
    {
        $header = $this->getSoapHeader();
        $header->appendChild($this->getSecurity());

        return $header;
    }

    /**
     * @return DOMElement
     */
    private function getSecurity()
    {
        $security = $this->getSoapSecurity();
        $security->appendChild($this->getUsernameToken());

        return $security;
    }

    /**
     * @return DOMElement
     */
    private function getUsernameToken()
    {
        // @codingStandardsIgnoreStart
        $passwordSpec = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText';
        // @codingStandardsIgnoreEnd
        $userName = $this->domHelper->createElement('o:Username', $this->username);
        $password = $this->domHelper->createElement('o:Password', $this->password);
        $password->setAttribute('Type', $passwordSpec);

        $token = $this->domHelper->createElement('o:UsernameToken');
        $token->setAttribute('u:Id', 'user');
        $token->appendChild($userName);
        $token->appendChild($password);

        return $token;
    }

    /**
     * @return DOMElement
     */
    protected function getBody()
    {
        $address = $this->domHelper->createElement('a:Address', $this->discoveryUrl);

        $endPointReference = $this->domHelper->createElement('a:EndpointReference');
        $endPointReference->appendChild($address);

        $policy = $this->domHelper->createElementNS('http://schemas.xmlsoap.org/ws/2004/09/policy', 'wsp:AppliesTo');
        $policy->appendChild($endPointReference);

        $requestType = $this->domHelper->createElement(
            'trust:RequestType',
            'http://docs.oasis-open.org/ws-sx/ws-trust/200512/Issue'
        );

        $rst = $this->domHelper->createElementNS(
            'http://docs.oasis-open.org/ws-sx/ws-trust/200512',
            'trust:RequestSecurityToken'
        );
        $rst->appendChild($policy);
        $rst->appendChild($requestType);

        $body = $this->domHelper->createElement('s:Body');
        $body->appendChild($rst);

        return $body;
    }
}
