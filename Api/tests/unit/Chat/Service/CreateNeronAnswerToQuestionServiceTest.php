<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Chat\Service;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\ChannelPlayer;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Chat\Repository\ChannelRepositoryInterface;
use Mush\Chat\Repository\InMemoryChannelRepository;
use Mush\Chat\Repository\InMemoryMessageRepository;
use Mush\Chat\Repository\MessageRepositoryInterface;
use Mush\Chat\Services\CreateNeronAnswerToQuestionService;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Tests\unit\Chat\TestDoubles\FixedNeronAnswerGateway;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CreateNeronAnswerToQuestionServiceTest extends TestCase
{
    private CreateNeronAnswerToQuestionService $createNeronAnswerToQuestion;
    private ChannelRepositoryInterface $channelRepository;
    private MessageRepositoryInterface $messageRepository;
    private FixedNeronAnswerGateway $neronAnswerGateway;

    private Daedalus $daedalus;
    private Neron $neron;
    private Player $player;
    private Channel $neronChannel;

    protected function setUp(): void
    {
        $this->channelRepository = new InMemoryChannelRepository();
        $this->messageRepository = new InMemoryMessageRepository();
        $this->neronAnswerGateway = new FixedNeronAnswerGateway(answer: 'No, mycoalarms do not detect spore extraction.');

        $this->createNeronAnswerToQuestion = new CreateNeronAnswerToQuestionService(
            $this->messageRepository,
            $this->neronAnswerGateway
        );
    }

    public function testShouldCreateNeronAnswerMessageWhenQuestionIsAsked(): void
    {
        // Given
        $this->givenADaedalusWithNeron();
        $this->givenAPlayerInDaedalus();
        $this->givenANeronChannelWithPlayer();

        // When
        $this->whenNeronAnswersQuestion('Do mycoalarms detect spore extraction?');

        // Then
        $this->thenNeronAnswerMessageShouldBeCreatedInChannel();
        $this->thenMessageShouldContainExpectedAnswer();
        $this->thenMessageShouldHaveCorrectNeron();
        $this->thenMessageShouldHaveCorrectGameTime();
    }

    private function givenADaedalusWithNeron(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->neron = $this->daedalus->getNeron();
    }

    private function givenAPlayerInDaedalus(): void
    {
        $this->player = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
    }

    private function givenANeronChannelWithPlayer(): void
    {
        $this->neronChannel = new Channel();
        $this->neronChannel
            ->setScope(ChannelScopeEnum::NERON)
            ->setDaedalus($this->player->getDaedalus()->getDaedalusInfo());

        $neronChannelPlayer = new ChannelPlayer();
        $neronChannelPlayer
            ->setChannel($this->neronChannel)
            ->setParticipant($this->player->getPlayerInfo());

        $this->channelRepository->save($this->neronChannel);
    }

    private function whenNeronAnswersQuestion(string $question): void
    {
        $this->createNeronAnswerToQuestion->execute(
            question: $question,
            channel: $this->neronChannel,
        );
    }

    private function thenNeronAnswerMessageShouldBeCreatedInChannel(): void
    {
        $messages = $this->messageRepository->findByChannel($this->neronChannel);

        self::assertCount(1, $messages, 'Expected exactly one message to be created in the channel');
    }

    private function thenMessageShouldContainExpectedAnswer(): void
    {
        $message = $this->getCreatedMessage();

        self::assertEquals(
            'No, mycoalarms do not detect spore extraction.',
            $message->getMessage(),
            'Message should contain the expected Neron answer'
        );
    }

    private function thenMessageShouldHaveCorrectNeron(): void
    {
        $message = $this->getCreatedMessage();

        self::assertTrue(
            $message->getNeron()->getId() === $this->neron->getId(),
            'Message should be attributed to the correct Neron'
        );
    }

    private function thenMessageShouldHaveCorrectGameTime(): void
    {
        $message = $this->getCreatedMessage();

        self::assertTrue(
            $message->getDay() === $this->daedalus->getDay(),
            'Message should have the correct day from Daedalus'
        );
        self::assertTrue(
            $message->getCycle() === $this->daedalus->getCycle(),
            'Message should have the correct cycle from Daedalus'
        );
    }

    private function getCreatedMessage(): Message
    {
        $messages = $this->messageRepository->findByChannel($this->neronChannel);

        return $messages[0];
    }
}
