<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Service\AlertServiceInterface;
use Mush\Player\Event\PlayerModifierEventInterface;
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
            PlayerModifierEventInterface::SATIETY_POINT_MODIFIER => 'onSatietyPointModifier',
        ];
    }

    public function onSatietyPointModifier(PlayerModifierEventInterface $playerEvent): void
    {
        $this->alertService->handleSatietyAlert($playerEvent->getPlayer()->getDaedalus());
    }
}
