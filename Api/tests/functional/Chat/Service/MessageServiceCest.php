<?php

declare(strict_types=1);

namespace Mush\tests\functional\Chat\Service;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\Dto\CreateMessage;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Services\CreateNeronChannelForPlayerService;
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
    private CreateNeronChannelForPlayerService $createNeronChannelForPlayer;
    private MessageServiceInterface $messageService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->messageService = $I->grabService(MessageServiceInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
        $this->createNeronChannelForPlayer = $I->grabService(CreateNeronChannelForPlayerService::class);
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

    public function shouldGetNeronAnswerIfInNeronChannel(FunctionalTester $I): void
    {
        $this->createNeronChannelForPlayer->execute($this->player);
        $neronChannel = $I->grabEntityFromRepository(Channel::class, [
            'scope' => ChannelScopeEnum::NERON,
            'daedalusInfo' => $this->player->getDaedalus()->getDaedalusInfo(),
        ]);

        $this->whenPlayerTalksInNeronChannel($neronChannel);

        $this->thenIShouldSeeNeronAnswerInNeronChannel($neronChannel, $I);
    }

    private function givenPlayerHasTornTongue(): void
    {
        $this->playerDiseaseService->createDiseaseFromName(
            InjuryEnum::TORN_TONGUE,
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
        $messageDto->setPlayer($this->player);

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
        $messageDto->setPlayer($this->player);

        $this->messageService->createPlayerMessage(
            player: $this->player,
            createMessage: $messageDto
        );
    }

    private function whenPlayerTalksInNeronChannel(Channel $neronChannel): void
    {
        $messageDto = new CreateMessage();
        $messageDto->setChannel($neronChannel);
        $messageDto->setMessage('test');
        $messageDto->setPlayer($this->player);

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

    private function thenIShouldSeeNeronAnswerInNeronChannel(Channel $neronChannel, FunctionalTester $I): void
    {
        $I->seeInRepository(Message::class, [
            'channel' => $neronChannel,
            'neron' => $this->player->getDaedalus()->getNeron(),
        ]);
    }
}
