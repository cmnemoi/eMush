<?php

declare(strict_types=1);

namespace Mush\Game\Listener;

use Mush\Game\Entity\TimestampableCancelInterface;

/**
 * Listener to prevent some entities from being timestamped.
 * Author: zajca (https://stackoverflow.com/a/53666915)
 */
final class TimestampableListener extends \Gedmo\Timestampable\TimestampableListener
{
    protected function updateField($object, $eventAdapter, $meta, $field)
    {
        /** @var \Doctrine\Orm\Mapping\ClassMetadata $meta */
        $property = $meta->getReflectionProperty($field);
        $newValue = $this->getFieldValue($meta, $field, $eventAdapter);

        if (!$this->isTimestampableCanceled($object)) {
            $property->setValue($object, $newValue);
        }
    }

    private function isTimestampableCanceled($object): bool
    {
        if (!$object instanceof TimestampableCancelInterface) {
            return false;
        }

        return $object->isTimestampableCanceled();
    }
}
