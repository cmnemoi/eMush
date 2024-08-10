<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Infect;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class InfectCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Infect $infect;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::INFECT->value]);
        $this->infect = $I->grabService(Infect::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenKuanTiIsMush();
        $this->addSkillToPlayer(SkillEnum::INFECTOR, $I, $this->kuanTi);
    }

    public function shouldBeAbleToInfectTwiceADay(FunctionalTester $I)
    {
        $this->givenKuanTiInfects();

        $this->whenKuanTiTriesToInfect();

        $this->thenActionShouldBeExecutable($I);
    }

    private function givenKuanTiIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
        $this->kuanTi->setSpores(2);
    }

    private function givenKuanTiInfects(): void
    {
        $this->infect->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->player,
        );
        $this->infect->execute();
    }

    private function whenKuanTiTriesToInfect(): void
    {
        $this->infect->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->player,
        );
    }

    private function thenActionShouldBeExecutable(FunctionalTester $I): void
    {
        $I->assertNull($this->infect->cannotExecuteReason());
    }
}
