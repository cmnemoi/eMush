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

    public function getByNameOrThrow(string $name): CharacterConfig
    {
        $character = $this->getCharacter($name);

        if ($character === null) {
            throw new \LogicException("Character {$name} not available");
        }

        return $character;
    }

    public function getAllExcept(array $names): self
    {
        return $this->filter(
            static fn (CharacterConfig $character) => !\in_array($character->getName(), $names, true)
        );
    }
}
