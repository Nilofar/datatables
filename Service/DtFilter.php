<?php
namespace Edulog\DatatablesBundle\Service;

/**
 * Class DtFilter
 * @package Edulog\DatatablesBundle\Service
 */
class DtFilter
{
    /** @var bool $doSearch */
    protected $doSearch = false;

    /** @var string $identifier */
    protected $identifier;

    /** @var array $params */
    protected $params = [];

    public function __isset($name)
    {
        return isset($this->params[$name]);
    }

    public function __get($propertyName)
    {
        if (empty($this->params[$propertyName])) {
            return null;
        }

        return $this->params[$propertyName];
    }

    public function __set($propertyName, $value)
    {
        $this->doSearch = true;
        $this->params[$propertyName] = $value;
    }

    public function isSearchDone()
    {
        return $this->doSearch;
    }

    /**
     * @param string|null $identifier
     * @return DtFilter
     */
    public function setFormClass(string $identifier = null)
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}
