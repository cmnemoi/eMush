<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Ceasefire;
use Mush\Action\Actions\Hit;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class CeasefireCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Ceasefire $ceasefire;
    private ChooseSkillUseCase $chooseSkillUseCase;
    private Hit $hit;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::CEASEFIRE]);
        $this->ceasefire = $I->grabService(Ceasefire::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->hit = $I->grabService(Hit::class);

        $this->givenChunIsADiplomat($I);
    }

    public function shouldCreateCeasefireStatusInTheRoom(FunctionalTester $I): void
    {
        $this->whenChunCeasefires($I);

        $this->thenCeasefireStatusIsCreatedInTheRoom($I);
    }

    public function shouldPrintAPublicLog(FunctionalTester $I): void
    {
        $this->whenChunCeasefires();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "**Chun** impose un cessez-le-feu dans la pièce. Quelle autorité !",
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: ActionLogEnum::CEASEFIRE_SUCCESS,
                visibility: VisibilityEnum::PUBLIC,
            ),
            I: $I,
        );
    }

    public function shouldPreventAggressiveActionsInRoom(FunctionalTester $I): void
    {
        $this->givenChunCeasefires();

        $this->whenChunWantsToHitKuanTi($I);

        $this->thenHitActionShouldNotBeExecutableWithMessage(message: ActionImpossibleCauseEnum::CEASEFIRE, I: $I);
    }

    private function givenChunIsADiplomat(FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::DIPLOMAT]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::DIPLOMAT, $this->player));
    }

    private function givenChunCeasefires(): void
    {
        $this->whenChunCeasefires();
    }

    private function whenChunCeasefires(): void
    {
        $this->ceasefire->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player,
            target: null,
        );
        $this->ceasefire->execute();
    }

    private function whenChunWantsToHitKuanTi(FunctionalTester $I): void
    {
        $this->hit->loadParameters(
            actionConfig: $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::HIT]),
            actionProvider: $this->player,
            player: $this->player,
            target: $this->kuanTi,
        );
    }

    private function thenCeasefireStatusIsCreatedInTheRoom(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->getPlace()->hasStatus(PlaceStatusEnum::CEASEFIRE->toString()));
    }

    private function thenHitActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $message,
            actual: $this->hit->cannotExecuteReason(),
        );
    }
}
