<?php

declare(strict_types=1);

namespace Mush\tests\unit\Communication\Normalizer;

use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Enum\MushMessageEnum;
use Mush\Communication\Normalizer\MessageNormalizer;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\Random\FakeGetRandomIntegerService;
use Mush\Game\Service\Random\RandomString;
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
    private Channel $publicChannel;
    private Player $human;
    private Player $mush;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->normalizer = new MessageNormalizer(
            new RandomString(new FakeGetRandomIntegerService(result: 5)),
            new MessageNormalizerOnPheromodemTranslationService(),
        );
        $this->statusService = new FakeStatusService();

        $this->givenDaedalus();
        $this->givenMushChannel();
        $this->givenPublicChannel();
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

    public function testSystemMessageCharactersShouldBeScrambledForHuman(): void
    {
        $message = $this->givenMushChannelSystemMessage();

        // when I normalize the message
        $normalizedMessage = $this->normalizer->normalize($message, format: null, context: ['currentPlayer' => $this->human]);

        $messageText = $normalizedMessage['message'];

        // then I should not see "derek" in the message text
        self::assertStringNotContainsString('derek', $messageText);

        // then I should not see "paola" in the message text
        self::assertStringNotContainsString('paola', $messageText);
    }

    public function testSystemMessageCharactersShouldNotBeScrambledForMush(): void
    {
        $message = $this->givenMushChannelSystemMessage();

        // when I normalize the message
        $normalizedMessage = $this->normalizer->normalize($message, format: null, context: ['currentPlayer' => $this->mush]);

        $messageText = $normalizedMessage['message'];

        // then I should see "derek" in the message text
        self::assertStringContainsString('derek', $messageText);

        // then I should see "paola" in the message text
        self::assertStringContainsString('paola', $messageText);
    }

    public function testSystemMessageShouldNotBeScrambledOutsideMushChannel(): void
    {
        $message = $this->givenPublicChannelSystemMessage();

        // when I normalize the message
        $normalizedMessage = $this->normalizer->normalize($message, format: null, context: ['currentPlayer' => $this->human]);

        $messageText = $normalizedMessage['message'];

        // then I should see "derek" in the message text
        self::assertStringContainsString('derek', $messageText);

        // then I should see "paola" in the message text
        self::assertStringContainsString('paola', $messageText);
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

    private function givenPublicChannel(): void
    {
        $this->publicChannel = new Channel();
        $this->publicChannel
            ->setDaedalus($this->daedalus->getDaedalusInfo())
            ->setScope(ChannelScopeEnum::PUBLIC);
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

    private function givenMushChannelSystemMessage(): Message
    {
        $message = (new Message())
            ->setChannel($this->mushChannel)
            ->setMessage(MushMessageEnum::INFECT_TRAP)
            ->setCreatedAt(new \DateTime())
            ->setTranslationParameters([
                'character' => 'derek',
                'target_character' => 'paola',
                'quantity' => 1,
            ]);

        (new \ReflectionProperty($message, 'id'))->setValue($message, crc32(serialize($message)));

        return $message;
    }

    private function givenPublicChannelSystemMessage(): Message
    {
        $message = (new Message())
            ->setChannel($this->publicChannel)
            ->setMessage('infect_trap')
            ->setCreatedAt(new \DateTime())
            ->setTranslationParameters([
                'character' => 'derek',
                'target_character' => 'paola',
                'quantity' => 1,
            ]);

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
            'infect_trap' => "**{$parameters['target_character']}** a été contaminé en ouvrant une étagère piégée par **{$parameters['character']}**. Son niveau de contamination est maintenant de **{$parameters['quantity']}**.",
            default => 'à l\'instant',
        };
    }
}
