<?php

namespace Mush;

use Mush\Action\DependencyInjection\ActionPass;
use Mush\Disease\DependencyInjection\SymptomHandlerPass;
use Mush\Equipment\DependencyInjection\EquipmentCycleHandlerPass;
use Mush\Modifier\DependencyInjection\ModifierHandlerPass;
use Mush\Status\DependencyInjection\ChargeStrategyPass;
use Mush\Status\DependencyInjection\StatusCycleHandlerPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/' . $this->environment . '/*.yaml');

        if (is_file(\dirname(__DIR__) . '/config/services.yaml')) {
            $container->import('../config/services.yaml');
            $container->import('../config/{services}_' . $this->environment . '.yaml');
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/' . $this->environment . '/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__) . '/config/routes.yaml')) {
            $routes->import('../config/routes.yaml');
        }
    }

    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new ActionPass());
        $container->addCompilerPass(new ChargeStrategyPass());
        $container->addCompilerPass(new StatusCycleHandlerPass());
        $container->addCompilerPass(new EquipmentCycleHandlerPass());
        $container->addCompilerPass(new SymptomHandlerPass());
        $container->addCompilerPass(new ModifierHandlerPass());
    }
}
