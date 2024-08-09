<?php

declare(strict_types=1);

namespace Mush\tests\functional\Daedalus\Normalizer;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Daedalus\Normalizer\DaedalusNormalizer;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Factory\ProjectFactory;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AnonymushTest extends TestCase
{
    private DaedalusNormalizer $daedalusNormalizer;
    private Player $player;

    private array $normalizedDaedalus = [];

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->daedalusNormalizer = new DaedalusNormalizer(
            $this->createStub(CycleServiceInterface::class),
            $this->createStub(TranslationServiceInterface::class),
            $this->createStub(PlanetServiceInterface::class),
        );

        $this->givenAMushPlayer();
    }

    public function testShouldAppearAsHuman(): void
    {
        $this->givenPlayerIsAnonymous();

        $this->whenINormalizeDaedalus();

        $this->IShouldSeeOneAliveHuman();
    }

    public function testShouldNotAppearAsMush(): void
    {
        $this->givenPlayerIsAnonymous();

        $this->whenINormalizeDaedalus();

        $this->IShouldSeeNoAliveMush();
    }

    public function testShouldAppearAsDeadMush(): void
    {
        $this->givenPlayerIsAnonymous();

        $this->givenPlayerIsDead();

        $this->whenINormalizeDaedalus();

        $this->IShouldSeeOneDeadMush();
    }

    private function givenAMushPlayer(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        ProjectFactory::createNeronProjectByNameForDaedalus(ProjectName::PLASMA_SHIELD, $daedalus);
        ProjectFactory::createPilgredProjectForDaedalus($daedalus);
        $this->player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::TERRENCE, $daedalus);
        StatusFactory::createChargeStatusFromStatusName(
            name: PlayerStatusEnum::MUSH,
            holder: $this->player,
        );
    }

    private function givenPlayerIsAnonymous(): void
    {
        Skill::createByNameForPlayer(SkillEnum::ANONYMUSH, $this->player);
    }

    private function givenPlayerIsDead(): void
    {
        $this->player->kill();

        $closedPlayer = $this->player->getPlayerInfo()->getClosedPlayer();
        $closedPlayer->setIsMush(true);
    }

    private function whenINormalizeDaedalus(): void
    {
        $this->normalizedDaedalus = $this->daedalusNormalizer->normalize($this->player->getDaedalus(), format: null, context: ['currentPlayer' => $this->player]);
    }

    private function IShouldSeeOneAliveHuman(): void
    {
        self::assertEquals(1, $this->normalizedDaedalus['humanPlayerAlive']);
    }

    private function IShouldSeeNoAliveMush(): void
    {
        self::assertEquals(0, $this->normalizedDaedalus['mushPlayerAlive']);
    }

    private function IShouldSeeOneDeadMush(): void
    {
        self::assertEquals(1, $this->normalizedDaedalus['mushPlayerDead']);
    }
}
