<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\Entity\Mechanics\Document;

class DocumentConfigFixtures extends BlueprintConfigFixtures implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (MechanicsData::$dataArray as $documentData) {
            if ($documentData['type'] !== 'document') {
                continue;
            }

            $document = new Document();

            $document
                ->setName($documentData['name'])
                ->setContent($documentData['content'])
                ->setIsTranslated($documentData['isTranslated'])
                ->setCanShred($documentData['canShred']);
            $this->setMechanicsActions($document, $documentData, $manager);

            $manager->persist($document);

            $this->addReference($document->getName(), $document);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ActionsFixtures::class,
        ];
    }
}
