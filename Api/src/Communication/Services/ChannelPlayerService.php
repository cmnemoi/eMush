<?php

namespace Mush\Communication\Services;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Player\Entity\PlayerInfo;

class ChannelPlayerService implements ChannelPlayerServiceInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    public function addPlayer(PlayerInfo $playerInfo, Channel $channel): ChannelPlayer
    {
        $channelPlayer = new ChannelPlayer();

        $channelPlayer
            ->setChannel($channel)
            ->setParticipant($playerInfo)
        ;

        $this->entityManager->persist($channelPlayer);
        $this->entityManager->flush();

        return $channelPlayer;
    }

    public function removePlayer(PlayerInfo $playerInfo, Channel $channel): bool
    {
        $channelParticipant = $channel->getParticipants()
            ->filter(fn (ChannelPlayer $channelPlayer) => ($channelPlayer->getParticipant() === $playerInfo))
        ;

        if ($channelParticipant->isEmpty()) {
            return false;
        }

        $this->entityManager->remove($channelParticipant->first());
        $this->entityManager->flush();

        return true;
    }
}
