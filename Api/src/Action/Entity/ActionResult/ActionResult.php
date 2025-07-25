<?php

namespace Mush\Action\Entity\ActionResult;

use Mush\Action\Entity\ActionProviderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;

abstract class ActionResult
{
    protected const DEFAULT = 'default';

    private ?Player $player = null;
    private ?LogParameterInterface $target = null;
    private ?ActionProviderInterface $actionProvider;

    private ?GameEquipment $equipment = null;
    private ?int $quantity = null;
    private string $visibility = VisibilityEnum::HIDDEN;
    private ?string $content = null;
    private array $details = [];

    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function getPlayer(): Player
    {
        return $this->player ?? throw new \RuntimeException('Player is not set');
    }

    public function setTarget(?LogParameterInterface $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getTarget(): LogParameterInterface
    {
        return $this->target ?? throw new \RuntimeException('Target is not set');
    }

    public function getTargetAsPlayer(): ?Player
    {
        if ($this->target instanceof Player) {
            return $this->target;
        }

        return null;
    }

    public function getTargetAsPlayerOrThrow(): Player
    {
        if ($this->target instanceof Player) {
            return $this->target;
        }

        throw new \RuntimeException('Target is not Player');
    }

    public function setActionProvider(ActionProviderInterface $actionProvider): self
    {
        $this->actionProvider = $actionProvider;

        return $this;
    }

    public function getActionProvider(): ActionProviderInterface
    {
        return $this->actionProvider ?? throw new \RuntimeException('Action provider is not set');
    }

    public function getActionProviderAsGameItem(): GameItem
    {
        if ($this->actionProvider instanceof GameItem) {
            return $this->actionProvider;
        }

        throw new \RuntimeException('Action provider is not a GameItem');
    }

    public function getGameItemActionProviderOrDefault(): GameItem
    {
        if ($this->actionProvider instanceof GameItem) {
            return $this->actionProvider;
        }

        return GameItem::createNull();
    }

    public function setEquipment(GameEquipment $equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getEquipment(): ?GameEquipment
    {
        return $this->equipment;
    }

    public function getEquipmentOrThrow(): GameEquipment
    {
        return $this->equipment ?? throw new \RuntimeException('Equipment is not set');
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function getQuantityOrThrow(): int
    {
        return $this->quantity ?? throw new \RuntimeException('Quantity is not set');
    }

    public function getQuantityOr(int $default): int
    {
        return $this->quantity ?? $default;
    }

    public function getName(): string
    {
        return self::DEFAULT;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getContentOrThrow(): string
    {
        return $this->content ?? throw new \RuntimeException('Content is not set');
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function addDetail(string $key, $value): self
    {
        $this->details[$key] = $value;

        return $this;
    }

    public function isASuccess(): bool
    {
        return $this instanceof Success;
    }

    public function isAFail(): bool
    {
        return $this instanceof Fail;
    }

    public function isACriticalSuccess(): bool
    {
        return $this instanceof CriticalSuccess;
    }

    public function isACriticalFail(): bool
    {
        return $this instanceof CriticalFail;
    }

    public function isNotACriticalSuccess(): bool
    {
        return $this instanceof CriticalSuccess === false;
    }

    public function getResultTags(): array
    {
        $tags = [$this->isASuccess() ? ActionOutputEnum::SUCCESS : ActionOutputEnum::FAIL];
        $this->isACriticalSuccess() ? $tags[] = ActionOutputEnum::CRITICAL_SUCCESS : null;
        $this->isACriticalFail() ? $tags[] = ActionOutputEnum::CRITICAL_FAIL : null;

        return $tags;
    }

    public function doesNotHaveContent(): bool
    {
        return $this->getContent() === null;
    }
}
