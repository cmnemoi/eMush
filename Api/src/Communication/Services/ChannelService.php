<?php

namespace Mush\Communication\Services;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Enum\CommunicationActionEnum;
use Mush\Communication\Event\ChannelEvent;
use Mush\Communication\Repository\ChannelPlayerRepository;
use Mush\Communication\Repository\ChannelRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class ChannelService implements ChannelServiceInterface
{
    private EntityManagerInterface $entityManager;
    private ChannelRepository $channelRepository;
    private ChannelPlayerRepository $channelPlayerRepository;
    private EventDispatcherInterface $eventDispatcher;
    private StatusServiceInterface $statusService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ChannelRepository $channelRepository,
        ChannelPlayerRepository $channelPlayerRepository,
        EventDispatcherInterface $eventDispatcher,
        StatusServiceInterface $statusService
    ) {
        $this->entityManager = $entityManager;
        $this->channelRepository = $channelRepository;
        $this->channelPlayerRepository = $channelPlayerRepository;
         $this->eventService = $eventDispatcher;;
        $this->statusService = $statusService;
    }

    public function getPublicChannel(Daedalus $daedalus): ?Channel
    {
        $channel = $this->channelRepository->findOneBy([
            'daedalus' => $daedalus,
            'scope' => ChannelScopeEnum::PUBLIC,
        ]);

        return $channel instanceof Channel ? $channel : null;
    }

    public function createPublicChannel(Daedalus $daedalus): Channel
    {
        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;

        $this->entityManager->persist($channel);
        $this->entityManager->flush();

        return $channel;
    }

    public function createPrivateChannel(Player $player): Channel
    {
        $channel = new Channel();
        $channel
            ->setDaedalus($player->getDaedalus())
            ->setScope(ChannelScopeEnum::PRIVATE)
        ;

        $this->entityManager->persist($channel);
        $this->entityManager->flush();

        $event = new ChannelEvent($channel, CommunicationActionEnum::CREATE_CHANNEL, new \DateTime(), $player);
        $this->eventService->dispatch($event, ChannelEvent::NEW_CHANNEL);

        return $channel;
    }

    public function getInvitablePlayersToPrivateChannel(Channel $channel, Player $player): PlayerCollection
    {
        $maxPrivateChannel = $channel->getDaedalus()->getGameConfig()->getMaxNumberPrivateChannel();

        $playersWithChannelsSlots = $this->channelPlayerRepository->findAvailablePlayerForPrivateChannel($channel, $maxPrivateChannel);

        $availablePlayers = new PlayerCollection();

        foreach ($playersWithChannelsSlots as $invitablePlayer) {
            if ($this->canPlayerCommunicate($player)) {
                if ($this->canPlayerCommunicate($invitablePlayer) ||
                $this->canPlayerWhisperInChannel($channel, $invitablePlayer)) {
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
        $event = new ChannelEvent($channel, CommunicationActionEnum::INVITED, new \DateTime(), $player);
        $this->eventService->dispatch($event, ChannelEvent::JOIN_CHANNEL);

        return $channel;
    }

    public function exitChannel(
        Player $player,
        Channel $channel,
        \DateTime $time = null,
        string $reason = CommunicationActionEnum::EXIT
    ): bool {
        if ($time === null) {
            $time = new \DateTime();
        }
        $event = new ChannelEvent($channel, $reason, $time, $player);
        $this->eventService->dispatch($event, ChannelEvent::EXIT_CHANNEL);

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
        if ($player->hasOperationalEquipmentByName(ItemEnum::ITRACKIE) ||
            $player->hasOperationalEquipmentByName(ItemEnum::WALKIE_TALKIE) ||
            $player->hasStatus(PlayerStatusEnum::BRAINSYNC) ||
            $player->getPlace()->hasOperationalEquipmentByName(EquipmentEnum::COMMUNICATION_CENTER)
        ) {
            return true;
        }

        return false;
    }

    public function canPlayerWhisper(Player $player, Player $otherPlayer): bool
    {
        return $player->getPlace() === $otherPlayer->getPlace();
    }

    public function canPlayerWhisperInChannel(Channel $channel, Player $player): bool
    {
        // either all participant are in the same room
        if ($channel->getParticipants()->filter(fn (ChannelPlayer $channelPlayer) => $channelPlayer->getParticipant()->getPlace() === $player->getPlace()
        )->count() === $channel->getParticipants()->count()) {
            return true;
        }

        // or at least one member of the conversation in each room can communicate
        /** @var ChannelPlayer $channelParticipant */
        foreach ($channel->getParticipants() as $channelParticipant) {
            $participant = $channelParticipant->getParticipant();

            if (!($participant instanceof Player)) {
                return false;
            } elseif ($participant !== $player &&
                $this->canPlayerCommunicate($participant) &&
                $this->canPlayerWhisper($player, $participant)
            ) {
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

    private function updatePrivateChannel(Channel $channel, string $reason, \DateTime $time): void
    {
        /** @var ChannelPlayer $channelParticipant */
        foreach ($channel->getParticipants() as $channelParticipant) {
            $participant = $channelParticipant->getParticipant();

            $piratePlayer = $this->getPiratePlayer($participant);
            if ($piratePlayer === null) {
                $pirateAccess = false;
            } else {
                $pirateAccess = $this->canPlayerCommunicate($piratePlayer);
            }

            if (
                !$this->canPlayerCommunicate($participant) &&
                !$this->canPlayerWhisperInChannel($channel, $participant) &&
                !$pirateAccess
            ) {
                $this->exitChannel($participant, $channel, $time, $reason);
            }
        }
    }

    public function getPlayerChannels(Player $player, bool $privateOnly = false): Collection
    {
        $channels = $this->channelRepository->findByPlayer($player, $privateOnly);

        if (!$this->canPlayerCommunicate($player) && !$privateOnly) {
            $publicChannel = $channels->filter(fn (Channel $channel) => $channel->isPublic())->first();
            $channels->removeElement($publicChannel);
        }

        return $channels;
    }

    public function getPiratedPlayer(Player $player): ?Player
    {
        if (!$player->hasStatus(PlayerStatusEnum::TALKIE_SCREWED)) {
            return null;
        }

        /** @var Status $talkieScrewedStatus */
        $talkieScrewedStatus = $player->getStatusByName(PlayerStatusEnum::TALKIE_SCREWED);
        /** @var Player $piratedPlayer */
        $piratedPlayer = $talkieScrewedStatus->getTarget();

        return $piratedPlayer;
    }

    public function getPiratedChannels(Player $piratedPlayer): Collection
    {
        $channels = $this->channelRepository->findByPlayer($piratedPlayer);

        return $channels->filter(fn (Channel $channel) => !$this->isChannelWhisperOnly($channel));
    }

    public function getPiratePlayer(Player $player): ?Player
    {
        $screwedTalkieStatus = $this->statusService->getByTargetAndName($player, PlayerStatusEnum::TALKIE_SCREWED);

        if ($screwedTalkieStatus) {
            /** @var Player $player */
            $player = $screwedTalkieStatus->getOwner();

            return $player;
        }

        return null;
    }

    private function isChannelWhisperOnly(Channel $channel): bool
    {
        if ($channel->isPublic()) {
            return false;
        }

        $firstParticipant = $channel->getParticipants()->first()->getParticipant();

        /** @var ChannelPlayer $channelParticipant */
        foreach ($channel->getParticipants() as $channelParticipant) {
            $participant = $channelParticipant->getParticipant();

            if (!$this->canPlayerWhisper($participant, $firstParticipant)) {
                return false;
            }
        }

        return true;
    }
}
