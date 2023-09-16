<?php

namespace Mush\Tests\functional\Equipment\ConfigData;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Equipment\ConfigData\MechanicsData;
use Mush\Equipment\ConfigData\ToolDataLoader;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Tests\FunctionalTester;

class ToolDataLoaderCest
{
    private ToolDataLoader $toolDataLoader;
    private ActionDataLoader $actionDataLoader;

    public function _before(FunctionalTester $I)
    {
        $this->actionDataLoader = $I->grabService(ActionDataLoader::class);
        $this->actionDataLoader->loadConfigsData();

        $this->toolDataLoader = $I->grabService(ToolDataLoader::class);
    }

    public function testLoadConfigsData(FunctionalTester $I)
    {
        $this->toolDataLoader->loadConfigsData();

        foreach (MechanicsData::$dataArray as $toolData) {
            if ($toolData['type'] !== 'tool') {
                continue;
            }
            $toolData = $this->dropFields($toolData);
            $I->seeInRepository(Tool::class, $toolData);
        }

        // TODO: fix this test
        // $I->seeNumRecords($this->getNumberOfTools(), Tool::class);
    }

    public function testLoadConfigsDataDefaultConfigAlreadyExists(FunctionalTester $I)
    {
        $config = [
            'name' => 'tools_hacker_kit_default',
        ];

        $config = $this->dropFields($config);

        $this->toolDataLoader->loadConfigsData();

        $I->seeNumRecords(1, Tool::class, $config);
    }

    /** need to drop those fields because they are not in the Tool entity.
     */
    private function dropFields(array $configData): array
    {
        // drop everything instead name field
        $configData = array_filter($configData, function ($key) {
            return $key === 'name';
        }, ARRAY_FILTER_USE_KEY);

        return $configData;
    }

    /**
     * MechanicsData::$dataArray contains all the MechanicsData, including the ones that are not Tool,
     * so this method returns the number of Tool in the array.
     */
    private function getNumberOfTools(): int
    {
        $configs = new ArrayCollection(MechanicsData::$dataArray);
        $tools = $configs->filter(function ($config) {
            return $config['type'] === 'tool';
        });

        return $tools->count();
    }
}
