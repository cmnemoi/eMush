<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Player\Listener\PlayerStatistics;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\Dto\CreateMessage;
use Mush\Chat\Entity\Message;
use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Chat\Services\MessageServiceInterface;
use Mush\Game\Enum\TitleEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ChatStatisticCest extends AbstractFunctionalTest
{
    private ChannelServiceInterface $channelService;
    private MessageServiceInterface $messageService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->channelService = $I->grabService(ChannelServiceInterface::class);
        $this->messageService = $I->grabService(MessageServiceInterface::class);

        $this->givenChunIsCommsOfficer();
    }

    public function testCountNeronMessageAndReplyInFavorite(FunctionalTester $I): void
    {
        $this->givenChunIsNeronAdmin();
        $this->givenVocodedAnnouncementsAreEnabled();

        $message = $this->whenChunTalksWithMessage('/neron test');
        $this->whenChunPutsMessageInFavorite($message);
        $this->whenChunTalksWithReplyToMessage($message, 'test');

        $this->thenNeronMessageShouldExist($I, $this->publicChannel, 'test');
        $this->thenChunShouldHaveMessageCount(2, $I);
    }

    public function shouldNotIncrementWhenTalkingInPrivate(FunctionalTester $I): void
    {
        $channel = $this->givenChunHasPrivateChannel();

        $this->whenChunTalksToPrivateChannelWithMessage($channel, 'test');

        $this->thenChunPrivateMessageShouldExist($I, $channel, 'test');
        $this->thenChunShouldHaveMessageCount(0, $I);
    }

    private function givenChunIsCommsOfficer(): void
    {
        $this->chun->addTitle(TitleEnum::COM_MANAGER);
    }

    private function givenChunIsNeronAdmin(): void
    {
        $this->chun->addTitle(TitleEnum::NERON_MANAGER);
    }

    private function givenVocodedAnnouncementsAreEnabled(): void
    {
        $this->chun->getDaedalus()->getNeron()->toggleVocodedAnnouncements();
    }

    private function givenChunHasPrivateChannel(): Channel
    {
        return $this->channelService->createPrivateChannel($this->chun);
    }

    private function whenChunTalksWithMessage(string $text): Message
    {
        $messageDto = new CreateMessage();
        $messageDto->setChannel($this->publicChannel);
        $messageDto->setMessage($text);
        $messageDto->setParent(null);
        $messageDto->setPlayer($this->chun);

        return $this->messageService->createPlayerMessage(
            player: $this->chun,
            createMessage: $messageDto
        );
    }

    private function whenChunPutsMessageInFavorite(Message $message): void
    {
        $this->messageService->putMessageInFavoritesForPlayer($message, $this->chun);
    }

    private function whenChunTalksWithReplyToMessage(Message $parent, string $text): void
    {
        $messageDto = new CreateMessage();
        $messageDto->setChannel($this->publicChannel);
        $messageDto->setMessage($text);
        $messageDto->setParent($parent);
        $messageDto->setPlayer($this->chun);

        $this->messageService->createPlayerMessage(
            player: $this->chun,
            createMessage: $messageDto
        );
    }

    private function whenChunTalksToPrivateChannelWithMessage(Channel $privateChannel, string $text): void
    {
        $messageDto = new CreateMessage();
        $messageDto->setChannel($privateChannel);
        $messageDto->setMessage($text);
        $messageDto->setParent(null);
        $messageDto->setPlayer($this->chun);

        $this->messageService->createPlayerMessage(
            player: $this->chun,
            createMessage: $messageDto
        );
    }

    private function thenNeronMessageShouldExist(FunctionalTester $I, Channel $channel, string $message): void
    {
        $I->seeInRepository(Message::class, [
            'channel' => $channel,
            'message' => $message,
            'neron' => $this->chun->getDaedalus()->getNeron(),
        ]);
    }

    private function thenChunPrivateMessageShouldExist(FunctionalTester $I, Channel $channel, string $message): void
    {
        $I->seeInRepository(Message::class, [
            'channel' => $channel,
            'message' => $message,
            'author' => $this->chun,
        ]);
    }

    private function thenChunShouldHaveMessageCount(int $expectedCount, FunctionalTester $I): void
    {
        $I->assertEquals($expectedCount, $this->chun->getPlayerInfo()->getStatistics()->getMessageCount());
    }
}
