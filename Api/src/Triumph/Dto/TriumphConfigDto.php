<?php

declare(strict_types=1);

namespace Mush\Triumph\Dto;

use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Enum\TriumphScope;
use Mush\Triumph\Enum\TriumphVisibility;

/**
 * @property string            $key              The unique key of the triumph
 * @property TriumphEnum       $name             The name of the triumph
 * @property TriumphScope      $scope            Determines which players receive the triumph (see [`TriumphScope`](./Enum/TriumphScope.php))
 * @property string            $targetedEvent    The event that triggers this triumph
 * @property int               $quantity         The amount of triumph points awarded
 * @property array             $tagConstraints   Controls the application of the triumph based on the event tags
 * @property TriumphVisibility $visibility       Controls the visibility of triumph log
 * @property string            $target           If set, only this character will receive the triumph. You can combine this `scope` to create more complex targeting conditions
 * @property int               $regressiveFactor Determines after how many gains the gains has 2x less chance to be earned (TODO)
 */
final readonly class TriumphConfigDto
{
    public function __construct(
        public string $key,
        public TriumphEnum $name,
        public TriumphScope $scope,
        public string $targetedEvent,
        public int $quantity,
        public array $tagConstraints = [],
        public TriumphVisibility $visibility = TriumphVisibility::PRIVATE,
        public string $target = '',
        public int $regressiveFactor = 0,
    ) {}
}
