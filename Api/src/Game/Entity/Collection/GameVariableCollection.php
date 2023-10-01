<?php

namespace Mush\Game\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\ActionVariables;
use Mush\Daedalus\Entity\DaedalusVariables;
use Mush\Game\Entity\GameVariable;
use Mush\Hunter\Entity\HunterVariables;
use Mush\Player\Entity\PlayerVariables;
use Mush\Status\Entity\ChargeVariable;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'daedalusVariables' => DaedalusVariables::class,
    'playerVariables' => PlayerVariables::class,
    'actionVariables' => ActionVariables::class,
    'hunterVariables' => HunterVariables::class,
    'chargeVariable' => ChargeVariable::class,
])]
abstract class GameVariableCollection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToMany(mappedBy: 'gameVariableCollection', targetEntity: GameVariable::class, cascade: ['ALL'])]
    protected Collection $gameVariables;

    public function __construct(array $variables)
    {
        $this->gameVariables = new ArrayCollection($variables);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getGameVariables(): ArrayCollection
    {
        return new ArrayCollection($this->gameVariables->toArray());
    }

    public function getValueByName(string $name): int
    {
        /** @var GameVariable $variable */
        $variable = $this->gameVariables
            ->filter(fn (GameVariable $gameVariable) => $gameVariable->getName() === $name)
            ->first()
        ;

        return $variable->getValue();
    }

    public function getVariableByName(string $name): GameVariable
    {
        /** @var GameVariable $variable */
        $variable = $this->gameVariables
            ->filter(fn (GameVariable $gameVariable) => $gameVariable->getName() === $name)
            ->first()
        ;

        return $variable;
    }

    public function setValueByName(int $value, string $name): GameVariable
    {
        $variable = $this->getVariableByName($name);

        $variable->setValue($value);

        return $variable;
    }

    public function setValuesByName(array $values, string $name): GameVariable
    {
        $variable = $this->getVariableByName($name);

        $variable->setValuesFromArray($values);

        return $variable;
    }

    public function hasVariable(string $name): bool
    {
        return !$this->gameVariables
            ->filter(fn (GameVariable $gameVariable) => $gameVariable->getName() === $name)->isEmpty()
        ;
    }
}
