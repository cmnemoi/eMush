<?php

namespace Mush\Action\Validator;

use Mush\Place\Enum\PlaceTypeEnum;

/**
 * Raises a violation if scrap metal needed to do the action, based on the room type and the target name.
 *
 * The violation will be raised only if the action target room type and name are in the passed parameters.
 *
 * @param array $roomTypes   Room types to check. Currently only `PlaceTypeEnum::ROOM` is supported.
 * @param array $targetNames target names to check
 */
class ScrapMetalNeeded extends ClassConstraint
{
    public string $message = 'Scrap metal is needed to perform this action.';

    public array $roomTypes = [PlaceTypeEnum::ROOM];
    public array $targetNames = [];
}
