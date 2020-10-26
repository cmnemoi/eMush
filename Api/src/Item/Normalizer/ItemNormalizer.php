<?php

namespace Mush\Item\Normalizer;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Normalizer\DaedalusNormalizer;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Item;
use Mush\Player\Entity\Player;
use Mush\Room\Normalizer\RoomNormalizer;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
