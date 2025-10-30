<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\BecomeAnonymous;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class BecomeAnonymousCest extends AbstractFunctionalTest
{
    private BecomeAnonymous $actionBecomeAnonymous;
    private ActionConfig $actionConfigBecomeAnonymous;
    private Take $actionTake;
    private ActionConfig $actionConfigTake;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameItem $kubinus;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfigBecomeAnonymous = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::BECOME_ANONYMOUS]);
        $this->actionBecomeAnonymous = $I->grabService(BecomeAnonymous::class);

        $this->actionConfigTake = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TAKE]);
        $this->actionTake = $I->grabService(Take::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::NINJA, $I, $this->player);
        $this->kubinus = $this->gameEquipmentService->createGameEquipmentFromName(GameFruitEnum::KUBINUS, $this->player->getPlace(), [], new \DateTime());
    }

    public function becomeAnonymousShouldGiveAnonymousStatus(FunctionalTester $I): void
    {
        $this->whenChunBecomeAnonymous();

        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::IS_ANONYMOUS));
    }

    public function becomeAnonymousShouldRemoveAnonymousStatusIfAlreadyAnonymous(FunctionalTester $I): void
    {
        $this->givenChunIsAnonymous();

        $this->whenChunBecomeAnonymous();

        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::IS_ANONYMOUS));
    }

    public function logsShouldBeAnonymousWithStatusIsAnonymous(FunctionalTester $I): void
    {
        $this->givenChunIsAnonymous();

        $this->whenChunTakeTheKubinus();

        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'log' => ActionLogEnum::TAKE,
            ]
        );

        $character = $roomLog->getParameters()['character'];

        $I->assertEquals($character, CharacterEnum::SOMEONE);
    }

    private function whenChunBecomeAnonymous(): void
    {
        $this->actionBecomeAnonymous->loadParameters(
            $this->actionConfigBecomeAnonymous,
            $this->player,
            $this->player,
        );

        $this->actionBecomeAnonymous->execute();
    }

    private function givenChunIsAnonymous(): void
    {
        $this->statusService->createStatusFromName(
            PlayerStatusEnum::IS_ANONYMOUS,
            $this->player,
            [],
            new \DateTime(),
        );
    }

    private function whenChunTakeTheKubinus(): void
    {
        $this->actionTake->loadParameters(
            $this->actionConfigTake,
            $this->kubinus,
            $this->player,
            $this->kubinus
        );

        $this->actionTake->execute();
    }
}
