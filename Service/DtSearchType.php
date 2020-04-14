<?php

namespace Edulog\DatatablesBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\DataTableTypeInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class DtSearchType
 * @package Edulog\DatatablesBundle\Service
 */
abstract class DtSearchType implements DataTableTypeInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var DtFilter
     */
    protected $dtFiler;

    /**
     * @var DtFilterFactory
     */
    protected $dtFilterFactory;

    /**
     * DtSearchType constructor.
     * @param EntityManagerInterface $em
     * @param EngineInterface $templating
     * @param RouterInterface $router
     * @param DtFilterFactory $dtFilterFactory
     */
    public function __construct(
        EntityManagerInterface $em,
        EngineInterface $templating,
        RouterInterface $router,
        DtFilterFactory $dtFilterFactory)
    {
        $this->em = $em;
        $this->templating = $templating;
        $this->router = $router;
        $this->dtFilterFactory = $dtFilterFactory;
    }
}
