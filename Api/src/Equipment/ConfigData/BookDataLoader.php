<?php

namespace Mush\Equipment\ConfigData;

use Mush\Equipment\Entity\Mechanics\Book;

class BookDataLoader extends MechanicsDataLoader
{
    public function loadConfigsData(): void
    {
        foreach (MechanicsData::$dataArray as $bookData) {
            if ($bookData['type'] !== 'book') {
                continue;
            }

            $book = $this->mechanicsRepository->findOneBy(['name' => $bookData['name']]);

            if ($book !== null) {
                continue;
            }

            $book = new Book();
            $book
                ->setName($bookData['name'])
                ->setSkill($bookData['skill'])
            ;
            $this->setMechanicsActions($book, $bookData);

            $this->entityManager->persist($book);
        }
        $this->entityManager->flush();
    }
}
