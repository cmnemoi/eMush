<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Modifier\Service\ModifierListenerService;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Factory\GameModifierFactory;
use Mush\Modifier\Service\FakeModifierCreationService;
use Mush\Modifier\Service\ModifierListenerService\DeletePlayerRelatedModifiersService;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DeletePlayerRelatedModifiersServiceTest extends TestCase
{
    private Player $player;
    private FakeModifierCreationService $modifierCreationService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->player = $this->givenAPlayer();
        $this->modifierCreationService = new FakeModifierCreationService();
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->modifierCreationService->clearRepository();
    }

    public function testShouldDeleteModifiersCreatedFromPlayerStatuses(): void
    {
        $modifier = $this->givenDaedalusHasAModifier();

        $this->givenPlayerHasAStatusHoldingModifierConfig($modifier);

        $this->whenIDeletePlayerRelatedModifiers();

        $this->IShouldNotSeeModifierInRepository($modifier);
    }

    public function testShouldDeleteModifiersCreatedFromPlayerSkills(): void
    {
        $modifier = $this->givenDaedalusHasAModifier();

        $this->givenPlayerHasASkillHoldingModifierConfig($modifier);

        $this->whenIDeletePlayerRelatedModifiers();

        $this->IShouldNotSeeModifierInRepository($modifier);
    }

    private function givenAPlayer(): Player
    {
        return PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());
    }

    private function givenDaedalusHasAModifier(): GameModifier
    {
        $modifier = GameModifierFactory::createByNameForHolder(
            name: 'modifier_for_daedalus_+1moral_on_day_change',
            holder: $this->player->getDaedalus()
        );
        $this->modifierCreationService->persist($modifier);

        return $modifier;
    }

    private function givenPlayerHasAStatusHoldingModifierConfig(GameModifier $modifier): Status
    {
        $status = StatusFactory::createStatusByNameForHolder(
            name: PlayerStatusEnum::IMMUNIZED,
            holder: $this->player
        );
        $status->getStatusConfig()->setModifierConfigs([$modifier->getModifierConfig()]);
        $this->player->addStatus($status);

        return $status;
    }

    private function givenPlayerHasASkillHoldingModifierConfig(GameModifier $modifier): void
    {
        new Skill(
            skillConfig: new SkillConfig(
                name: SkillEnum::MANKIND_ONLY_HOPE,
                modifierConfigs: new ArrayCollection([$modifier->getModifierConfig()])
            ),
            player: $this->player
        );
    }

    private function whenIDeletePlayerRelatedModifiers(): void
    {
        $service = new DeletePlayerRelatedModifiersService($this->modifierCreationService);
        $service->execute($this->player);
    }

    private function IShouldNotSeeModifierInRepository(GameModifier $modifier): void
    {
        $modifier = $this->modifierCreationService->findOneById($modifier->getId());

        self::assertTrue($modifier->isNull());
    }
}
