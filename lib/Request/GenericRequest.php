<?php
/**
 * Class RetrieveEntityRequest
 *
 * @package Sixdg\DynamicsCRMConnector\Requests
 */
class GenericRequest extends AbstractSoapRequest
{
    protected $action; 
    protected $to = 'XRMServices/2011/Organization.svc';
    protected  $body;
   public  $requestBuilder;

    /**
     * @param RequestBuilder $requestBuilder
     */
    public function __construct(RequestBuilder $requestBuilder, $action)
    {
    	$this->action ='http://schemas.microsoft.com/xrm/2011/Contracts/Services/IOrganizationService/'.$action;
    	$this->organization = $requestBuilder->getOrganization();
        $this->securityToken = $requestBuilder->getSecurityToken();
        $this->entity = $requestBuilder->getEntity();
        
        
        parent::__construct($requestBuilder);
        $this->requestBuilder=$requestBuilder;
    }

    /**
     * @return \Sixdg\DynamicsCRMConnector\Components\DOM\DOMElement
     */
    protected function getBody()
    {
        return $this->body;
    }
    public function setBody($body) {
   		return $this;
    }
	public function getAction() {
		return $this->action;
	}
	public function setAction($action) {
		$this->action ='http://schemas.microsoft.com/xrm/2011/Contracts/Services/IOrganizationService/'.$action;
		return $this;
	}

}
