<?php
use \DOMElement;


/**
 * Class SingleEntityRequest
 *
 * @package Sixdg\DynamicsCRMConnector\Requests
 */
class RetrieveRequest extends \AbstractSoapRequest
{

    protected $action = 'http://schemas.microsoft.com/xrm/2011/Contracts/Services/IOrganizationService/Retrieve';
    protected $to = 'XRMServices/2011/Organization.svc';
    protected $entityName;
    protected $entityId;

    /**
     * @param RequestBuilder $requestBuilder
     */
    public function __construct(\RequestBuilder $requestBuilder)
    {
        $this->securityToken = $requestBuilder->getSecurityToken();

        parent::__construct($requestBuilder);
    }

    /**
     *   * @return mixed|string
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
     * @return \Sixdg\DynamicsCRMConnector\Components\DOM\DOMElement
     */
    protected function getBody()
    {
        $entityName = $this->domHelper->createElement('entityName', $this->getEntityName());
        $entityId = $this->domHelper->createElement('id', $this->getEntityId());

        $columnSet = $this->getColumnSetNode();

        $retrievalNode = $this->domHelper->createElementNS($this->serviceNS, 'Retrieve');
        $retrievalNode->appendChild($entityName);
        $retrievalNode->appendChild($entityId);
        $retrievalNode->appendChild($columnSet);

        $body = $this->domHelper->createElement('s:Body');
        $body->appendChild($retrievalNode);

        return $body;
    }

    /**
     * @return DOMElement
     */
    private function getColumnSetNode()
    {
        $xmlnsb = 'http://schemas.microsoft.com/xrm/2011/Contracts';
        $xmlnsi = "http://www.w3.org/2001/XMLSchema-instance";

        $columns = $this->domHelper->createElement('b:AllColumns', 'true');

        $columnSet = $this->domHelper->createElement('columnSet');
        $columnSet->setAttributeNS($this->xmlns, 'xmlns:b', $xmlnsb);
        $columnSet->setAttributeNS($this->xmlns, 'xmlns:i', $xmlnsi);
        $columnSet->appendChild($columns);

        return $columnSet;
    }

    /**
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     *
     * @param  id                                                   $id
     * @return \Sixdg\DynamicsCRMConnector\Requests\RetrieveRequest
     */
    public function setEntityId($id)
    {
        $this->entityId = $id;

        return $this;
    }
}
