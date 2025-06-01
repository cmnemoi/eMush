<?php

declare(strict_types=1);

namespace Mush\Triumph\Dto;

use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Enum\TriumphScope;
use Mush\Triumph\Enum\TriumphTarget;
use Mush\Triumph\Enum\TriumphVisibility;

/**
 * @property string            $key              The unique key of the triumph
 * @property TriumphEnum       $name             The name of the triumph
 * @property TriumphScope      $scope            Determines which players receive the triumph (see [`TriumphScope`](./Enum/TriumphScope.php))
 * @property string            $targetedEvent    The event that triggers this triumph
 * @property int               $quantity         The amount of triumph points awarded
 * @property array             $tagConstraints   Controls the application of the triumph based on the event tags
 * @property TriumphVisibility $visibility       Controls the visibility of triumph log
 * @property TriumphTarget     $target           If set, only this player will receive the triumph if fits into the scope
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
        public TriumphTarget $target = TriumphTarget::NONE,
        public int $regressiveFactor = 0,
    ) {}
}
