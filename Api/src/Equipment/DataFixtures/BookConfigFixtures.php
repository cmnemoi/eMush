<?php

namespace Mush\Equipment\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\DataFixtures\ActionsFixtures;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\Entity\Mechanics\Book;

class BookConfigFixtures extends BlueprintConfigFixtures implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (MechanicsData::$dataArray as $bookData) {
            if ($bookData['type'] !== 'book') {
                continue;
            }

            $book = new Book();

            $book
                ->setName($bookData['name'])
                ->setSkill($bookData['skill']);
            $this->setMechanicsActions($book, $bookData, $manager);

            $manager->persist($book);
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
