<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Skill\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Factory\GameModifierFactory;
use Mush\Modifier\Service\FakeModifierCreationService;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryPlayerRepository;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Repository\SkillRepositoryInterface;
use Mush\Skill\Service\DeletePlayerSkillService;
use Mush\Status\Enum\SkillPointsEnum;
use Mush\Status\Service\FakeStatusService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DeletePlayerSkillServiceTest extends TestCase
{
    private DeletePlayerSkillService $deletePlayerSkillService;
    private FakeModifierCreationService $modifierCreationService;
    private FakeStatusService $statusService;
    private Player $player;
    private InMemoryPlayerRepository $playerRepository;
    private InMemorySkillRepository $skillRepository;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->modifierCreationService = new FakeModifierCreationService();
        $this->statusService = new FakeStatusService();
        $this->playerRepository = new InMemoryPlayerRepository();
        $this->skillRepository = new InMemorySkillRepository();
        $this->player = PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());

        $this->deletePlayerSkillService = new DeletePlayerSkillService(
            $this->createStub(EventServiceInterface::class),
            $this->modifierCreationService,
            $this->skillRepository,
            $this->statusService,
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->player = PlayerFactory::createNullPlayer();
        $this->modifierCreationService->clearRepository();
        $this->statusService->clearRepository();
        $this->playerRepository->clear();
        $this->skillRepository->clear();
    }

    public function testShouldDeletePlayerSkill(): void
    {
        $this->givenPlayerHasSkill(SkillEnum::APPRENTICE);

        $this->whenIDeleteSkill(SkillEnum::APPRENTICE);

        $this->thenPlayerShouldNotHaveSkill(SkillEnum::APPRENTICE);
    }

    public function testShouldThrowIfPlayerDoesNotHaveSkill(): void
    {
        $this->expectException(\Exception::class);

        $this->whenIDeleteSkill(SkillEnum::NULL);
    }

    public function testShouldDeletePlaceRangedSkillModifiers(): void
    {
        $modifier = $this->givenPlayerHasSkillWithPlaceRangedModifier(
            SkillEnum::SHRINK,
            'modifier_for_player_+1morale_point_on_new_cycle_if_lying_down',
        );

        $this->whenIDeleteSkill(SkillEnum::SHRINK);

        $this->thenIShouldNotSeeModifierInRepository($modifier);
    }

    public function testShouldDeletePlayerRangedSkillModifiers(): void
    {
        $modifier = $this->givenPlayerHasSkillWithPlayerRangedModifier(
            SkillEnum::MYCOLOGIST,
            ModifierNameEnum::PLAYER_MINUS_1_SPORE_ON_HEAL,
        );

        $this->whenIDeleteSkill(SkillEnum::MYCOLOGIST);

        $this->thenIShouldNotSeeModifierInRepository($modifier);
    }

    public function testShouldDeleteDaedalusRangedSkillModifiers(): void
    {
        $modifier = $this->givenPlayerHasSkillWithDaedalusRangedModifier(
            SkillEnum::MANKIND_ONLY_HOPE,
            'modifier_for_daedalus_+1moral_on_day_change',
        );

        $this->whenIDeleteSkill(SkillEnum::MANKIND_ONLY_HOPE);

        $this->thenIShouldNotSeeModifierInRepository($modifier);
    }

    public function testShouldDeleteSkillPoints(): void
    {
        $this->givenPlayerHasSkillWithPoints(SkillEnum::TECHNICIAN);

        $this->whenIDeleteSkill(SkillEnum::TECHNICIAN);

        $this->thenIShouldNotSeeSkillPointsInRepository(SkillPointsEnum::TECHNICIAN_POINTS);
    }

    private function givenPlayerHasSkill(SkillEnum $skill): void
    {
        $skill = Skill::createByNameForPlayer($skill, $this->player);
        $this->playerRepository->save($this->player);
    }

    private function givenPlayerHasSkillWithPlayerRangedModifier(SkillEnum $skill, string $modifierName): GameModifier
    {
        $modifier = GameModifierFactory::createByNameForHolder(
            name: $modifierName,
            holder: $this->player,
        );
        $this->modifierCreationService->persist($modifier);

        new Skill(
            skillConfig: new SkillConfig(
                name: $skill,
                modifierConfigs: new ArrayCollection([$modifier->getModifierConfig()])
            ),
            player: $this->player
        );

        return $modifier;
    }

    private function givenPlayerHasSkillWithPlaceRangedModifier(SkillEnum $skill, string $modifierName): GameModifier
    {
        $modifier = GameModifierFactory::createByNameForHolder(
            name: $modifierName,
            holder: $this->player->getPlace(),
        );
        $this->modifierCreationService->persist($modifier);

        new Skill(
            skillConfig: new SkillConfig(
                name: $skill,
                modifierConfigs: new ArrayCollection([$modifier->getModifierConfig()])
            ),
            player: $this->player
        );

        return $modifier;
    }

    private function givenPlayerHasSkillWithDaedalusRangedModifier(SkillEnum $skill, string $modifierName): GameModifier
    {
        $modifier = GameModifierFactory::createByNameForHolder(
            name: $modifierName,
            holder: $this->player->getDaedalus(),
        );
        $this->modifierCreationService->persist($modifier);

        new Skill(
            skillConfig: new SkillConfig(
                name: $skill,
                modifierConfigs: new ArrayCollection([$modifier->getModifierConfig()])
            ),
            player: $this->player
        );

        return $modifier;
    }

    private function givenPlayerHasSkillWithPoints(SkillEnum $skillName): void
    {
        $this->givenPlayerHasSkill($skillName);
        $skill = $this->player->getSkillByNameOrThrow($skillName);

        $this->statusService->createStatusFromName(
            statusName: SkillPointsEnum::fromSkill($skill)->toString(),
            holder: $this->player,
            tags: [],
            time: new \DateTime()
        );
    }

    private function whenIDeleteSkill(SkillEnum $skill): void
    {
        $this->deletePlayerSkillService->execute(skillName: $skill, player: $this->player);
    }

    private function thenPlayerShouldNotHaveSkill(SkillEnum $skill): void
    {
        $player = $this->playerRepository->findOneByName($this->player->getName());
        self::assertFalse($player?->hasSkill($skill));
    }

    private function thenIShouldNotSeeModifierInRepository(GameModifier $modifier): void
    {
        $modifier = $this->modifierCreationService->findOneById($modifier->getId());

        self::assertTrue($modifier->isNull());
    }

    private function thenIShouldNotSeeSkillPointsInRepository(SkillPointsEnum $skillPoints): void
    {
        $player = $this->playerRepository->findOneByName($this->player->getName());
        self::assertFalse($player?->hasStatus($skillPoints->toString()));
    }
}
final class InMemorySkillRepository implements SkillRepositoryInterface
{
    private array $skills = [];

    public function delete(Skill $skill): void
    {
        $player = $skill->getPlayer();
        $player->removeSkill($skill);

        unset($this->skills[$skill->getName()->toString()]);
    }

    public function save(Skill $skill): void
    {
        $this->skills[$skill->getName()->toString()] = $skill;
    }

    public function clear(): void
    {
        $this->skills = [];
    }
}
