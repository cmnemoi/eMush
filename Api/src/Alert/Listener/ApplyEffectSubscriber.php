<?php

namespace Mush\Alert\Listener;

use Mush\Action\Event\ApplyEffectEvent;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ApplyEffectSubscriber implements EventSubscriberInterface
{
    private AlertServiceInterface $alertService;
    private LoggerInterface $logger;

    public function __construct(
        AlertServiceInterface $alertService,
        LoggerInterface $logger
    ) {
        $this->alertService = $alertService;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ApplyEffectEvent::REPORT_FIRE => 'onReportFire',
            ApplyEffectEvent::REPORT_EQUIPMENT => 'onReportEquipment',
        ];
    }

    public function onReportFire(ApplyEffectEvent $event): void
    {
        $player = $event->getAuthor();
        $place = $event->getPlace();

        $alert = $this->alertService->findByNameAndDaedalus(AlertEnum::FIRES, $player->getDaedalus());
        if ($alert === null) {
            throw new \LogicException('there should be a fire alert on this Daedalus');
        }

        $alertElement = $this->alertService->getAlertFireElement($alert, $place);

        $alertElement->setPlayerInfo($player->getPlayerInfo());
        $this->alertService->persistAlertElement($alertElement);
    }

    public function onReportEquipment(ApplyEffectEvent $event): void
    {
        $player = $event->getAuthor();
        $equipment = $event->getParameter();
        if (!$equipment instanceof GameEquipment) {
            throw new UnexpectedTypeException($equipment, GameEquipment::class);
        }
        if ($equipment instanceof GameItem) {
            $this->logger->info(
                'GameItem should not be reported',
                [
                    'player' => $player->getId(),
                    'equipment' => $equipment->getName(),
                ]
            );

            return;
        }

        if ($equipment instanceof Door) {
            $alertName = AlertEnum::BROKEN_DOORS;
        } else {
            $alertName = AlertEnum::BROKEN_EQUIPMENTS;
        }

        $alert = $this->alertService->findByNameAndDaedalus($alertName, $player->getDaedalus());
        if ($alert === null) {
            throw new \LogicException('there should be a broken equipment alert on this Daedalus');
        }

        $alertElement = $this->alertService->getAlertEquipmentElement($alert, $equipment);

        $alertElement->setPlayerInfo($player->getPlayerInfo())->setPlace($player->getPlace());

        $this->alertService->persistAlertElement($alertElement);
    }
}
