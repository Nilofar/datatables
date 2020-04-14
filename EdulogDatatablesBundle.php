<?php
namespace Edulog\DatatablesBundle;

use Edulog\DatatablesBundle\Extension\ParameterBagExtension;
use Edulog\DatatablesBundle\Service\DtFilter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EdulogDatatablesBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ParameterBagExtension(DtFilter::class));
    }
}
