<?php

namespace Mush\Action\Service;

use Mush\Action\Event\ActionEvent;

interface PatrolShipManoeuvreServiceInterface
{
    public function handlePatrolshipManoeuvreDamage(ActionEvent $event): void;

    public function handleLand(ActionEvent $event): void;
}
