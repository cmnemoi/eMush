<?php

declare(strict_types=1);

namespace Mush\Triumph\Dto;

use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Enum\TriumphScope;
use Mush\Triumph\Enum\TriumphVisibility;

/**
 * @property string            $key                       The unique key of the triumph
 * @property TriumphEnum       $name                      The name of the triumph
 * @property TriumphScope      $scope                     Determines which players receive the triumph (see [`TriumphScope`](./Enum/TriumphScope.php))
 * @property string            $targetedEvent             The event that triggers this triumph
 * @property int               $quantity                  The amount of triumph points awarded
 * @property array             $targetedEventExpectedTags Additional tags that must be present in the event for the triumph to be awarded (for the moment, all tags must be present for the triumph to apply - TODO)
 * @property TriumphVisibility $visibility                Controls the visibility of triumph log
 * @property string            $target                    For `PERSONAL` scope, specifies which character receives the triumph
 * @property int               $regressiveFactor          Determines after how many gains the gains has 2x less chance to be earned (TODO)
 */
final readonly class TriumphConfigDto
{
    public function __construct(
        public string $key,
        public TriumphEnum $name,
        public TriumphScope $scope,
        public string $targetedEvent,
        public int $quantity,
        public array $targetedEventExpectedTags = [],
        public TriumphVisibility $visibility = TriumphVisibility::PRIVATE,
        public string $target = '',
        public int $regressiveFactor = 0,
    ) {}
}
