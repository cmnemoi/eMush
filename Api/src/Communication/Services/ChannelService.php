<?php

namespace Mush\Communication\Services;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Enum\CommunicationActionEnum;
use Mush\Communication\Event\ChannelEvent;
use Mush\Communication\Repository\ChannelPlayerRepository;
use Mush\Communication\Repository\ChannelRepository;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class ChannelService implements ChannelServiceInterface
{
    private EntityManagerInterface $entityManager;
    private ChannelRepository $channelRepository;
    private ChannelPlayerRepository $channelPlayerRepository;
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ChannelRepository $channelRepository,
        ChannelPlayerRepository $channelPlayerRepository,
        EventServiceInterface $eventService,
        StatusServiceInterface $statusService
    ) {
        $this->entityManager = $entityManager;
        $this->channelRepository = $channelRepository;
        $this->channelPlayerRepository = $channelPlayerRepository;
        $this->eventService = $eventService;
        $this->statusService = $statusService;
    }

    public function getPublicChannel(DaedalusInfo $daedalusInfo): ?Channel
    {
        $channel = $this->channelRepository->findOneBy([
            'daedalusInfo' => $daedalusInfo,
            'scope' => ChannelScopeEnum::PUBLIC,
        ]);

        return $channel instanceof Channel ? $channel : null;
    }

    public function createPublicChannel(DaedalusInfo $daedalusInfo): Channel
    {
        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::PUBLIC);

        $this->entityManager->persist($channel);
        $this->entityManager->flush();

        return $channel;
    }

    public function createPrivateChannel(Player $player): Channel
    {
        $channel = new Channel();
        $channel
            ->setDaedalus($player->getDaedalus()->getDaedalusInfo())
            ->setScope(ChannelScopeEnum::PRIVATE);

        $this->entityManager->persist($channel);
        $this->entityManager->flush();

        $event = new ChannelEvent($channel, [CommunicationActionEnum::CREATE_CHANNEL], new \DateTime(), $player);
        $this->eventService->callEvent($event, ChannelEvent::NEW_CHANNEL);

        return $channel;
    }

    public function createMushChannel($daedalusInfo): Channel
    {
        $channel = new Channel();
        $channel
            ->setDaedalus($daedalusInfo)
            ->setScope(ChannelScopeEnum::MUSH);

        $this->entityManager->persist($channel);
        $this->entityManager->flush();

        return $channel;
    }

    public function getMushChannel(DaedalusInfo $daedalusInfo): ?Channel
    {
        $channel = $this->channelRepository->findOneBy([
            'daedalusInfo' => $daedalusInfo,
            'scope' => ChannelScopeEnum::MUSH,
        ]);

        return $channel instanceof Channel ? $channel : null;
    }

    public function getInvitablePlayersToPrivateChannel(Channel $channel, Player $player): PlayerCollection
    {
        $playersWithChannelsSlots = $this->channelPlayerRepository->findAvailablePlayerForPrivateChannel(
            $channel,
            $player->getDaedalus(),
            $player->getPlayerInfo()->getCharacterConfig()->getMaxNumberPrivateChannel()
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
        $event = new ChannelEvent($channel, [CommunicationActionEnum::INVITED], new \DateTime(), $player);
        $this->eventService->callEvent($event, ChannelEvent::JOIN_CHANNEL);

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
        string $reason = CommunicationActionEnum::EXIT
    ): bool {
        if ($time === null) {
            $time = new \DateTime();
        }
        $event = new ChannelEvent($channel, [$reason], $time, $player);
        $this->eventService->callEvent($event, ChannelEvent::EXIT_CHANNEL);

        if ($reason === CommunicationActionEnum::EXIT) {
            $this->updatePrivateChannel($channel, CommunicationActionEnum::EXIT, $time);
        }

        return true;
    }

    public function deleteChannel(Channel $channel): bool
    {
        $this->entityManager->remove($channel);
        $this->entityManager->flush();

        return true;
    }

    public function canPlayerCommunicate(Player $player): bool
    {
        if ($player->hasOperationalEquipmentByName(ItemEnum::ITRACKIE)
            || $player->hasOperationalEquipmentByName(ItemEnum::WALKIE_TALKIE)
            || $player->hasStatus(PlayerStatusEnum::BRAINSYNC)
            || $player->getPlace()->hasOperationalEquipmentByName(EquipmentEnum::COMMUNICATION_CENTER)
            || $player->hasTitle(TitleEnum::COM_MANAGER)
        ) {
            return true;
        }

        return false;
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

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    public function getPiratePlayer(Player $player): ?Player
    {
        $screwedTalkieStatus = $this->statusService->getByTargetAndName($player, PlayerStatusEnum::TALKIE_SCREWED);

        return $screwedTalkieStatus?->getStatusTargetOwner()->getPlayer();
    }

    public function getPlayerFavoritesChannel(Player $player): Channel
    {
        $channel = $this->channelRepository->findFavoritesChannelByPlayer($player);
        if (!$channel) {
            $channel = $this->createPlayerFavoritesChannel($player);
        }

        return $channel;
    }

    public function addPlayer(PlayerInfo $playerInfo, Channel $channel): ChannelPlayer
    {
        $channelPlayer = new ChannelPlayer();

        $channelPlayer
            ->setChannel($channel)
            ->setParticipant($playerInfo);

        $this->entityManager->persist($channelPlayer);
        $this->entityManager->flush();

        return $channelPlayer;
    }

    public function removePlayer(PlayerInfo $playerInfo, Channel $channel): bool
    {
        $channelParticipant = $channel->getParticipants()
            ->filter(static fn (ChannelPlayer $channelPlayer) => ($channelPlayer->getParticipant() === $playerInfo));

        if ($channelParticipant->isEmpty()) {
            return false;
        }

        $this->entityManager->remove($channelParticipant->first());
        $this->entityManager->flush();

        return true;
    }

    public function markChannelAsReadForPlayer(Channel $channel, Player $player): void
    {
        if ($channel->isTipsChannel()) {
            return;
        }

        $unreadMessages = $channel->getMessages()->filter(
            static fn (Message $message) => $message->isUnreadBy($player)
        );

        /** @var Message $message */
        foreach ($unreadMessages as $message) {
            $message->addReader($player);
            $this->entityManager->persist($message);

            $unreadChildren = $message->getChild()->filter(
                static fn (Message $child) => $child->isUnreadBy($player)
            );

            foreach ($unreadChildren as $reader) {
                $reader->addReader($player);
                $this->entityManager->persist($reader);
            }
        }

        $this->entityManager->flush();
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

    private function createPlayerFavoritesChannel(Player $player): Channel
    {
        $channel = new Channel();
        $channel
            ->setDaedalus($player->getDaedalus()->getDaedalusInfo())
            ->setScope(ChannelScopeEnum::FAVORITES);

        $this->entityManager->persist($channel);
        $this->entityManager->flush();

        $this->addPlayer($player->getPlayerInfo(), $channel);

        return $channel;
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
}
