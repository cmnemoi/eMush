<?php

namespace Mush\Item\Entity;

class ItemConfig
{
    private string $name;

    private string $type;

    private bool $isHeavy;

    private bool $isDismantable;

    private bool $isStackable;

    private bool $isHideable;

    private bool $isMovable;

    private bool $isFireDestroyable;

    private bool $isFireBreakable;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ItemConfig
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): ItemConfig
    {
        $this->type = $type;
        return $this;
    }

    public function isHeavy(): bool
    {
        return $this->isHeavy;
    }

    public function setIsHeavy(bool $isHeavy): ItemConfig
    {
        $this->isHeavy = $isHeavy;
        return $this;
    }

    public function isDismantable(): bool
    {
        return $this->isDismantable;
    }

    public function setIsDismantable(bool $isDismantable): ItemConfig
    {
        $this->isDismantable = $isDismantable;
        return $this;
    }

    public function isStackable(): bool
    {
        return $this->isStackable;
    }

    public function setIsStackable(bool $isStackable): ItemConfig
    {
        $this->isStackable = $isStackable;
        return $this;
    }

    public function isHideable(): bool
    {
        return $this->isHideable;
    }

    public function setIsHideable(bool $isHideable): ItemConfig
    {
        $this->isHideable = $isHideable;
        return $this;
    }

    public function isMovable(): bool
    {
        return $this->isMovable;
    }

    public function setIsMovable(bool $isMovable): ItemConfig
    {
        $this->isMovable = $isMovable;
        return $this;
    }

    public function isFireDestroyable(): bool
    {
        return $this->isFireDestroyable;
    }

    public function setIsFireDestroyable(bool $isFireDestroyable): ItemConfig
    {
        $this->isFireDestroyable = $isFireDestroyable;
        return $this;
    }

    public function isFireBreakable(): bool
    {
        return $this->isFireBreakable;
    }

    public function setIsFireBreakable(bool $isFireBreakable): ItemConfig
    {
        $this->isFireBreakable = $isFireBreakable;
        return $this;
    }
}