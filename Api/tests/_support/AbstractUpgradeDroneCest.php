<?php

declare(strict_types=1);

namespace Mush\Tests;

use Mush\Action\Actions\AbstractUpgradeDrone;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;

abstract class AbstractUpgradeDroneCest extends AbstractFunctionalTest
{
    protected ActionConfig $actionConfig;
    protected AbstractUpgradeDrone $upgradeDrone;
    protected GameEquipmentServiceInterface $gameEquipmentService;
    protected Drone $drone;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->givenPlayerHasASupportDrone($this->chun);
        $this->setupDroneNicknameAndSerialNumber($this->drone, 0, 0);
    }

    public function shouldNotBeVisibleIfPlayerIsNotRoboticsExpert(FunctionalTester $I): void
    {
        $this->whenChunTriesToUpgradeDrone();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeVisibleIfDroneAlreadyHasUpgrade(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::ROBOTICS_EXPERT, $I);

        $this->givenPlayerHasPiecesOfScrapMetalOnReach($this->chun, 4);

        $this->givenChunUpgradesDrone();

        $this->whenChunTriesToUpgradeDrone();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeExecutableIfThereAreNotTwoScrapMetalOnReach(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::ROBOTICS_EXPERT, $I);

        $this->whenChunTriesToUpgradeDrone();

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::DRONE_UPGRADE_LACK_RESSOURCES, $I);
    }

    public function shouldCreateUpgradeStatus(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::ROBOTICS_EXPERT, $I);

        $this->givenPlayerHasPiecesOfScrapMetalOnReach($this->chun, 2);

        $this->whenPlayerUpgradesDrone($this->chun);

        $this->thenDroneShouldHaveUpgradeStatus($I);
    }

    public function shouldPrintAPublicLog(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::ROBOTICS_EXPERT, $I);

        $this->givenPlayerHasPiecesOfScrapMetalOnReach($this->chun, 2);

        $this->whenPlayerUpgradesDrone($this->chun);

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "**Chun** s'acharne un peu sur ce pauvre **Robo Wheatley #0**. Mais c'est pour son bien. **Robo Wheatley #0** reçoit l'amélioration **{$this->upgradeDrone->upgradeName()}**.",
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: $this->upgradeDrone->upgradeLog(),
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    public function shouldConsumeScrapMetal(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::ROBOTICS_EXPERT, $I);

        $this->givenPlayerHasPiecesOfScrapMetalOnReach($this->chun, 3);

        $this->whenPlayerUpgradesDrone($this->chun);

        $this->thenChunShouldHaveScrapMetalOnReach(1, $I);
    }

    public function shouldProvideTerrencePersonalTriumph(FunctionalTester $I): void
    {
        $terrence = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::TERRENCE);

        $this->addSkillToPlayer(SkillEnum::ROBOTICS_EXPERT, $I, $terrence);

        $this->givenPlayerHasASupportDrone($terrence);

        $this->givenPlayerHasPiecesOfScrapMetalOnReach($terrence, 2);

        $this->whenPlayerUpgradesDrone($terrence);

        $this->thenPlayerShouldHaveTriumph(4, $terrence, $I);
    }

    private function givenPlayerHasASupportDrone(Player $player): void
    {
        $this->drone = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SUPPORT_DRONE,
            equipmentHolder: $player,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenChunUpgradesDrone(): void
    {
        $this->whenPlayerUpgradesDrone($this->chun);
    }

    private function givenPlayerHasPiecesOfScrapMetalOnReach(Player $player, int $quantity): void
    {
        $this->gameEquipmentService->createGameEquipmentsFromName(
            equipmentName: ItemEnum::METAL_SCRAPS,
            equipmentHolder: $player,
            reasons: [],
            time: new \DateTime(),
            quantity: $quantity,
        );
    }

    private function whenPlayerUpgradesDrone(Player $player): void
    {
        $this->upgradeDrone->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->drone,
            player: $player,
            target: $this->drone,
        );
        $this->upgradeDrone->execute();
    }

    private function whenChunTriesToUpgradeDrone(): void
    {
        $this->upgradeDrone->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->drone,
            player: $this->chun,
            target: $this->drone,
        );
    }

    private function thenDroneShouldHaveUpgradeStatus(FunctionalTester $I): void
    {
        $I->assertTrue($this->drone->hasStatus($this->upgradeDrone->upgradeStatus()));
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->upgradeDrone->isVisible());
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->upgradeDrone->cannotExecuteReason());
    }

    private function thenChunShouldHaveScrapMetalOnReach(int $quantity, FunctionalTester $I): void
    {
        $chunScrapMetal = $this->chun->getEquipments()->filter(static fn (GameItem $item) => $item->getName() === ItemEnum::METAL_SCRAPS);
        $roomScrapMetal = $this->chun->getPlace()->getEquipments()->filter(static fn (GameItem $item) => $item->getName() === ItemEnum::METAL_SCRAPS);

        $actualQuantity = $chunScrapMetal->count() + $roomScrapMetal->count();

        $I->assertEquals($quantity, $actualQuantity);
    }

    private function thenPlayerShouldHaveTriumph(int $quantity, Player $player, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $player->getTriumph());
    }

    private function setupDroneNicknameAndSerialNumber(Drone $drone, int $nickName, int $serialNumber): void
    {
        $droneInfo = $drone->getDroneInfo();
        $ref = new \ReflectionClass($droneInfo);
        $ref->getProperty('nickName')->setValue($droneInfo, $nickName);
        $ref->getProperty('serialNumber')->setValue($droneInfo, $serialNumber);
    }
}
