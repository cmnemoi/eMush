<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action;

use Mush\Action\Actions\GenMetal;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ClumsinessShouldNotInfectCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private GenMetal $genMetal;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::GEN_METAL->value]);
        $this->genMetal = $I->grabService(GenMetal::class);
        $this->actionConfig->setInjuryRate(100);
        $this->addSkillToPlayer(SkillEnum::METALWORKER, $I);
    }

    public function shouldNotInfect(FunctionalTester $I): void
    {
        $this->givenPlayerIsInAStorage($I);

        $this->givenPlayerHasSpores(0);

        $this->whenPlayerExecutesAction();

        $this->thenPlayerShouldHaveSpores(0, $I);
    }

    private function givenPlayerIsInAStorage(FunctionalTester $I): void
    {
        $storage = $this->createExtraPlace(RoomEnum::FRONT_STORAGE, $I, $this->daedalus);
        $this->player->changePlace($storage);
    }

    private function givenPlayerHasSpores(int $spores): void
    {
        $this->player->setSpores(0);
    }

    private function whenPlayerExecutesAction(): void
    {
        $this->genMetal->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player,
        );
        $this->genMetal->execute();
    }

    private function thenPlayerShouldHaveSpores(int $spores, FunctionalTester $I): void
    {
        $I->assertEquals($spores, $this->player->getSpores());
    }
}
