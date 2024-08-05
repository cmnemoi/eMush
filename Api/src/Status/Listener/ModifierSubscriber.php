<?php

namespace Mush\Status\Listener;

use Mush\Modifier\Event\ModifierEvent;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ModifierSubscriber implements EventSubscriberInterface
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
            ModifierEvent::APPLY_MODIFIER => 'onApplyModifier',
        ];
    }

    public function onApplyModifier(ModifierEvent $event): void
    {
        $modifier = $event->getModifier();

        if (($charge = $modifier->getUsedCharge()) !== null) {
            $this->statusService->updateCharge($charge, -1, $event->getTags(), $event->getTime());
        }
    }
}
