<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Putsch;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\TitlePriority;
use Mush\Game\Enum\TitleEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PutschCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Putsch $putsch;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::PUTSCH]);
        $this->putsch = $I->grabService(Putsch::class);

        $this->addSkillToPlayer(SkillEnum::POLITICIAN, $I, $this->chun);
    }

    public function shouldPutPlayerInFirstPlaceForCommanderTitle(FunctionalTester $I): void
    {
        $this->whenChunPutsches();

        $this->thenChunShouldBeInFirstPlaceForCommanderTitle($I);
    }

    private function whenChunPutsches(): void
    {
        $this->putsch->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
        );
        $this->putsch->execute();
    }

    private function thenChunShouldBeInFirstPlaceForCommanderTitle(FunctionalTester $I): void
    {
        $commanderTitlePriorities = $this->daedalus
            ->getTitlePriorities()
            ->filter(static fn (TitlePriority $titlePriority) => $titlePriority->getName() === TitleEnum::COMMANDER)
            ->first()
            ->getPriority();

        $I->assertEquals($this->chun->getName(), $commanderTitlePriorities[0]);
    }
}
