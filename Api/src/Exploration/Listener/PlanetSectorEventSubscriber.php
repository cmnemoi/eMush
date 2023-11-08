<?php

declare(strict_types=1);

namespace Mush\Exploration\Listener;

use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlanetSectorEventSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private ExplorationServiceInterface $explorationService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        ExplorationServiceInterface $explorationService,
        RandomServiceInterface $randomService,
    ) {
        $this->eventService = $eventService;
        $this->explorationService = $explorationService;
        $this->randomService = $randomService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlanetSectorEvent::ACCIDENT => 'onAccident',
            PlanetSectorEvent::DISASTER => 'onDisaster',
            PlanetSectorEvent::NOTHING_TO_REPORT => 'onNothingToReport',
            PlanetSectorEvent::TIRED => 'onTired',
        ];
    }

    public function onAccident(PlanetSectorEvent $event): void
    {
        $logParameters = $this->removeHealthToARandomExplorator($event);

        $this->explorationService->createExplorationLog($event, $logParameters);
    }

    public function onDisaster(PlanetSectorEvent $event): void
    {
        $logParameters = $this->removeHealthPointsToAllExplorators($event);

        $this->explorationService->createExplorationLog($event, $logParameters);
    }

    public function onNothingToReport(PlanetSectorEvent $event): void
    {
        $this->explorationService->createExplorationLog($event);
    }

    public function onTired(PlanetSectorEvent $event): void
    {
        $logParameters = $this->removeHealthPointsToAllExplorators($event);

        $this->explorationService->createExplorationLog($event, $logParameters);
    }

    // @TODO move this to a service
    private function removeHealthPointsToAllExplorators(PlanetSectorEvent $event): array
    {
        $exploration = $event->getExploration();

        $healthLost = $this->drawEventOutputQuantity($event->getOutputQuantityTable());
        foreach ($exploration->getExplorators() as $player) {
            $playerVariableEvent = new PlayerVariableEvent(
                player: $player,
                variableName: PlayerVariableEnum::HEALTH_POINT,
                quantity: -$healthLost,
                tags: $event->getTags(),
                time: new \DateTime()
            );
            $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
        }

        return array_merge([
            'quantity' => $healthLost,
        ], $event->getLogParameters());
    }

    // @TODO move this to a service
    private function removeHealthToARandomExplorator(PlanetSectorEvent $event): array
    {
        $exploration = $event->getExploration();
        $exploratorToInjure = $this->randomService->getRandomPlayer($exploration->getExplorators());
        $healthLost = $this->drawEventOutputQuantity($event->getOutputQuantityTable());

        $playerVariableEvent = new PlayerVariableEvent(
            player: $exploratorToInjure,
            variableName: PlayerVariableEnum::HEALTH_POINT,
            quantity: -$healthLost,
            tags: $event->getTags(),
            time: new \DateTime()
        );
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);

        return array_merge([
            $exploratorToInjure->getLogKey() => $exploratorToInjure->getLogName(),
            'quantity' => $healthLost,
        ], $event->getLogParameters());
    }

    // @TODO move this to a service
    private function drawEventOutputQuantity(?ProbaCollection $outputQuantityTable): int
    {
        if ($outputQuantityTable === null) {
            throw new \RuntimeException('You need an output quantity table to draw an event output quantity');
        }

        $quantity = $this->randomService->getSingleRandomElementFromProbaCollection($outputQuantityTable);
        if (!is_int($quantity)) {
            throw new \RuntimeException('Quantity should be an int');
        }

        return $quantity;
    }
}
