<?php

declare(strict_types=1);

namespace Mush\tests\unit\Chat\Normalizer;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Normalizer\TipsChannelNormalizer;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\CommanderMission;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class TipsChannelNormalizerTest extends TestCase
{
    private TipsChannelNormalizer $tipsChannelNormalizer;
    private Player $player;
    private Channel $channel;
    private array $normalizedChannel;
    private CommanderMission $mission;

    protected function setUp(): void
    {
        $this->tipsChannelNormalizer = new TipsChannelNormalizer(self::createStub(TranslationServiceInterface::class));
        $this->tipsChannelNormalizer->setNormalizer(self::createStub(NormalizerInterface::class));
        $this->player = PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());
    }

    public function shouldFlashIfPlayerIsBeginner(): void
    {
        $this->givenTipsChannel();
        $this->givenPlayerIsBeginner();

        $this->whenNormalizingChannel();

        $this->thenChannelShouldFlash();
    }

    public function shouldNotFlashIfPlayerHasReadTips(): void
    {
        $this->givenTipsChannelForPlayer();
        $this->givenPlayerIsBeginner();
        $this->givenPlayerHasReadTips();

        $this->whenNormalizingChannel();

        $this->thenChannelShouldNotFlash();
    }

    public function shouldFlashIfPlayerHasUncompletedMissions(): void
    {
        $this->givenTipsChannelForPlayer();
        $this->givenPlayerHasUncompletedMission();

        $this->whenNormalizingChannel();

        $this->thenChannelShouldFlash();
    }

    public function testShouldReturnOneMessageCountForBeginner(): void
    {
        $this->givenTipsChannel();
        $this->givenPlayerIsBeginner();

        $this->whenNormalizingChannel();

        $this->thenChannelShouldHaveMessageCount(1);
    }

    public function testShouldReturnOneMessageCountForAnUnreadMission(): void
    {
        $this->givenTipsChannelForPlayer();
        $this->givenPlayerHasUncompletedMission();

        $this->whenNormalizingChannel();

        $this->thenChannelShouldHaveMessageCount(1);
    }

    public function testShouldReturnZeroMessageCountForAReadMission(): void
    {
        $this->givenTipsChannelForPlayer();
        $this->givenPlayerHasReadMission();

        $this->whenNormalizingChannel();

        $this->thenChannelShouldHaveMessageCount(0);
    }

    public function testShouldReturnTwoMessageCountForAnUnreadMissionAndBeginner(): void
    {
        $this->givenTipsChannel();
        $this->givenPlayerIsBeginner();
        $this->givenPlayerHasUncompletedMission();

        $this->whenNormalizingChannel();

        $this->thenChannelShouldHaveMessageCount(2);
    }

    private function givenTipsChannel(): void
    {
        $this->channel = Channel::createTipsChannel();
    }

    private function givenTipsChannelForPlayer(): void
    {
        $this->channel = Channel::createTipsChannelForPlayer($this->player);
    }

    private function givenPlayerIsBeginner(): void
    {
        StatusFactory::createStatusByNameForHolder(PlayerStatusEnum::BEGINNER, $this->player);
    }

    private function givenPlayerHasReadTips(): void
    {
        $this->player->markTipsAsRead($this->channel->getIdOrThrow());
    }

    private function givenPlayerHasUncompletedMission(): void
    {
        $this->mission = new CommanderMission(commander: Player::createNull(), subordinate: $this->player, mission: 'test');
    }

    private function givenPlayerHasReadMission(): void
    {
        $this->mission = new CommanderMission(commander: Player::createNull(), subordinate: $this->player, mission: 'test');
        $this->mission->markAsRead();
    }

    private function whenNormalizingChannel(): void
    {
        $this->normalizedChannel = $this->tipsChannelNormalizer->normalize($this->channel, null, ['currentPlayer' => $this->player]);
    }

    private function thenChannelShouldFlash(): void
    {
        self::assertTrue($this->normalizedChannel['flashing'], 'Tips channel should be flashing');
    }

    private function thenChannelShouldNotFlash(): void
    {
        self::assertFalse($this->normalizedChannel['flashing'], 'Tips channel should not be flashing');
    }

    private function thenChannelShouldHaveMessageCount(int $expectedCount): void
    {
        self::assertEquals($expectedCount, $this->normalizedChannel['numberOfNewMessages'], \sprintf('Tips channel should have %d message(s)', $expectedCount));
    }
}
