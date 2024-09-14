<?php

declare(strict_types=1);

namespace Mush\tests\unit\Skill\Service;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Exception\GameException;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryPlayerRepository;
use Mush\Skill\ConfigData\SkillConfigData;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Repository\InMemorySkillConfigRepository;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Status\Enum\SkillPointsEnum;
use Mush\Status\Service\FakeStatusService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AddSkillToPlayerServiceTest extends TestCase
{
    private Player $player;
    private InMemoryPlayerRepository $playerRepository;
    private InMemorySkillConfigRepository $skillConfigRepository;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->playerRepository = new InMemoryPlayerRepository();
        $this->skillConfigRepository = new InMemorySkillConfigRepository();
        $this->skillConfigRepository->save(SkillConfig::createFromDto(SkillConfigData::getByName(SkillEnum::TECHNICIAN)));
        $this->skillConfigRepository->save(SkillConfig::createFromDto(SkillConfigData::getByName(SkillEnum::SHOOTER)));

        $this->player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ANDIE, DaedalusFactory::createDaedalus());
        $this->playerRepository->save($this->player);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->player = PlayerFactory::createNullPlayer();
        $this->playerRepository->clear();
        $this->skillConfigRepository->clear();
    }

    public function testShouldAddSkillToPlayer(): void
    {
        $this->whenIAddSkillToPlayer(SkillEnum::TECHNICIAN);

        $this->thenPlayerShouldHaveSkill(SkillEnum::TECHNICIAN);
    }

    public function testShouldThrowIfSkillNotFound(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->whenIAddSkillToPlayer(SkillEnum::NULL);
    }

    public function testShouldThrowIfPlayerAlreadyHasSkill(): void
    {
        $this->givenPlayerHasSkill(SkillEnum::PILOT);

        $this->expectException(GameException::class);

        $this->whenIAddSkillToPlayer(SkillEnum::PILOT);
    }

    public function testShouldCreateSkillPoints(): void
    {
        $this->whenIAddSkillToPlayer(SkillEnum::SHOOTER);

        $this->thenPlayerShouldHaveSkillPoints(SkillPointsEnum::SHOOTER_POINTS);
    }

    private function givenPlayerHasSkill(SkillEnum $skill): void
    {
        Skill::createByNameForPlayer($skill, $this->player);
    }

    private function whenIAddSkillToPlayer(SkillEnum $skill): void
    {
        $service = new AddSkillToPlayerService(
            $this->createStub(GameEquipmentServiceInterface::class),
            $this->createStub(ModifierCreationServiceInterface::class),
            $this->playerRepository,
            $this->skillConfigRepository,
            new FakeStatusService(),
        );
        $service->execute(skill: $skill, player: $this->player);
    }

    private function thenPlayerShouldHaveSkill(SkillEnum $skill): void
    {
        $player = $this->playerRepository->findOneByName(CharacterEnum::ANDIE);

        self::assertTrue($player->hasSkill($skill));
    }

    private function thenPlayerShouldHaveSkillPoints(SkillPointsEnum $skillPoints): void
    {
        $player = $this->playerRepository->findOneByName(CharacterEnum::ANDIE);
        $skillPointsStatus = $player->getStatusByName($skillPoints->toString());

        self::assertNotNull($skillPointsStatus);
    }
}
