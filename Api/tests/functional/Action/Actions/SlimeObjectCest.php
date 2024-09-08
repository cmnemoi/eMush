<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class SlimeObjectCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private SlimeObject $slimeObject;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameItem $blaster;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SLIME_OBJECT]);
        $this->slimeObject = $I->grabService(SlimeObject::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->blaster = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::BLASTER,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );
    }

    public function shouldCreateSlimedObjectStatus(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::GREEN_JELLY, $I, $this->kuanTi);

        $this->whenKuanTiSlimesObject();

        $this->thenBlasterShouldHaveSlimedStatus($I);
    }

    public function shouldCreateCovertLog(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::GREEN_JELLY, $I, $this->kuanTi);

        $delay = $this->whenKuanTiSlimesObject()->getQuantity();
        $delayString = $delay > 1 ? "{$delay} cycles" : "{$delay} cycle";

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: ":mush: **Kuan Ti** projette rapidement une infâme gelée sur un **Blaster**. Il sera cassé d'ici {$delayString}...",
            actualRoomLogDto: new RoomLogDto(
                player: $this->kuanTi,
                log: ActionLogEnum::SLIME_OBJECT_SUCCESS,
                visibility: VisibilityEnum::COVERT,
                inPlayerRoom: false
            ),
            I: $I
        );
    }

    public function shouldNotBeVisibleIfPlayerDoesNotHaveGreenJellySkill(FunctionalTester $I): void
    {
        $this->whenKuanTiSlimesObject();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotBeExecutableIfEquipmentAlreadySlimed(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::GREEN_JELLY, $I, $this->kuanTi);

        $this->givenBlasterAlreadySlimed();

        $this->whenKuanTiSlimesObject();

        $this->thenActionShouldNotBeExecutableWithMessage(
            ActionImpossibleCauseEnum::SLIME_ALREADY_DONE,
            $I
        );
    }

    public function shouldBreakEquipmentWhenSlimedStatusIsRemoved(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::GREEN_JELLY, $I, $this->kuanTi);

        $this->givenKuanTiSlimesObject();

        $this->whenSlimedStatusIsRemoved();

        $this->thenBlasterShouldBeBroken($I);
    }

    public function shouldCreateNeronAnnouncementWhenSlimedStatusIsRemoved(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::GREEN_JELLY, $I, $this->kuanTi);

        $this->givenKuanTiSlimesObject();

        $this->whenSlimedStatusIsRemoved();

        $announcement = $I->grabEntityFromRepository(
            entity: Message::class,
            params: [
                'neron' => $this->daedalus->getNeron(),
                'message' => NeronMessageEnum::BROKEN_EQUIPMENT,
            ]
        );
        $I->assertEquals($announcement->getTranslationParameters()['target_item'], ItemEnum::BLASTER);
    }

    private function givenBlasterAlreadySlimed(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::SLIMED,
            holder: $this->blaster,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenKuanTiSlimesObject(): void
    {
        $this->slimeObject->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->blaster,
            player: $this->kuanTi,
            target: $this->blaster,
        );
        $this->slimeObject->execute();
    }

    private function whenKuanTiSlimesObject(): ActionResult
    {
        $this->slimeObject->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->blaster,
            player: $this->kuanTi,
            target: $this->blaster,
        );

        return $this->slimeObject->execute();
    }

    private function whenSlimedStatusIsRemoved(): void
    {
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::SLIMED,
            holder: $this->blaster,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function thenBlasterShouldHaveSlimedStatus(FunctionalTester $I): void
    {
        $I->assertTrue($this->blaster->hasStatus(EquipmentStatusEnum::SLIMED));
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->slimeObject->isVisible());
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $expectedMessage, FunctionalTester $I): void
    {
        $I->assertEquals($expectedMessage, $this->slimeObject->cannotExecuteReason());
    }

    private function thenBlasterShouldBeBroken(FunctionalTester $I): void
    {
        $I->assertTrue($this->blaster->isBroken());
    }
}
