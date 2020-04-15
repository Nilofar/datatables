<?php
namespace Edulog\DatatablesBundle\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class DtFilterFactory
 * @package Edulog\DatatablesBundle\Service
 */
class DtFilterFactory
{
    /**
     * @var SessionInterface
     */
    protected $_session;

    /**
     * @var string
     */
    protected $bagName;

    const DEFAULT_BAG_NAME = 'dt_filter';

    /**
     * DtFilterFactory constructor.
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->_session = $session;
        $this->bagName = self::DEFAULT_BAG_NAME;
    }

    /**
     * Permet de sauvegarder en session un searchEngine pour un bag en particulier
     *
     * @param $objectToSave
     * @param string $class
     * @return $this
     */
    public function save($objectToSave, string $class)
    {
        $this->_session->getBag($this->bagName)->set($class, $objectToSave);
       
        return $this;
    }

    /**
     * @param string $class
     * @return DtFilter
     */
    public function getObject(string $class)
    {

        $dtFilter = $this->_session->getBag($this->bagName)->get($class);
        $dtFilter->setFormClass($class);
        
        if ($dtFilter instanceof DtFilter) {
            return $dtFilter;
        }

        $newObject = new DtFilter();
        $this->save($newObject, $class);

        return $newObject;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function destroy(string $class)
    {
        $this->_session->getBag($this->bagName)->remove($class);

        return $this;
    }
}
