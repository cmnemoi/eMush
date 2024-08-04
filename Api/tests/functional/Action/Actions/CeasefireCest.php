<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Ceasefire;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CeasefireCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Ceasefire $ceasefire;
    private ChooseSkillUseCase $chooseSkillUseCase;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::CEASEFIRE]);
        $this->ceasefire = $I->grabService(Ceasefire::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);

        $this->givenChunIsADiplomat($I);
    }

    public function shouldCreateCeasefireStatusInTheRoom(FunctionalTester $I): void
    {
        $this->whenChunCeasefires();

        $this->thenCeasefireStatusIsCreatedInTheRoom($I);
    }

    private function givenChunIsADiplomat(FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::DIPLOMAT]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::DIPLOMAT, $this->player));
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

    private function thenCeasefireStatusIsCreatedInTheRoom(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->getPlace()->hasStatus(PlaceStatusEnum::CEASEFIRE->toString()));
    }
}
