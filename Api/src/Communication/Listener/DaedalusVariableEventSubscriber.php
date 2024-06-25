<?php

declare(strict_types=1);

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Project\Enum\ProjectName;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DaedalusVariableEventSubscriber implements EventSubscriberInterface
{
    public const array NERON_MESSAGE_BY_EVENT_TAG = [
        ProjectName::OXY_MORE->value => NeronMessageEnum::OXYGENATED_DUCTS,
    ];

    public function __construct(private NeronMessageServiceInterface $neronMessageService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            VariableEventInterface::CHANGE_VARIABLE => 'onChangeVariable',
        ];
    }

    public function onChangeVariable(VariableEventInterface $event): void
    {
        if (!$event instanceof DaedalusVariableEvent) {
            return;
        }

        $messageKey = $event->mapLog(self::NERON_MESSAGE_BY_EVENT_TAG);
        if ($messageKey === null) {
            return;
        }

        $this->neronMessageService->createNeronMessage(
            messageKey: $messageKey,
            daedalus: $event->getDaedalus(),
            parameters: [],
            dateTime: $event->getTime(),
        );
    }
}
