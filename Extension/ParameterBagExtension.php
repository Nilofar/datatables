<?php

namespace Edulog\DatatablesBundle\Extension;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\DependencyInjection\Reference;

/**
 *  Cette classe permet d'executer du code pendant la compilation, ici cela permet de créer le bag $bagName
 * en session afin qu'il soit utilisé par le DtFilter pour sauvegarder des objets
 *
 * Class ParameterBagExtension
 * @package Edulog\DatatablesBundle\Extension
 */
class ParameterBagExtension implements CompilerPassInterface
{
    protected $_bagName;
    protected $_class;

    /**
     * ParameterBagExtension constructor.
     * @param string $class : La classe de l'objet qui sera sauvegarder en session (MyClass::class)
     */
    public function __construct(string $class = null)
    {
        $this->_class = $class;
    }

    /**
     * Méthode appelé via AppBundle.php afin d'ajouter le bag $bagName dans la session.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (null !== $this->_class) {
            // Nouvelle Definition
            $bagDefinition = new Definition();
            // Defini la classe de la definition : AttributeBag
            $bagDefinition->setClass(AttributeBag::class);
            //$bagDefinition->addArgument("absence");
            // Defini le nom de l'attributeBag -> SearchEngine
            $bagDefinition->addMethodCall("setName", ['dt_filter']);
            $bagDefinition->setPublic(true);

            // Ajoute l'attributeBag au container
            $container->setDefinition($this->_class, $bagDefinition);

            // Récupère la session puis ajoute au registerBag l'objet correspondant à la classe passée en paramètre
            $container->getDefinition("session")
                ->addMethodCall("registerBag", [new Reference($this->_class)]);
        }
    }
}
