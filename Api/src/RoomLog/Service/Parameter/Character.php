<?php

namespace Mush\RoomLog\Service\Parameter;

use Symfony\Contracts\EventDispatcher\Event;

class Character extends AbstractHandler implements HandlerInterface
{
    /** @var string */
    public const NAME = 'character';

    public function canProcess(): bool
    {
        return $this->data->getPlayer() !== null;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function execute()
    {
        return $this->data->getPlayer()->getCharacterConfig()->getName();
    }
}