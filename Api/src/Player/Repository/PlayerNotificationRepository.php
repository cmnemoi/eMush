<?php

declare(strict_types=1);

namespace Mush\Player\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Player\Entity\PlayerNotification;

/**
 * @template-extends ServiceEntityRepository<PlayerNotification>
 */
final class PlayerNotificationRepository extends ServiceEntityRepository implements PlayerNotificationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerNotification::class);
    }

    public function save(PlayerNotification $playerNotification): void
    {
        $this->_em->persist($playerNotification);
        $this->_em->flush();
    }

    public function delete(PlayerNotification $playerNotification): void
    {
        $playerNotification->getPlayer()->deleteNotification();
        $this->_em->remove($playerNotification);
        $this->_em->flush();
    }
}
