<?php

declare(strict_types=1);

namespace Mush\tests\unit\Communication\Normalizer;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Normalizer\MessageNormalizer;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\FakeStatusService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MessageNormalizerOnPheromodemTest extends TestCase
{
    private MessageNormalizer $normalizer;
    private FakeStatusService $statusService;

    private Daedalus $daedalus;
    private Channel $mushChannel;
    private Player $human;
    private Player $mush;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->normalizer = new MessageNormalizer(
            new MessageNormalizerOnPheromodemTranslationService(),
        );
        $this->statusService = new FakeStatusService();

        $this->givenDaedalus();
        $this->givenMushChannel();
        $this->givenHumanPlayer();
        $this->givenMushPlayer();
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->statusService->clearRepository();
    }

    public function testHumanShouldSeeAllMessagesAsWrittenByMush(): void
    {
        $message = $this->givenMushChannelMessageWritenBy($this->mush);

        // when I normalize the message
        $normalizedMessage = $this->normalizer->normalize($message, format: null, context: ['currentPlayer' => $this->human]);

        // then normalized message character should be "Mush"
        self::assertEquals('mush', $normalizedMessage['character']['key']);
        self::assertEquals('Mush', $normalizedMessage['character']['value']);
    }

    public function testMushShouldSeeAllMessagesAsWrittenByTheirAuthor(): void
    {
        $message = $this->givenMushChannelMessageWritenBy($this->mush);

        // when I normalize the message
        $normalizedMessage = $this->normalizer->normalize($message, format: null, context: ['currentPlayer' => $this->mush]);

        // then normalized message character should be "Derek"
        self::assertEquals('derek', $normalizedMessage['character']['key']);
        self::assertEquals('Derek', $normalizedMessage['character']['value']);
    }

    private function givenDaedalus(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
    }

    private function givenMushChannel(): void
    {
        $this->mushChannel = new Channel();
        $this->mushChannel
            ->setDaedalus($this->daedalus->getDaedalusInfo())
            ->setScope(ChannelScopeEnum::MUSH);
    }

    private function givenHumanPlayer(): void
    {
        $this->human = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $this->daedalus);
    }

    private function givenMushPlayer(): void
    {
        $this->mush = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::DEREK, $this->daedalus);
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->mush,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenMushChannelMessageWritenBy(Player $player): Message
    {
        $message = (new Message())
            ->setChannel($this->mushChannel)
            ->setAuthor($player->getPlayerInfo())
            ->setMessage('Hello, World!')
            ->setCreatedAt(new \DateTime());

        (new \ReflectionProperty($message, 'id'))->setValue($message, crc32(serialize($message)));

        return $message;
    }
}

final class MessageNormalizerOnPheromodemTranslationService implements TranslationServiceInterface
{
    public function translate(string $key, array $parameters, string $domain, ?string $language = null): string
    {
        return match ($key) {
            'derek.name' => 'Derek',
            'mush.name' => 'Mush',
            default => 'à l\'instant',
        };
    }
}
