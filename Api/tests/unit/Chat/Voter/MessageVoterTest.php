<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Chat\Voter;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\ChannelPlayer;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Chat\Repository\InMemoryChannelPlayerRepository;
use Mush\Chat\Repository\InMemoryChannelRepository;
use Mush\Chat\Repository\InMemoryMessageRepository;
use Mush\Chat\Services\ChannelService;
use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Chat\Voter\MessageVoter;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\InMemoryPlayerInfoRepository;
use Mush\Player\Repository\InMemoryPlayerRepository;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Status\Service\FakeStatusService;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @internal
 */
final class MessageVoterTest extends TestCase
{
    private InMemoryChannelRepository $channelRepository;
    private InMemoryChannelPlayerRepository $channelPlayerRepository;
    private InMemoryMessageRepository $messageRepository;
    private InMemoryPlayerRepository $playerRepository;
    private ChannelServiceInterface $channelService;
    private InMemoryPlayerInfoRepository $playerInfoRepository;
    private MessageVoter $voter;
    private Daedalus $daedalus;
    private Player $player;
    private User $user;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $this->daedalus);
        GameEquipmentFactory::createItemByNameForHolder(ItemEnum::WALKIE_TALKIE, $this->player);
        $this->user = $this->player->getUser();

        $this->channelRepository = new InMemoryChannelRepository();
        $this->channelPlayerRepository = new InMemoryChannelPlayerRepository();
        $this->messageRepository = new InMemoryMessageRepository();
        $this->playerRepository = new InMemoryPlayerRepository();
        $this->playerInfoRepository = new InMemoryPlayerInfoRepository();
        $this->playerInfoRepository->save($this->player->getPlayerInfo());
        $this->playerRepository->save($this->player);

        $this->channelService = new ChannelService(
            $this->channelRepository,
            $this->channelPlayerRepository,
            $this->messageRepository,
            self::createStub(EventServiceInterface::class),
            new FakeStatusService(),
            $this->playerRepository,
        );

        $this->voter = new MessageVoter($this->channelService, $this->playerInfoRepository);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->channelRepository->clear();
        $this->channelPlayerRepository->clear();
        $this->messageRepository->clear();
        $this->playerRepository->clear();
        $this->playerInfoRepository->clear();
    }

    public function testShouldAllowPlayerToViewPublicMessage(): void
    {
        $message = $this->givenPublicMessage();

        $this->thenAccessShouldBeGranted(MessageVoter::VIEW, $message);
    }

    public function testShouldAllowPrivateChannelParticipantToViewMessage(): void
    {
        $message = $this->givenPrivateMessageVisibleByPlayer($this->player);

        $this->thenAccessShouldBeGranted(MessageVoter::VIEW, $message);
    }

    public function testShouldDenyPrivateMessageViewToNonParticipant(): void
    {
        $message = $this->givenPrivateMessage();

        $this->thenAccessShouldBeDenied(MessageVoter::VIEW, $message);
    }

    public function testShouldAllowPirateToViewVictimPrivateMessage(): void
    {
        $victim = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::KUAN_TI, $this->daedalus);
        $this->givenPlayerPiratesVictim($victim);
        $message = $this->givenPrivateMessageVisibleByPlayer($victim);

        $this->thenAccessShouldBeGranted(MessageVoter::VIEW, $message);
    }

    public function testShouldAllowPlayerToCreatePublicMessageWhenTheyCanCommunicate(): void
    {
        $message = $this->givenPublicMessage();

        $this->thenAccessShouldBeGranted(MessageVoter::CREATE, $message);
    }

    public function testShouldDenyPlayerToCreatePublicMessageWhenTheyCannotCommunicate(): void
    {
        $this->givenPlayerCannotCommunicate();
        $message = $this->givenPublicMessage();

        $this->thenAccessShouldBeDenied(MessageVoter::CREATE, $message);
    }

    public function testShouldAllowPrivateChannelParticipantToCreateMessage(): void
    {
        $message = $this->givenPrivateMessageVisibleByPlayer($this->player);

        $this->thenAccessShouldBeGranted(MessageVoter::CREATE, $message);
    }

    public function testShouldDenyPrivateMessageCreationToNonParticipant(): void
    {
        $message = $this->givenPrivateMessage();

        $this->thenAccessShouldBeDenied(MessageVoter::CREATE, $message);
    }

    public function testShouldDenyFinishedPlayerToCreateMessage(): void
    {
        $this->givenPlayerHasFinishedGame();
        $message = $this->givenPublicMessage();

        $this->thenAccessShouldBeDenied(MessageVoter::CREATE, $message);
    }

    public function testShouldAllowPlayerToFavoritePublicMessageFromTheirDaedalus(): void
    {
        $message = $this->givenPublicMessage();

        $this->thenAccessShouldBeGranted(MessageVoter::FAVORITE, $message);
    }

    public function testShouldDenyFavoriteOnVisiblePrivateMessage(): void
    {
        $message = $this->givenPrivateMessageVisibleByPlayer($this->player);

        $this->thenAccessShouldBeDenied(MessageVoter::FAVORITE, $message);
    }

    public function testShouldDenyFavoriteOnPublicMessageFromAnotherDaedalus(): void
    {
        $message = $this->givenPublicMessage(DaedalusFactory::createDaedalus());

        $this->thenAccessShouldBeDenied(MessageVoter::FAVORITE, $message);
    }

    private function givenPlayerCannotCommunicate(): void
    {
        $talkie = $this->player->getEquipmentByName(ItemEnum::WALKIE_TALKIE);
        if ($talkie === null) {
            throw new \LogicException('Player should have a talkie.');
        }

        $this->player->removeEquipment($talkie);
    }

    private function givenPlayerPiratesVictim(Player $victim): void
    {
        StatusFactory::createStatusByNameForHolderAndTarget(PlayerStatusEnum::TALKIE_SCREWED, $this->player, $victim);
    }

    private function givenPlayerHasFinishedGame(): void
    {
        $this->player->getPlayerInfo()->setGameStatus(GameStatusEnum::FINISHED);
    }

    private function givenPublicMessage(?Daedalus $daedalus = null): Message
    {
        return $this->givenMessageInChannel($this->givenPublicChannel($daedalus ?? $this->daedalus));
    }

    private function givenPrivateMessage(): Message
    {
        return $this->givenMessageInChannel($this->givenPrivateChannel($this->daedalus));
    }

    private function givenPrivateMessageVisibleByPlayer(Player $player): Message
    {
        $channel = $this->givenPrivateChannel($this->daedalus);
        $this->addParticipantToChannel($player, $channel);

        return $this->givenMessageInChannel($channel);
    }

    private function givenPublicChannel(Daedalus $daedalus): Channel
    {
        return Channel::createPublicChannel()->setDaedalus($daedalus->getDaedalusInfo());
    }

    private function givenPrivateChannel(Daedalus $daedalus): Channel
    {
        return (new Channel())
            ->setDaedalus($daedalus->getDaedalusInfo())
            ->setScope(ChannelScopeEnum::PRIVATE);
    }

    private function givenMessageInChannel(Channel $channel): Message
    {
        return (new Message())->setChannel($channel);
    }

    private function addParticipantToChannel(Player $player, Channel $channel): void
    {
        $channelPlayer = new ChannelPlayer();
        $channelPlayer
            ->setChannel($channel)
            ->setParticipant($player->getPlayerInfo());

        $channel->addParticipant($channelPlayer);
    }

    private function thenAccessShouldBeGranted(string $attribute, Message $message): void
    {
        self::assertSame(Voter::ACCESS_GRANTED, $this->vote($attribute, $message));
    }

    private function thenAccessShouldBeDenied(string $attribute, Message $message): void
    {
        self::assertSame(Voter::ACCESS_DENIED, $this->vote($attribute, $message));
    }

    private function vote(string $attribute, Message $message): int
    {
        return $this->voter->vote(
            new UsernamePasswordToken($this->user, 'credentials', []),
            $message,
            [$attribute]
        );
    }
}
