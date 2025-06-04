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
 * @property TriumphTarget     $targetSetting    Determines what relation to the event players should be in to receive the triumph, as long as they fit within the scope (see [`TriumphTarget`](./Enum/TriumphTarget.php))
 * @property int               $regressiveFactor Determines after how many gains the gains become 2x less likely
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
        public TriumphTarget $targetSetting = TriumphTarget::NONE,
        public int $regressiveFactor = 0,
    ) {}
}
