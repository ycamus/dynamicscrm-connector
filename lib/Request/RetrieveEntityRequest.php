<?php

/**
 * Class RetrieveEntityRequest
 *
 * @package Sixdg\DynamicsCRMConnector\Requests
 */
class RetrieveEntityRequest extends AbstractSoapRequest
{
    protected $action = 'http://schemas.microsoft.com/xrm/2011/Contracts/Services/IOrganizationService/Execute';
    protected $to = 'XRMServices/2011/Organization.svc';

    private $blankGuid = "00000000-0000-0000-0000-000000000000";

    /**
     * @param RequestBuilder $requestBuilder
     */
    public function __construct(RequestBuilder $requestBuilder)
    {
        $this->organization = $requestBuilder->getOrganization();
        $this->securityToken = $requestBuilder->getSecurityToken();
        $this->entity = $requestBuilder->getEntity();

        parent::__construct($requestBuilder);
    }

    /**
     * @return \Sixdg\DynamicsCRMConnector\Components\DOM\DOMElement
     */
    protected function getBody()
    {
        $keyValueNodes = $this->getKeyValueNodes();

        $parameters = $this->domHelper->createElement('b:Parameters');
        $parameters->setAttributeNS(
            $this->xmlns,
            'xmlns:c',
            'http://schemas.datacontract.org/2004/07/System.Collections.Generic'
        );

        foreach ($keyValueNodes as $node) {
            $parameters->appendChild($node);
        }

        $requestId = $this->domHelper->createElement('b:RequestId');
        $requestId->setAttribute('i:nil', 'true');

        $requestName = $this->domHelper->createElement('b:RequestName', 'RetrieveEntity');

        $request = $this->domHelper->createElement('request');
        $request->setAttributeNS($this->xmlSchemaNS, 'i:type', 'b:RetrieveEntityRequest');
        $request->setAttributeNS($this->xmlns, 'xmlns:b', $this->contractNS);

        $request->appendChild($parameters);
        $request->appendChild($requestId);
        $request->appendChild($requestName);

        $execute = $this->domHelper->createElementNS($this->serviceNS, 'Execute');
        $execute->appendChild($request);

        $body = $this->domHelper->createElement('s:Body');
        $body->appendChild($execute);

        return $body;
    }

    /**
     * @return array
     */
    private function getKeyValueNodes()
    {
        $keyValueNodes =array();

        $keys = ('EntityFilters', 'MetadataId', 'RetrieveAsIfPublished', 'LogicalName');
        foreach ($keys as $key) {
            $keyNode = $this->domHelper->createElement('c:key', $key);
            $valueNode = $this->getValueNode($key);

            $keyValuePair = $this->domHelper->createElement('b:KeyValuePairOfstringanyType');
            $keyValuePair->appendChild($keyNode);
            $keyValuePair->appendChild($valueNode);

            $keyValueNodes[] = $keyValuePair;
        }

        return $keyValueNodes;
    }

    /**
     * @param string $key
     *
     * @return null|\Sixdg\DynamicsCRMConnector\Components\DOM\DOMElement
     */
    public function getValueNode($key)
    {
        $node = null;

        switch ($key) {
            case "EntityFilters":
                $node = $this->domHelper->createElement('c:value', 'Entity Attributes Privileges Relationships');
                $node->setAttribute('i:type', 'd:EntityFilters');
                $node->setAttributeNS(
                    'http://www.w3.org/2000/xmlns/',
                    'xmlns:d',
                    'http://schemas.microsoft.com/xrm/2011/Metadata'
                );
                break;
            case "MetadataId":
                $node = $this->domHelper->createElement('c:value', $this->blankGuid);
                $node->setAttribute('i:type', 'd:guid');
                $node->setAttributeNS(
                    'http://www.w3.org/2000/xmlns/',
                    'xmlns:d',
                    'http://schemas.microsoft.com/2003/10/Serialization/'
                );
                break;
            case "LogicalName":
                $node = $this->domHelper->createElement('c:value', $this->entity->getEntityName());
                $node->setAttribute('i:type', 'd:string');
                $node->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:d', 'http://www.w3.org/2001/XMLSchema');
                break;
            case "RetrieveAsIfPublished":
                $node = $this->domHelper->createElement('c:value', 'false');
                $node->setAttribute('i:type', 'd:boolean');
                $node->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:d', 'http://www.w3.org/2001/XMLSchema');
                break;
        }

        return $node;
    }
}
