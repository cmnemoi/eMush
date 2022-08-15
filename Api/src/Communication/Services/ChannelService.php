<?php

namespace Mush\Communication\Services;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Enum\CommunicationActionEnum;
use Mush\Communication\Event\ChannelEvent;
use Mush\Communication\Repository\ChannelPlayerRepository;
use Mush\Communication\Repository\ChannelRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChannelService implements ChannelServiceInterface
{
    private EntityManagerInterface $entityManager;
    private ChannelRepository $channelRepository;
    private ChannelPlayerRepository $channelPlayerRepository;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        ChannelRepository $channelRepository,
        ChannelPlayerRepository $channelPlayerRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->channelRepository = $channelRepository;
        $this->channelPlayerRepository = $channelPlayerRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getPlayerChannels(Player $player, bool $privateOnly = false): Collection
    {
        $channels = $this->channelRepository->findByPlayer($player, $privateOnly);

        if (!$this->canPlayerCommunicate($player)) {
            $publicChannel = $channels->filter(fn (Channel $channel) => $channel->isPublic())->first();
            $channels->removeElement($publicChannel);
        }

        return $channels;
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
        $this->eventDispatcher->dispatch($event, ChannelEvent::NEW_CHANNEL);

        return $channel;
    }

    public function getInvitablePlayersToPrivateChannel(Channel $channel): PlayerCollection
    {
        $maxPrivateChannel = $channel->getDaedalus()->getGameConfig()->getMaxNumberPrivateChannel();

        return new PlayerCollection($this->channelPlayerRepository->findAvailablePlayerForPrivateChannel($channel, $maxPrivateChannel));
    }

    public function invitePlayer(Player $player, Channel $channel): Channel
    {
        $event = new ChannelEvent($channel, CommunicationActionEnum::INVITED, new \DateTime(), $player);
        $this->eventDispatcher->dispatch($event, ChannelEvent::JOIN_CHANNEL);

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
        $this->eventDispatcher->dispatch($event, ChannelEvent::EXIT_CHANNEL);

        return true;
    }

    public function deleteChannel(Channel $channel): bool
    {
        $this->entityManager->remove($channel);
        $this->entityManager->flush();

        return true;
    }
}
