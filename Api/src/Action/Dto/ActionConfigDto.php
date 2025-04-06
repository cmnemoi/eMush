<?php

declare(strict_types=1);

namespace Mush\Action\Dto;

use Mush\Action\Entity\ActionVariables;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;

final readonly class ActionConfigDto
{
    public function __construct(
        public string $name,
        public ActionEnum $actionName,
        public array $types = [],
        public ActionHolderEnum $target,
        public ActionRangeEnum $scope,
        public array $visibilities = [ActionOutputEnum::SUCCESS => VisibilityEnum::PUBLIC, ActionOutputEnum::FAIL => VisibilityEnum::PRIVATE],
        public ActionVariables $actionVariables = new ActionVariables(),
    ) {}
}