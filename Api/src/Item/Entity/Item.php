<?php

namespace Mush\Item\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Item\Entity\Items\Ration;
use Mush\Item\Enum\ItemTypeEnum;

/**
 * Class ItemConfig.
 *
 * @ORM\Entity
 */
class Item
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Game\Entity\GameConfig", inversedBy="itemsConfig")
     */
    private GameConfig $gameConfig;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name;

    /**
     * @ORM\ManyToMany(targetEntity="Mush\Item\Entity\ItemType")
     */
    private Collection $types;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isHeavy;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isTakeable;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isDropable;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $breakableRate = 0;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isStackable;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isHideable;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isFireDestroyable;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isFireBreakable;

    public function __construct()
    {
        $this->types = new ArrayCollection();
    }

    public function createGameItem(): GameItem
    {
        $gameItem = new GameItem();
        $gameItem
            ->setName($this->getName())
            ->setItem($this)
        ;

        return $gameItem;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getGameConfig(): GameConfig
    {
        return $this->gameConfig;
    }

    public function setGameConfig(GameConfig $gameConfig): Item
    {
        $this->gameConfig = $gameConfig;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Item
    {
        $this->name = $name;

        return $this;
    }

    public function getTypes(): Collection
    {
        return $this->types;
    }

    public function setTypes(Collection $types): Item
    {
        $this->types = $types;

        return $this;
    }

    public function getItemType(string $type): ?ItemType
    {
        $itemTypes = $this->types->filter(fn (ItemType $itemType) => ($itemType->getType() === $type));

        return $itemTypes->count() > 0 ? $itemTypes->first() : null;
    }

    public function getItemTypeByTypes(array $types): ?ItemType
    {
        $itemTypes = $this->types->filter(fn (ItemType $itemType) => (in_array($itemType->getType(), $types)));

        return $itemTypes->count() > 0 ? $itemTypes->first() : null;
    }

    public function getRationsType(): ?Ration
    {
        return $this->getItemTypeByTypes([ItemTypeEnum::RATION, ItemTypeEnum::FRUIT, ItemTypeEnum::DRUG]);
    }

    public function isHeavy(): bool
    {
        return $this->isHeavy;
    }

    public function setIsHeavy(bool $isHeavy): Item
    {
        $this->isHeavy = $isHeavy;

        return $this;
    }

    public function isTakeable(): bool
    {
        return $this->isTakeable;
    }

    public function setIsTakeable(bool $isTakeable): Item
    {
        $this->isTakeable = $isTakeable;

        return $this;
    }

    public function isDropable(): bool
    {
        return $this->isDropable;
    }

    public function setIsDropable(bool $isDropable): Item
    {
        $this->isDropable = $isDropable;

        return $this;
    }

    public function getBreakableRate(): int
    {
        return $this->breakableRate;
    }

    public function setBreakableRate(int $breakableRate): Item
    {
        $this->breakableRate = $breakableRate;

        return $this;
    }

    public function isStackable(): bool
    {
        return $this->isStackable;
    }

    public function setIsStackable(bool $isStackable): Item
    {
        $this->isStackable = $isStackable;

        return $this;
    }

    public function isHideable(): bool
    {
        return $this->isHideable;
    }

    public function setIsHideable(bool $isHideable): Item
    {
        $this->isHideable = $isHideable;

        return $this;
    }

    public function isFireDestroyable(): bool
    {
        return $this->isFireDestroyable;
    }

    public function setIsFireDestroyable(bool $isFireDestroyable): Item
    {
        $this->isFireDestroyable = $isFireDestroyable;

        return $this;
    }

    public function isFireBreakable(): bool
    {
        return $this->isFireBreakable;
    }

    public function setIsFireBreakable(bool $isFireBreakable): Item
    {
        $this->isFireBreakable = $isFireBreakable;

        return $this;
    }

    public function getActions(): Collection
    {
        $actions = ActionEnum::getPermanentItemActions();

        foreach ($this->getTypes() as $itemType) {
            $actions = array_merge($actions, $itemType->getActions());
        }

        return new ArrayCollection($actions);
    }

    public function hasAction(string $action): bool
    {
        return $this->getActions()->contains($action);
    }
}
