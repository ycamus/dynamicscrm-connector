<?php

/**
 * Class RequestBuilder
 *
 * @package Sixdg\DynamicsCRMConnector\Builder
 */
class RequestBuilder
{
    private $domHelper;
    private $timeHelper;

    private $username;
    private $password;

    private $server;
    private $crm;

    private $organization;
    private $discoveryUrl;

    private $securityToken;
    private $entity;
    private $ntlm;

    private $entityToDomConverter;

    /**
     * @param \DOMHelper            $domHelper
     * @param \TimeHelper           $timeHelper
     * @param \EntityToDomConverter $entityToDomConverter
     */
    public function __construct($domHelper, $timeHelper, $entityToDomConverter)
    {
        $this->domHelper = $domHelper;
        $this->timeHelper = $timeHelper;
        $this->entityToDomConverter = $entityToDomConverter;
        $this->ntlm=false;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        return new self($this->domHelper, $this->timeHelper, $this->entityToDomConverter);
    }

    /**
     * @param string $name
     *
     * @throws \Exception
     * @return mixed      False on failure class on success
     */
    public function getRequest($name)
    {
        $namespace =$name;
        if (class_exists($namespace)) {
            return new $namespace($this);
        }

        throw new \Exception("Request class not found : ".$namespace);
    }

    /**
     * @return mixed
     */
    public function getSecurityToken()
    {
        if (!isset($this->securityToken)) {
            return null;
        }

        return $this->securityToken;
    }

    /**
     * @param array $securityToken
     *
     * @return $this
     */
    public function setSecurityToken($securityToken)
    {
        $this->securityToken = $securityToken;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param Entity $entity
     *
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param mixed $server
     *
     * @return $this
     */
    public function setServer($server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param string $organization
     *
     * @return $this
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscoveryUrl()
    {
        return $this->discoveryUrl;
    }

    /**
     * @param string $discoveryUrl
     *
     * @return $this
     */
    public function setDiscoveryUrl($discoveryUrl)
    {
        $this->discoveryUrl = $discoveryUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDomHelper()
    {
        return $this->domHelper;
    }

    /**
     * @param DOMHelper $domHelper
     *
     * @return $this
     */
    public function setDomHelper($domHelper)
    {
        $this->domHelper = $domHelper;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTimeHelper()
    {
        return $this->timeHelper;
    }

    /**
     * @param TimeHelper $timeHelper
     *
     * @return $this
     */
    public function setTimeHelper($timeHelper)
    {
        $this->timeHelper = $timeHelper;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCrm()
    {
        return $this->crm;
    }

    /**
     * @param string $crm
     *
     * @return mixed
     */
    public function setCrm($crm)
    {
        $this->crm = $crm;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntityToDomConverter()
    {
        return $this->entityToDomConverter;
    }

    /**
     * @param mixed $entityToDomConverter
     */
    public function setEntityToDomConverter($entityToDomConverter)
    {
        $this->entityToDomConverter = $entityToDomConverter;
    }
	public function getNtlm() {
		return $this->ntlm;
	}
	public function setNtlm($ntlm) {
		$this->ntlm = $ntlm;
		return $this;
	}
	
}
