<?php

namespace Mush\Communication\Services;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Repository\ChannelRepository;
use Mush\Daedalus\Entity\Daedalus;
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

    public function getPlayerChannels(Player $player, bool $privateOnly = false): Collection
    {
        return $this->channelRepository->findByPlayer($player, $privateOnly);
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
            ->addParticipant($player)
        ;

        $this->entityManager->persist($channel);
        $this->entityManager->flush();

        return $channel;
    }

    public function invitePlayerToPublicChannel(Player $player): ?Channel
    {
        /** @var Channel $publicChannel */
        $publicChannel = $this->channelRepository->findOneBy([
            'daedalus' => $player->getDaedalus(),
            'scope' => ChannelScopeEnum::PUBLIC,
        ]);

        if ($publicChannel === null) {
            return null;
        }

        $publicChannel->addParticipant($player);

        $this->entityManager->persist($publicChannel);
        $this->entityManager->flush();

        return $publicChannel;
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
