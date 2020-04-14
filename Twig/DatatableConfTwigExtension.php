<?php
namespace Edulog\DatatablesBundle\Twig;

use Omines\DataTablesBundle\DataTable;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Extension TWIG pour generer un div contenant les infos permettant au JS de charger la table.
 *
 *
 * Class DatatableConfTwigExtension
 * @package Edulog\DatatablesBundle\Twig
 */
class DatatableConfTwigExtension extends AbstractExtension
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * SearchEngineTwigExtension constructor.
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return array|\Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('render_datatables', [$this, 'renderDataTables'], [
                'needs_environment' => true,
                'is_safe' => ['html']
            ]),
        ];
    }

    /**
     * @param \Twig_Environment $environment
     * @param DataTable $dataTable
     * @param string $id
     * @param array $options
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderDataTables(\Twig_Environment $environment, DataTable $dataTable, string $id, array $options = [])
    {
        return $environment->render('@EdulogDatatables/render_datatables.html.twig', [
            'datatable' => $dataTable,
            'id'        => $id,
            'options'   => $options
        ]);
    }
}
