<?php

namespace Mush\Player\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @template-extends ArrayCollection<int, CharacterConfig>
 */
class CharacterConfigCollection extends ArrayCollection
{
    public function getCharacter(string $name): ?CharacterConfig
    {
        $character = $this
            ->filter(static fn (CharacterConfig $characterConfig) => $characterConfig->getCharacterName() === $name)
            ->first();

        return $character === false ? null : $character;
    }
}
