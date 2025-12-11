<?php

declare(strict_types=1);

namespace Mush\Chat\Services;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\OptimisticLockException;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\ChannelPlayer;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Chat\Enum\ChatActionEnum;
use Mush\Chat\Event\ChannelEvent;
use Mush\Chat\Repository\ChannelPlayerRepositoryInterface;
use Mush\Chat\Repository\ChannelRepositoryInterface;
use Mush\Chat\Repository\MessageRepositoryInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final class ChannelService implements ChannelServiceInterface
{
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private ChannelPlayerRepositoryInterface $channelPlayerRepository,
        private MessageRepositoryInterface $messageRepository,
        private EventServiceInterface $eventService,
        private StatusServiceInterface $statusService,
        private PlayerRepositoryInterface $playerRepository,
    ) {}

    public function getPublicChannel(DaedalusInfo $daedalusInfo): ?Channel
    {
        return $this->channelRepository->findOneByDaedalusInfoAndScope($daedalusInfo, ChannelScopeEnum::PUBLIC);
    }

    public function createPublicChannel(DaedalusInfo $daedalusInfo): Channel
    {
        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);

        $this->channelRepository->save($channel);

        return $channel;
    }

    public function createPrivateChannel(Player $player): Channel
    {
        return $this->channelRepository->wrapInTransaction(function () use ($player) {
            $currentChannelCount = $this->getPlayerChannels($player, true)->count();
            if ($currentChannelCount >= $player->getMaxPrivateChannels()) {
                throw new \RuntimeException('Player has reached maximum number of private channels');
            }

            $channel = new Channel();
            $channel
                ->setDaedalus($player->getDaedalus()->getDaedalusInfo())
                ->setScope(ChannelScopeEnum::PRIVATE);
            $this->channelRepository->save($channel);

            $event = new ChannelEvent($channel, [ChatActionEnum::CREATE_CHANNEL], new \DateTime(), $player);
            $this->eventService->callEvent($event, ChannelEvent::NEW_CHANNEL);

            return $channel;
        });
    }

    public function createMushChannel($daedalusInfo): Channel
    {
        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH);

        $this->channelRepository->save($channel);

        return $channel;
    }

    public function getMushChannel(DaedalusInfo $daedalusInfo): ?Channel
    {
        return $this->channelRepository->findOneByDaedalusInfoAndScope($daedalusInfo, ChannelScopeEnum::MUSH);
    }

    public function getMushChannelOrThrow(Daedalus $daedalus): Channel
    {
        return $this->getMushChannel($daedalus->getDaedalusInfo()) ?? throw new \LogicException('Mush channel not found');
    }

    public function getInvitablePlayersToPrivateChannel(Channel $channel, Player $player): PlayerCollection
    {
        $playersWithChannelsSlots = $this->channelPlayerRepository->findAvailablePlayerForPrivateChannel(
            $channel,
            $player->getDaedalus(),
        );

        $availablePlayers = new PlayerCollection();

        /** @var PlayerInfo $invitablePlayerInfo */
        foreach ($playersWithChannelsSlots as $invitablePlayerInfo) {
            /** @var Player $invitablePlayer */
            $invitablePlayer = $invitablePlayerInfo->getPlayer();
            if ($this->canPlayerCommunicate($player)) {
                if ($this->canPlayerSeePrivateChannel($invitablePlayer, $channel)) {
                    $availablePlayers->add($invitablePlayer);
                }
            } elseif ($this->canPlayerWhisper($player, $invitablePlayer)) {
                $availablePlayers->add($invitablePlayer);
            }
        }

        return $availablePlayers;
    }

    public function invitePlayer(Player $player, Channel $channel): Channel
    {
        $event = new ChannelEvent($channel, [ChatActionEnum::INVITED], new \DateTime(), $player);
        $this->eventService->callEvent($event, ChannelEvent::REQUEST_CHANNEL);

        return $channel;
    }

    public function addPlayerToMushChannel(Player $player): void
    {
        $mushChannel = $this->channelRepository->findMushChannelByDaedalus($player->getDaedalus());
        $this->addPlayer($player->getPlayerInfo(), $mushChannel);
    }

    public function removePlayerFromMushChannel(Player $player): void
    {
        $mushChannel = $this->channelRepository->findMushChannelByDaedalus($player->getDaedalus());
        $this->removePlayer($player->getPlayerInfo(), $mushChannel);
    }

    public function exitChannel(
        Player $player,
        Channel $channel,
        ?\DateTime $time = null,
        string $reason = ChatActionEnum::EXIT
    ): bool {
        if ($time === null) {
            $time = new \DateTime();
        }
        $event = new ChannelEvent($channel, [$reason], $time, $player);
        $this->eventService->callEvent($event, ChannelEvent::EXIT_CHANNEL);

        if ($reason === ChatActionEnum::EXIT) {
            $this->updatePrivateChannel($channel, ChatActionEnum::EXIT, $time);
        }

        return true;
    }

    public function deleteChannel(Channel $channel): bool
    {
        $this->channelRepository->delete($channel);

        return true;
    }

    public function canPlayerCommunicate(Player $player): bool
    {
        return $player->hasMeansOfCommunication();
    }

    public function canPlayerWhisper(Player $player, Player $otherPlayer): bool
    {
        return $player->getPlace() === $otherPlayer->getPlace()
            && !$player->hasStatus(PlayerStatusEnum::LOST)
            && !$otherPlayer->hasStatus(PlayerStatusEnum::LOST);
    }

    public function canPlayerWhisperInChannel(Channel $channel, Player $player): bool
    {
        // all Mush players can post in mush channel, whatever the conditions
        if ($channel->isMushChannel() && $player->isMush()) {
            return true;
        }

        // either all participant are in the same room
        if (!$this->isChannelOnSeveralRoom($channel, $player->getPlace())) {
            return true;
        }

        // or at least one member of the conversation in each room can communicate
        $otherParticipants = $channel->getParticipants()
            ->map(static fn (ChannelPlayer $channelPlayer) => $channelPlayer->getParticipant()->getPlayer())
            ->filter(static fn (?Player $participant) => $participant !== null && $participant !== $player);

        /** @var Player $participant */
        foreach ($otherParticipants as $participant) {
            if ($this->canPlayerCommunicate($participant) || $this->canPlayerWhisper($player, $participant)) {
                return true;
            }
        }

        return false;
    }

    public function updatePlayerPrivateChannels(Player $player, string $reason, \DateTime $time): void
    {
        $channels = $this->getPlayerChannels($player, true);

        foreach ($channels as $channel) {
            $this->updatePrivateChannel($channel, $reason, $time);
        }
    }

    /** @return Collection<int, Channel> */
    public function getPlayerChannels(Player $player, bool $privateOnly = false): Collection
    {
        $channels = $this->channelRepository->findByPlayer($player->getPlayerInfo(), $privateOnly);

        if ($player->isAlive() && !$this->canPlayerCommunicate($player) && !$privateOnly) {
            return $channels->filter(static fn (Channel $channel) => $channel->isPrivateOrMush());
        }

        return $channels;
    }

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    public function getPiratedPlayer(Player $player): ?Player
    {
        if (!$player->hasStatus(PlayerStatusEnum::TALKIE_SCREWED)) {
            return null;
        }

        /** @var Status $talkieScrewedStatus */
        $talkieScrewedStatus = $player->getStatusByName(PlayerStatusEnum::TALKIE_SCREWED);

        return $talkieScrewedStatus->getStatusTargetTarget()?->getPlayer();
    }

    public function getPiratedChannels(Player $piratedPlayer): Collection
    {
        $channels = $this->channelRepository->findByPlayer($piratedPlayer->getPlayerInfo());

        return $channels->filter(
            fn (Channel $channel) => !$this->isChannelWhisperOnly($channel)
            && !$channel->isScope(ChannelScopeEnum::MUSH)
        );
    }

    public function addPlayer(PlayerInfo $playerInfo, Channel $channel): void
    {
        try {
            $this->channelRepository->wrapInTransaction(function () use ($playerInfo, $channel) {
                // Check if player is already in the channel
                $existingChannelPlayer = $this->channelPlayerRepository->findByChannelAndPlayer($channel, $playerInfo);
                if ($existingChannelPlayer) {
                    return;
                }

                $player = $playerInfo->getPlayer();
                if (!$player) {
                    throw new \Exception('Player is not defined');
                }

                // check player has enough private channels
                if ($channel->isPrivate()) {
                    $currentChannelCount = $this->getPlayerChannels($player, true)->count();
                    if ($currentChannelCount >= $player->getMaxPrivateChannels()) {
                        throw new \Exception('Player has too many private channels');
                    }
                }

                $channelPlayer = new ChannelPlayer();
                $channelPlayer->setChannel($channel)->setParticipant($playerInfo);
                $channel->addParticipant($channelPlayer);

                $this->channelPlayerRepository->save($channelPlayer);
                $this->channelRepository->save($channel);

                $this->eventService->callEvent(
                    new ChannelEvent($channel, [ChatActionEnum::INVITED], new \DateTime(), $playerInfo->getPlayer()),
                    ChannelEvent::JOIN_CHANNEL
                );
            });
        } catch (OptimisticLockException|UniqueConstraintViolationException $e) {
        }
    }

    public function removePlayer(PlayerInfo $playerInfo, Channel $channel): bool
    {
        /** @var ChannelPlayer|false $channelParticipant */
        $channelParticipant = $channel->getParticipants()
            ->filter(static fn (ChannelPlayer $channelPlayer) => ($channelPlayer->getParticipant() === $playerInfo))
            ->first();

        if (!$channelParticipant) {
            return false;
        }

        $this->channelPlayerRepository->delete($channelParticipant);

        return true;
    }

    public function markChannelAsReadForPlayer(Channel $channel, Player $player): void
    {
        if ($channel->isTipsChannel()) {
            return;
        }

        $this->readMessages($channel->getPlayerUnreadMessages($player), $player);
    }

    public function markTipsChannelAsReadForPlayer(Channel $tipsChannel, Player $player): void
    {
        if ($tipsChannel->isNotTipsChannel()) {
            throw new \InvalidArgumentException('Channel must be a tips channel');
        }

        $player->markTipsAsRead($tipsChannel->getIdOrThrow());
        $this->playerRepository->save($player);
    }

    /**
     * @param Collection<int, Message> $messages
     */
    private function readMessages(Collection $messages, Player $player): void
    {
        $messages->map(fn (Message $message) => $this->readMessage($message, $player));
        $this->messageRepository->saveAll($messages->toArray());
    }

    private function isChannelOnSeveralRoom(Channel $channel, Place $place): bool
    {
        /** @var ChannelPlayer $participant */
        foreach ($channel->getParticipants() as $participant) {
            /** @var Player $player */
            $player = $participant->getParticipant()->getPlayer();
            if ($player->getPlace() !== $place) {
                return true;
            }
        }

        return false;
    }

    private function updatePrivateChannel(Channel $channel, string $reason, \DateTime $time): void
    {
        /** @var ChannelPlayer $channelParticipant */
        foreach ($channel->getParticipants() as $channelParticipant) {
            /** @var Player $participant */
            $participant = $channelParticipant->getParticipant()->getPlayer();

            $piratePlayer = $this->getPiratePlayer($participant);
            if ($piratePlayer === null) {
                $pirateAccess = false;
            } else {
                $pirateAccess = $this->canPlayerCommunicate($piratePlayer);
            }

            if (
                !$this->canPlayerCommunicate($participant)
                && !$this->canPlayerSeePrivateChannel($participant, $channel)
                && !$pirateAccess
            ) {
                $this->exitChannel($participant, $channel, $time, $reason);
            }
        }
    }

    private function canPlayerSeePrivateChannel(Player $player, Channel $channel): bool
    {
        $playerIsAloneInTheirChannel = $channel->getParticipants()->count() === 1
            && $channel->getParticipants()->first()->getParticipant()->getPlayer() === $player;

        if ($this->canPlayerCommunicate($player)) {
            return true;
        }
        if ($playerIsAloneInTheirChannel) {
            return true;
        }
        // can whisper with at least one channel participant
        $otherParticipants = $channel->getParticipants()
            ->map(static fn (ChannelPlayer $channelPlayer) => $channelPlayer->getParticipant()->getPlayer())
            ->filter(static fn (?Player $participant) => $participant !== null && $participant !== $player);

        /** @var Player $participant */
        foreach ($otherParticipants as $participant) {
            if ($this->canPlayerWhisper($player, $participant)) {
                return true;
            }
        }

        return false;
    }

    private function isChannelWhisperOnly(Channel $channel): bool
    {
        if (!$channel->isPrivate()) {
            return false;
        }

        /** @var Player $firstParticipant */
        $firstParticipant = $channel->getParticipants()->first()->getParticipant()->getPlayer();

        /** @var ChannelPlayer $channelParticipant */
        foreach ($channel->getParticipants() as $channelParticipant) {
            /** @var Player $participant */
            $participant = $channelParticipant->getParticipant()->getPlayer();

            if (!$this->canPlayerWhisper($participant, $firstParticipant)) {
                return false;
            }
        }

        return true;
    }

    private function readMessage(Message $message, Player $player): Message
    {
        if ($message->isReadBy($player)) {
            return $message;
        }

        $message = $message->addReader($player);

        return $message->cancelTimestampable();
    }

    private function getPiratePlayer(Player $player): ?Player
    {
        $screwedTalkieStatus = $this->statusService->getByTargetAndName($player, PlayerStatusEnum::TALKIE_SCREWED);

        return $screwedTalkieStatus?->getStatusTargetOwner()?->getPlayerOrThrow();
    }
}
