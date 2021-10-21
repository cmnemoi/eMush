<?php

namespace Mush\Status\Listener;

use Error;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        StatusServiceInterface $statusService,
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::INFECTION_PLAYER => ['onInfectionPlayer', 100], //do this before checking the number of spores
            PlayerEvent::CONVERSION_PLAYER => 'onConversionPlayer',
        ];
    }

    public function onInfectionPlayer(PlayerEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();

        /** @var ?ChargeStatus $playerSpores */
        $playerSpores = $player->getStatusByName(PlayerStatusEnum::SPORES);

        if ($playerSpores === null) {
            throw new Error('Player should have a spore status');
        }

        $playerSpores->addCharge(1);

        $this->statusService->persist($playerSpores);

        //@TODO implement research modifiers
        if ($playerSpores->getCharge() >= 3) {
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::CONVERSION_PLAYER);
        }
    }

    public function onConversionPlayer(PlayerEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();

        if ($player->isAlive()) {
            $sporeStatus = $player->getStatusByName(PlayerStatusEnum::SPORES);

            if (!($sporeStatus instanceof ChargeStatus)) {
                throw new Error('Player should have a spore status');
            }

            $sporeStatus->setCharge(0);
            $this->statusService->persist($sporeStatus);
        }

        $mushStatusConfig = $this->statusService->getStatusConfigByNameAndDaedalus(PlayerStatusEnum::MUSH, $player->getDaedalus());
        $mushStatus = $this->statusService->createStatusFromConfig($mushStatusConfig, $player, $playerEvent->getReason(), $playerEvent->getTime());
        $this->statusService->persist($mushStatus);
    }
}
