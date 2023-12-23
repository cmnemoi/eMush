<?php

declare(strict_types=1);

namespace Mush\Hunter\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Hunter\Enum\HunterEnum;

class HunterNormalizerHelper implements HunterNormalizerHelperInterface
{
    private const NUMBER_OF_HUNTERS_TO_NORMALIZE = 17;

    /**
     * Function which returns the hunters to normalize.
     * We don't normalize all attacking hunters, because there would be too many of them displayed on the screen.
     * This function caps the number of hunters (currently 17) and guarantees that there will be at least 1 hunter of each type normalized.
     */
    public function getHuntersToNormalize(Daedalus $daedalus): HunterCollection
    {
        $attackingHunters = $daedalus->getAttackingHunters();

        // we want to normalize only 17 hunters. If there are less than 17 hunters, no treatment is needed
        if ($attackingHunters->count() <= self::NUMBER_OF_HUNTERS_TO_NORMALIZE) {
            return $attackingHunters;
        }

        $huntersToNormalize = new HunterCollection();

        // we need one advanced hunter of each type
        foreach ($this->getAdvancedHuntersToNormalize($daedalus) as $hunter) {
            $huntersToNormalize->add($hunter);
        }

        // then we fill the rest with simple hunters
        $numberOfSimpleHuntersToNormalize = self::NUMBER_OF_HUNTERS_TO_NORMALIZE - $huntersToNormalize->count();
        foreach ($this->getSimpleHuntersToNormalize($daedalus, $numberOfSimpleHuntersToNormalize) as $hunter) {
            $huntersToNormalize->add($hunter);
        }

        return $huntersToNormalize;
    }

    private function getAdvancedHuntersToNormalize(Daedalus $daedalus): HunterCollection
    {
        $advancedHunters = $daedalus->getAttackingHunters()->getAllHuntersExcept(HunterEnum::HUNTER);
        $advancedHuntersToNormalize = new HunterCollection();

        foreach (HunterEnum::getAdvancedHunters() as $hunterName) {
            $advancedHunter = $advancedHunters->getOneHunterByType($hunterName);
            if (!$advancedHunter) {
                continue;
            }

            $advancedHuntersToNormalize->add($advancedHunter);
        }

        return $advancedHuntersToNormalize;
    }

    private function getSimpleHuntersToNormalize(Daedalus $daedalus, int $number): HunterCollection
    {
        $simpleHunters = $daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::HUNTER);
        $simpleHuntersToNormalize = new HunterCollection();

        for ($i = 0; $i < $number; ++$i) {
            if ($simpleHunters[$i] === null) {
                break;
            }

            $simpleHuntersToNormalize->add($simpleHunters[$i]);
        }

        return $simpleHuntersToNormalize;
    }
}
