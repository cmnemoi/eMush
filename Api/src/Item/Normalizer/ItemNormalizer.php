<?php

namespace Mush\Item\Normalizer;

use Mush\Action\Enum\ActionEnum;
use Mush\Item\Entity\GameItem;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class ItemNormalizer implements ContextAwareNormalizerInterface
{
    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof GameItem;
    }

    /**
     * @param GameItem $item
     * @param string|null $format
     * @param array $context
     * @return array
     */
    public function normalize($item, string $format = null, array $context = [])
    {
        $actions = [];

        if ($item->getPlayer() === null) {
            $actions[] = ActionEnum::TAKE;
        } else {
            $actions[] = ActionEnum::DROP;
        }

        return [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'statuses' => $item->getStatuses(),
            'actions' => $actions
        ];
    }
}
