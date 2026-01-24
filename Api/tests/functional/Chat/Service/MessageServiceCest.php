<?php

declare(strict_types=1);

namespace Mush\tests\functional\Chat\Service;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\Dto\CreateMessage;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Services\MessageServiceInterface;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Enum\TitleEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class MessageServiceCest extends AbstractFunctionalTest
{
    private MessageServiceInterface $messageService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->messageService = $I->grabService(MessageServiceInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
    }

    public function shouldAllowMutePlayerToCreateMessageInMushChannel(FunctionalTester $I): void
    {
        $this->givenPlayerHasTornTongue();

        $this->whenPlayerTriesToTalkInMushChannel();

        $this->thenMessageShouldBeCreatedInMushChannel($I);
    }

    public function shouldAllowNeronAdminToCreateVocodedAnnouncement(FunctionalTester $I): void
    {
        $this->givenPlayerIsNeronAdmin();
        $this->givenVocodedAnnouncementsAreEnabled();

        $this->whenPlayerCreatesVocodedAnnouncement($this->publicChannel, 'test');

        $this->thenMessageShouldBeCreatedWithNeron($I, $this->publicChannel, 'test');
    }

    public function shouldRejectVocodedAnnouncementFromNonAdmin(FunctionalTester $I): void
    {
        $this->givenVocodedAnnouncementsAreEnabled();

        $this->whenPlayerCreatesVocodedAnnouncement($this->publicChannel, 'test');

        $this->thenMessageShouldBeRejected($I);
    }

    public function shouldRejectVocodedAnnouncementWhenDisabled(FunctionalTester $I): void
    {
        $this->givenPlayerIsNeronAdmin();
        $this->givenVocodedAnnouncementsAreDisabled();

        $this->whenPlayerCreatesVocodedAnnouncement($this->publicChannel, 'test');

        $this->thenMessageShouldBeRejected($I);
    }

    public function shouldPrevenDeafPlayerToReadMessages(FunctionalTester $I): void
    {
        // given player is deaf
        $this->playerDiseaseService->createDiseaseFromName(
            InjuryEnum::DESTROYED_EARS->toString(),
            $this->player,
        );

        // given messages in public channel
        $message = $this->messageService->createPlayerMessage(
            player: $this->player2,
            createMessage: new CreateMessage()->setChannel($this->publicChannel)->setMessage('Hello, World!'),
        );

        // given a child message
        $this->messageService->createPlayerMessage(
            player: $this->player2,
            createMessage: new CreateMessage()->setChannel($this->publicChannel)->setMessage('Hello, World!')->setParent($message),
        );

        // when player tries to read message
        $messages = $this->messageService->getChannelMessages($this->player, $this->publicChannel, new \DateInterval('P7D'));

        // then message should not be readable
        $I->assertEquals(
            expected: [
                '...',
            ],
            actual: $messages->map(static fn (Message $message) => $message->getMessage())->toArray(),
        );

        // then child message should not be readable too
        $I->assertEquals(
            expected: [[
                '...',
            ]],
            actual: $messages->map(static fn (Message $message) => $message->getChild()->map(static fn (Message $child) => $child->getMessage())->toArray())->toArray(),
        );
    }

    public function shouldThrowIfPlayerNotInPrivateChannel(FunctionalTester $I): void
    {
        $privateChannel = new Channel();
        $privateChannel
            ->setScope(ChannelScopeEnum::PRIVATE)
            ->setDaedalus($this->player->getDaedalus()->getDaedalusInfo());
        $I->haveInRepository($privateChannel);

        $I->expectThrowable(new \InvalidArgumentException('Cannot post in private channel if not inside'), function () use ($privateChannel) {
            $this->messageService->createPlayerMessage(
                player: $this->player,
                createMessage: new CreateMessage()->setChannel($privateChannel)->setMessage('Hello, World!'),
            );
        });
    }

    private function givenPlayerHasTornTongue(): void
    {
        $this->playerDiseaseService->createDiseaseFromName(
            InjuryEnum::TORN_TONGUE->toString(),
            $this->player,
            [],
        );
    }

    private function givenPlayerIsNeronAdmin(): void
    {
        $this->player->addTitle(TitleEnum::NERON_MANAGER);
    }

    private function givenVocodedAnnouncementsAreEnabled(): void
    {
        $this->player->getDaedalus()->getNeron()->toggleVocodedAnnouncements();
    }

    private function givenVocodedAnnouncementsAreDisabled(): void
    {
        // By default, vocoded announcements are disabled
    }

    private function whenPlayerTriesToTalkInMushChannel(): void
    {
        $messageDto = new CreateMessage();
        $messageDto->setChannel($this->mushChannel);
        $messageDto->setMessage('test');
        $messageDto->setParent(null);

        $this->messageService->createPlayerMessage(
            player: $this->player,
            createMessage: $messageDto
        );
    }

    private function whenPlayerCreatesVocodedAnnouncement(Channel $channel, string $message): void
    {
        $messageDto = new CreateMessage();
        $messageDto->setChannel($channel);
        $messageDto->setMessage('/neron ' . $message);

        $this->messageService->createPlayerMessage(
            player: $this->player,
            createMessage: $messageDto
        );
    }

    private function thenMessageShouldBeCreatedInMushChannel(FunctionalTester $I): void
    {
        $I->seeInRepository(Message::class, [
            'channel' => $this->mushChannel,
            'message' => 'test',
        ]);
    }

    private function thenMessageShouldBeCreatedWithNeron(FunctionalTester $I, Channel $channel, string $message): void
    {
        $I->seeInRepository(Message::class, [
            'channel' => $channel,
            'message' => $message,
            'neron' => $this->player->getDaedalus()->getNeron(),
        ]);
    }

    private function thenMessageShouldBeRejected(FunctionalTester $I): void
    {
        $I->seeInRepository(Message::class, [
            'channel' => $this->publicChannel,
            'message' => NeronMessageEnum::COMMAND_REFUSED,
            'neron' => null,
        ]);
    }
}
