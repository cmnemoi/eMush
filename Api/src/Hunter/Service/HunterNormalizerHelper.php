<?php

declare(strict_types=1);

namespace Mush\Hunter\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Enum\HunterVariableEnum;

class HunterNormalizerHelper implements HunterNormalizerHelperInterface
{
    private const NUMBER_OF_HUNTERS_TO_NORMALIZE = 17;

    /**
     * Function which returns the hunters to normalize.
     * We don't normalize all attacking hunters, because there would be too many of them displayed on the screen.
     * This function :
     * - caps the number of hunters to normalize to 17
     * - ensures that there is at least one hunter of each type
     * - prioritizes hunters with low health.
     */
    public function getHuntersToNormalize(Daedalus $daedalus): HunterCollection
    {
        $attackingHunters = $daedalus->getAttackingHunters()->getAllHuntersSortedBy(HunterVariableEnum::HEALTH);

        // we want to normalize only 17 hunters. If there are less than 17 hunters, no treatment is needed
        if ($attackingHunters->count() <= self::NUMBER_OF_HUNTERS_TO_NORMALIZE) {
            return $attackingHunters;
        }

        $huntersToNormalize = new HunterCollection();

        // we need at least one hunter of each type
        foreach ($this->getOneHunterByType($attackingHunters) as $hunter) {
            $huntersToNormalize->add($hunter);
        }

        // then we fill the rest with arbitrary hunters
        foreach ($attackingHunters as $hunter) {
            if (!$huntersToNormalize->contains($hunter)) {
                $huntersToNormalize->add($hunter);
            }

            if ($huntersToNormalize->count() === self::NUMBER_OF_HUNTERS_TO_NORMALIZE) {
                break;
            }
        }

        return $huntersToNormalize;
    }

    private function getOneHunterByType(HunterCollection $hunters): HunterCollection
    {
        $huntersToNormalize = new HunterCollection();

        foreach (HunterEnum::getAll() as $hunterName) {
            $advancedHunter = $hunters->getOneHunterByType($hunterName);
            if (!$advancedHunter) {
                continue;
            }

            $huntersToNormalize->add($advancedHunter);
        }

        return $huntersToNormalize;
    }
}
