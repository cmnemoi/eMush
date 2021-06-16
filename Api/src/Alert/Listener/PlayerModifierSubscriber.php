<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Service\AlertServiceInterface;
use Mush\Player\Event\PlayerModifierEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerModifierSubscriber implements EventSubscriberInterface
{
    private AlertServiceInterface $alertService;

    public function __construct(
        AlertServiceInterface $alertService
    ) {
        $this->alertService = $alertService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerModifierEvent::SATIETY_POINT_MODIFIER => 'onSatietyPointModifier',
        ];
    }

    public function onSatietyPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $this->alertService->handleSatietyAlert($playerEvent->getPlayer()->getDaedalus());
    }
}
