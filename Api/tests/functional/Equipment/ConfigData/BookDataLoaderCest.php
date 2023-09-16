<?php

namespace Mush\Tests\functional\Equipment\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Equipment\ConfigData\BookDataLoader;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Tests\FunctionalTester;

class BookDataLoaderCest
{
    private BookDataLoader $bookDataLoader;
    private ActionDataLoader $actionDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->actionDataLoader = $I->grabService(ActionDataLoader::class);
        $this->actionDataLoader->loadConfigsData();

        $this->bookDataLoader = $I->grabService(BookDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->bookDataLoader->loadConfigsData();

        foreach (MechanicsData::$dataArray as $bookData) {
            if ($bookData['type'] !== 'book') {
                continue;
            }
            $bookData = $this->dropFields($bookData);
            $I->seeInRepository(Book::class, $bookData);
        }

        $I->seeNumRecords($this->getNumberOfBooks(), Book::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = [
            'name' => 'apprenton_astrophysicist_default',
            'skill' => 'astrophysicist',
        ];

        $config = $this->dropFields($config);

        $this->bookDataLoader->loadConfigsData();

        $I->seeNumRecords(1, Book::class, $config);
    }

    /** need to drop those fields because they are not in the Book entity.
     */
    private function dropFields(array $configData): array
    {
        // drop everything instead name field
        $configData = array_filter($configData, function ($key) {
            return $key === 'name'
            || $key === 'skill';
        }, ARRAY_FILTER_USE_KEY);

        return $configData;
    }

    /**
     * MechanicsData::$dataArray contains all the MechanicsData, including the ones that are not Book,
     * so this method returns the number of Book in the array.
     */
    private function getNumberOfBooks(): int
    {
        $configs = new ArrayCollection(MechanicsData::$dataArray);
        $books = $configs->filter(function ($config) {
            return $config['type'] === 'book';
        });

        return $books->count();
    }
}
