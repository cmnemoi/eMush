<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\SearchForMushGenome;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\TitleEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class SearchForMushGenomeCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private SearchForMushGenome $searchForMushGenome;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private GameEquipment $commsCenter;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SEARCH_FOR_MUSH_GENOME->value]);
        $this->searchForMushGenome = $I->grabService(SearchForMushGenome::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->givenPlaceHasCommsCenter();
    }

    public function shouldNotBeExecutableIfPlayerIsNotCommsOfficer(FunctionalTester $I): void
    {
        $this->whenPlayerSearchesForMushGenome();

        $this->thenActionShouldNotBeExecutableWithMessage($I, ActionImpossibleCauseEnum::TERMINAL_ROLE_RESTRICTED);
    }

    public function shouldSpawnMushGenomeDiskOnSuccess(FunctionalTester $I): void
    {
        $this->givenPlayerIsCommsOfficer();

        $this->givenActionSuccessRateIs(100);

        $this->whenPlayerSearchesForMushGenome();

        $this->thenPlaceShouldHaveMushGenomeDisk($I);
    }

    private function givenPlaceHasCommsCenter(): void
    {
        $this->commsCenter = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::COMMUNICATION_CENTER,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerIsCommsOfficer(): void
    {
        $this->player->addTitle(TitleEnum::COM_MANAGER);
    }

    private function givenActionSuccessRateIs(int $successRate): void
    {
        $this->actionConfig->setSuccessRate($successRate);
    }

    private function whenPlayerSearchesForMushGenome(): void
    {
        $this->searchForMushGenome->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->commsCenter,
            player: $this->player,
            target: $this->commsCenter,
        );
        $this->searchForMushGenome->execute();
    }

    private function thenPlaceShouldHaveMushGenomeDisk(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->getPlace()->hasEquipmentByName(ItemEnum::MUSH_GENOME_DISK));
    }

    private function thenActionShouldNotBeExecutableWithMessage(FunctionalTester $I, string $message): void
    {
        $I->assertEquals($message, $this->searchForMushGenome->cannotExecuteReason());
    }
}
