<?php

namespace Mush\Status\Listener;

use Mush\Action\Event\ApplyEffectEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ApplyEffectSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;

    public function __construct(
        StatusServiceInterface $statusService,
    ) {
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ApplyEffectEvent::CONSUME => 'onConsume',
        ];
    }

    public function onConsume(ApplyEffectEvent $event)
    {
        $drug = $event->getParameter();
        $player = $event->getAuthor();

        if (!$drug instanceof GameEquipment) {
            throw new UnexpectedTypeException($drug, Drug::class);
        }

        /** @var Drug $drugMechanic */
        $drugMechanic = $drug->getEquipment()->getMechanicByName(EquipmentMechanicEnum::DRUG);

        if ($drugMechanic !== null && !$player->isMush()) {
            $status = $this->statusService->createStatusFromName(
                PlayerStatusEnum::DRUG_EATEN,
                $player,
                $event->getTags(),
                $event->getTime()
            );
        }
    }
}
