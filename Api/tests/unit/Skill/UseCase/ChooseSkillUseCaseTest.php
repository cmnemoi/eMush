<?php

declare(strict_types=1);

namespace Mush\tests\unit\Skill\UseCase;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Exception\GameException;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryPlayerRepository;
use Mush\Skill\ConfigData\SkillConfigData;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Repository\InMemorySkillConfigRepository;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\SkillPointsEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Status\Service\FakeStatusService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ChooseSkillUseCaseTest extends TestCase
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
        $this->initSkillConfigs();

        $this->player = $this->givenAPlayer();
        $this->playerRepository->save($this->player);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->playerRepository->clear();
    }

    public function testShouldAddSkillToPlayer(): void
    {
        $this->whenIChooseSkill(SkillEnum::PILOT);

        $this->thenPlayerShouldHaveSkill(SkillEnum::PILOT);
    }

    public function testShouldAddMushSkillToMushPlayer(): void
    {
        $this->givenPlayerIsMush();

        $this->whenIChooseSkill(SkillEnum::ANONYMUSH);

        $this->thenPlayerShouldHaveSkill(SkillEnum::ANONYMUSH);
    }

    public function testShouldThrowWhenTryingToChooseSkillIfNotInPlayerSkillConfigs(): void
    {
        $this->expectException(GameException::class);

        $this->whenIChooseSkill(SkillEnum::CREATIVE);
    }

    public function testShouldThrowWhenTryingToChooseSkillAlreadyTaken(): void
    {
        $this->givenPlayerHasSkill(SkillEnum::PILOT);

        $this->expectException(GameException::class);

        $this->whenIChooseSkill(SkillEnum::PILOT);
    }

    public function testShouldCreateSkillPoints(): void
    {
        $this->whenIChooseSkill(SkillEnum::SHOOTER);

        $this->thenPlayerShouldHaveSkillPoints(SkillPointsEnum::SHOOTER_POINTS);
    }

    public function testShouldThrowIfPlayerDoesNotHaveEmptyHumanSkillSlot(): void
    {
        $this->givenHumanSkillSlotsNumberIs(1);

        $this->givenPlayerHasSkill(SkillEnum::PILOT);

        $this->expectException(GameException::class);

        $this->whenIChooseSkill(SkillEnum::TECHNICIAN);
    }

    public function testShouldThrowIfPlayerDoesNotHaveEmptyMushSkillSlot(): void
    {
        $this->givenPlayerIsMush();

        $this->givenPlayerHasSkill(SkillEnum::ANONYMUSH);

        $this->givenMushSkillSlotsNumberIs(1);

        $this->expectException(GameException::class);

        $this->whenIChooseSkill(SkillEnum::SPLASHPROOF);
    }

    private function givenAPlayer(): Player
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $daedalus->getDaedalusConfig()->setHumanSkillSlots(4);
        $daedalus->getDaedalusConfig()->setMushSkillSlots(4);

        return PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::TERRENCE, $daedalus);
    }

    private function givenPlayerHasSkill(SkillEnum $skill): void
    {
        Skill::createByNameForPlayer($skill, $this->player);
    }

    private function givenPlayerIsMush(): void
    {
        StatusFactory::createChargeStatusFromStatusName(
            name: PlayerStatusEnum::MUSH,
            holder: $this->player,
        );
    }

    private function givenHumanSkillSlotsNumberIs(int $number): void
    {
        $this->player->getDaedalus()->getDaedalusConfig()->setHumanSkillSlots($number);
    }

    private function givenMushSkillSlotsNumberIs(int $number): void
    {
        $this->player->getDaedalus()->getDaedalusConfig()->setMushSkillSlots($number);
    }

    private function whenIChooseSkill(SkillEnum $skill): void
    {
        $useCase = new ChooseSkillUseCase(
            new AddSkillToPlayerService(
                $this->createStub(ModifierCreationServiceInterface::class),
                $this->playerRepository,
                $this->skillConfigRepository,
                new FakeStatusService(),
            ),
        );
        $useCase->execute(new ChooseSkillDto($skill, $this->player));
    }

    private function thenPlayerShouldHaveSkill(SkillEnum $skill): void
    {
        $player = $this->playerRepository->findOneByName($this->player->getName());
        $addedSkill = $player->getSkillByNameOrThrow($skill);
        self::assertEquals($skill, $addedSkill->getName());
    }

    private function thenPlayerShouldHaveSkillPoints(SkillPointsEnum $skillPoints): void
    {
        $player = $this->playerRepository->findOneByName($this->player->getName());
        $skillPointsStatus = $player->getStatusByName($skillPoints->toString());

        self::assertNotNull($skillPointsStatus);
    }

    private function initSkillConfigs(): void
    {
        $this->skillConfigRepository->save(SkillConfig::createFromDto(SkillConfigData::getByName(SkillEnum::PILOT)));
        $this->skillConfigRepository->save(SkillConfig::createFromDto(SkillConfigData::getByName(SkillEnum::TECHNICIAN)));
        $this->skillConfigRepository->save(SkillConfig::createFromDto(SkillConfigData::getByName(SkillEnum::SHOOTER)));
        $this->skillConfigRepository->save(SkillConfig::createFromDto(SkillConfigData::getByName(SkillEnum::MANKIND_ONLY_HOPE)));
        $this->skillConfigRepository->save(SkillConfig::createFromDto(SkillConfigData::getByName(SkillEnum::ANONYMUSH)));
        $this->skillConfigRepository->save(SkillConfig::createFromDto(SkillConfigData::getByName(SkillEnum::SPLASHPROOF)));
    }
}
