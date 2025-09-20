<?php

declare(strict_types=1);

namespace Mush\RoomLog\Entity;

use Mush\Game\Service\TranslationServiceInterface as Translate;

interface ExaminableInterface extends LogParameterInterface
{
    public function getNormalizationType(): string;

    public function toExamineLogParameters(Translate $translate): array;
}
