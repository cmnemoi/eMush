<?php

namespace Mush\Communication\Services;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Player\Entity\Player;

class ChannelPlayerService implements ChannelPlayerServiceInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
