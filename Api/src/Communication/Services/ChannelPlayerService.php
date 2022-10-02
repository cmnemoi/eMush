<?php

namespace Mush\Communication\Services;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Repository\ChannelRepository;
use Mush\Player\Entity\Player;

class ChannelPlayerService implements ChannelPlayerServiceInterface
{
    private EntityManagerInterface $entityManager;
    private ChannelServiceInterface $channelService;
    private ChannelRepository $channelRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ChannelServiceInterface $channelService,
        ChannelRepository $channelRepository
    ) {
        $this->entityManager = $entityManager;
        $this->channelService = $channelService;
        $this->channelRepository = $channelRepository;
    }

    public function addPlayer(Player $player, Channel $channel): ChannelPlayer
    {
        $channelPlayer = new ChannelPlayer();

        $channelPlayer
            ->setChannel($channel)
            ->setParticipant($player)
        ;

        $this->entityManager->persist($channelPlayer);
        $this->entityManager->flush();

        return $channelPlayer;
    }

    public function removePlayer(Player $player, Channel $channel): bool
    {
        $channelParticipant = $channel->getParticipants()
            ->filter(fn (ChannelPlayer $channelPlayer) => ($channelPlayer->getParticipant() === $player))
        ;

        if ($channelParticipant->isEmpty()) {
            return false;
        }

        $this->entityManager->remove($channelParticipant->first());
        $this->entityManager->flush();

        return true;
    }
}
