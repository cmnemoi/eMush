<?php

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Mechanics\Container;

class ContainerDataLoader extends MechanicsDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (MechanicsData::$dataArray as $containerData) {
            if ($containerData['type'] !== 'container') {
                continue;
            }

            $container = $this->mechanicsRepository->findOneBy(['name' => $containerData['name']]);

            if ($container === null) {
                $container = new Container();
            } elseif (!$container instanceof Container) {
                $this->entityManager->remove($container);
                $container = new Container();
            }

            $container->setName($containerData['name']);
            $this->setMechanicsActions($container, $containerData);
            $this->setContainerContents($container, $containerData['containerContents']);

            $this->entityManager->persist($container);
        }
        $this->entityManager->flush();
    }

    public function setContainerContents(Container $container, array $containerContents): void {}
}
