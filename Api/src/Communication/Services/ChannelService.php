<?php

namespace Mush\Communication\Services;

use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Repository\ChannelRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Communication\Entity\Channel;
use Mush\Player\Entity\Player;

class ChannelService implements ChannelServiceInterface
{
    private EntityManagerInterface $entityManager;
    private ChannelRepository $channelRepository;

    public function __construct(EntityManagerInterface $entityManager, ChannelRepository $channelRepository)
    {
        $this->entityManager = $entityManager;
        $this->channelRepository = $channelRepository;
    }

    public function getPlayerChannels(Player $player): Collection
    {
        return $this->channelRepository->findByPlayer($player);
    }

    public function createPrivateChannel(Player $player): Channel
    {
        $channel = new Channel();
        $channel
            ->setScope(ChannelScopeEnum::PRIVATE)
            ->addParticipant($player)
        ;

        $this->entityManager->persist($channel);
        $this->entityManager->flush();

        return $channel;
    }

    public function invitePlayer(Player $player, Channel $channel): Channel
    {
        $channel->addParticipant($player);

        $this->entityManager->persist($channel);
        $this->entityManager->flush();

        return $channel;
    }

    public function exitChannel(Player $player, Channel $channel): Channel
    {
        $channel->removeParticipant($player);

        $this->entityManager->persist($channel);
        $this->entityManager->flush();

        return $channel;
    }
}