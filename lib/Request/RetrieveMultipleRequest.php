<?php

namespace Sixdg\DynamicsCRMConnector\Requests;



/**
 * Class RetrieveMultipleRequest
 *
 * @package Sixdg\DynamicsCRMConnector\Requests
 */
class RetrieveMultipleRequest extends \AbstractSoapRequest
{

    protected $action = 'http://schemas.microsoft.com/xrm/2011/Contracts/Services/IOrganizationService/RetrieveMultiple';
    protected $to = 'XRMServices/2011/Organization.svc';
    protected $pageNumber = 1;
    protected $pagingCookie = null;
    protected $limit = 1000;
    protected $query;
    protected $filters = array();

    /**
     * @param RequestBuilder $requestBuilder
     */
    public function __construct(RequestBuilder $requestBuilder)
    {
        $this->securityToken = $requestBuilder->getSecurityToken();
        parent::__construct($requestBuilder);
    }

    /**
     * Returns the xml request
     * @return mixed|string
     */
    public function getXML()
    {
        $retrievalRequest = new \DOMDocument();
        $request = $this->getEnvelope();
        $node = $retrievalRequest->importNode($request, true);
        $retrievalRequest->appendChild($node);

        return $retrievalRequest->saveXML();
    }

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
     * Returns the RetrieveMultiple xml request to be included in the body tag
     *
     * @return DOMNode
     */
    private function getRetrieveMultipleRequest(){

    
    if (is_array ( $Columns )) {
    	if ($this->array_key_exists_r ( 'aggregate', $Columns ) || $this->array_key_exists_r ( 'groupby', $Columns )) {
    		$Aggregate = 'true';
    	} else
    		$Aggregate = 'false';
    } else
    	$Aggregate = 'false';
    // build Soap Enveloppe
    try {
    	$Entity = $this->FormatFetchEntity ( $Table );
    	$Attributes = $this->FormatFetchAttribute ( $Columns );
    	$Order = $this->FormatFetchOrder ( $Order );
    	$Filter = $this->formatFetchFilter ( $Where );
    	if ($Join !== false){
    		$Join = $this->FormatFetchJoin ( $Join );
    	}else $Join='';
    } catch ( Exception $e ) {
    	return $this->GetErrorObject ( $e->getMessage () );
    }
    
    	
  
    //<query i:type<a:Query>
    $FetchXML= ' &lt;fetch version=\'1.0\' output-format=\'xml-platform\' mapping=\'logical\' aggregate=\'' . $Aggregate . '\' distinct=\'false\' page=\'1\' count=\'5000\' &gt;'
    		.$Entity.$Attributes.$Order.$Filter.$Join
    		. '&lt;/entity&gt;
				                               &lt;/fetch&gt';
			
			
    
        $queryXML = $this->query->getFetchExpression();
        // Turn the $queryXML into a DOMDocument so we can manipulate it
        $queryDOM = new \DOMDocument();
        $queryDOM->loadXML($queryXML);
        $page = $this->getPageNumber();
        // Modify the query that we send: Add the Page number
        $queryDOM->documentElement->setAttribute('page', $page);
        if ($this->getPagingCookie() != null) {
            // Modify the query that we send: Add the Paging-Cookie (note - HTMLENTITIES automatically applied by DOMDocument!)
            $queryDOM->documentElement->setAttribute('paging-cookie', $this->getPagingCookie());
        }

        // Modify the query that we send: Change the Count
        $queryDOM->documentElement->setAttribute('count', $this->limit);
        // Update the Query XML with the new structure
        $queryXML = $queryDOM->saveXML($queryDOM->documentElement);

        // Generate the RetrieveMultipleRequest message
        $retrieveMultipleRequestDOM = new \DOMDocument();
        $retrieveMultipleNode = $retrieveMultipleRequestDOM->appendChild($retrieveMultipleRequestDOM->createElementNS('http://schemas.microsoft.com/xrm/2011/Contracts/Services', 'RetrieveMultiple'));
        $queryNode = $retrieveMultipleNode->appendChild($retrieveMultipleRequestDOM->createElement('query'));
        $queryNode->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'i:type', 'b:FetchExpression');
        $queryNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:b', 'http://schemas.microsoft.com/xrm/2011/Contracts');
        $queryNode->appendChild($retrieveMultipleRequestDOM->createElement('b:Query', htmlentities($queryXML)));
        // Return the DOMNode
        return $retrieveMultipleNode;
    }

    /**
     * Returns the body tag
     *
     * @return DOMElement
     */
    protected function getBody()
    {
        $body = $this->domHelper->createElement('s:Body');
        $body->appendChild($this->domHelper->importNode($this->getRetrieveMultipleRequest(), true));

        return $body;
    }

    /**
     * Return the PagingCookie
     *
     * @returns string
     */
    public function getPagingCookie()
    {
        return $this->pagingCookie;
    }

    /**
     * Return the PageNumber
     *
     * @returns int
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     *
     * @param  string                                                       $cookie
     * @return \Sixdg\DynamicsCRMConnector\Requests\RetrieveMultipleRequest
     */
    public function setPagingCookie($cookie)
    {
        $this->pagingCookie = $cookie;

        return $this;
    }

    /**
     *
     * @param  int                                                          $number
     * @return \Sixdg\DynamicsCRMConnector\Requests\RetrieveMultipleRequest
     */
    public function setPageNumber($number)
    {
        $this->pageNumber = $number;

        return $this;
    }

    /**
     *
     * @param  int                                                          $number
     * @return \Sixdg\DynamicsCRMConnector\Requests\RetrieveMultipleRequest
     */
    public function setLimit($number)
    {
        $this->limit = $number;

        return $this;
    }

    /**
     *
     * @param  array                                                        $filters
     * @return \Sixdg\DynamicsCRMConnector\Requests\RetrieveMultipleRequest
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;

        return $this;
    }

        /**
     *
     * @param  FetchXML                                                     $query
     * @return \Sixdg\DynamicsCRMConnector\Requests\RetrieveMultipleRequest
     */
    public function setQuery(FetchXML $query)
    {
        $this->query = $query;

        return $this;
    }
}
