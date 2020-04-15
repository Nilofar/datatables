<?php

namespace Edulog\DatatablesBundle\Controller;

use Edulog\DatatablesBundle\Service\DtFilterFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DatatablesController
 * @package Edulog\DatatablesBundle\Controller
 */
class DatatablesController extends Controller
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var DtFilterFactory
     */
    protected $dtFilterFactory;

    /**
     * DatatablesController constructor.
     * @param EntityManagerInterface $em
     * @param DtFilterFactory $dtFilterFactory
     */
    public function __construct(EntityManagerInterface $em, DtFilterFactory $dtFilterFactory)
    {
        $this->em = $em;
        $this->dtFilterFactory = $dtFilterFactory;
    }

    /**
     * Requête AJAX qui permet de sauvegarder en session les filtres d'un formulaire de
     * recherche.
     *
     * @Route("/save-filters/{identifier}", methods={"GET", "POST"},
     *     name="edulog_datatables_saveFilters", options={"expose" = true})
     *
     * @param Request $request
     * @param string $identifier
     * @return JsonResponse
     */
    public function saveFilters(Request $request, string $identifier)
    {
        $dtFilter = $this->dtFilterFactory->getObject($identifier);

        // Récupère le form hydraté puis le sauvegarde en session via la searchEngineFactory
        $form = $this->createForm($identifier, $dtFilter);
        $form->handleRequest($request);
        $this->dtFilterFactory->save($dtFilter, $identifier);

        return new JsonResponse([
            'identifier' => $identifier,
            'saved' => true
        ]);
    }
}
