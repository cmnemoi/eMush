<?php

namespace Mush\Tests\functional\Equipment\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Equipment\ConfigData\DocumentDataLoader;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Tests\FunctionalTester;

class DocumentDataLoaderCest
{
    private DocumentDataLoader $documentDataLoader;
    private ActionDataLoader $actionDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->actionDataLoader = $I->grabService(ActionDataLoader::class);
        $this->actionDataLoader->loadConfigsData();

        $this->documentDataLoader = $I->grabService(DocumentDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->documentDataLoader->loadConfigsData();

        foreach (MechanicsData::$dataArray as $documentData) {
            if ($documentData['type'] !== 'document') {
                continue;
            }
            $documentData = $this->dropFields($documentData);
            $I->seeInRepository(Document::class, $documentData);
        }

        $I->seeNumRecords($this->getNumberOfDocuments(), Document::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = [
            'name' => 'document_default',
            'content' => null,
            'isTranslated' => true,
            'canShred' => true,
        ];

        $config = $this->dropFields($config);

        $this->documentDataLoader->loadConfigsData();

        $I->seeNumRecords(1, Document::class, $config);
    }

    /** need to drop those fields because they are not in the Document entity.
     */
    private function dropFields(array $configData): array
    {
        $configData = array_filter($configData, function ($key) {
            return $key === 'name'
            || $key === 'content'
            || $key === 'isTranslated'
            || $key === 'canShred';
        }, ARRAY_FILTER_USE_KEY);

        return $configData;
    }

    /**
     * MechanicsData::$dataArray contains all the MechanicsData, including the ones that are not Document,
     * so this method returns the number of Document in the array.
     */
    private function getNumberOfDocuments(): int
    {
        $configs = new ArrayCollection(MechanicsData::$dataArray);
        $documents = $configs->filter(function ($config) {
            return $config['type'] === 'document';
        });

        return $documents->count();
    }
}
