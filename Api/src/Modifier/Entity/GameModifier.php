<?php

declare(strict_types=1);

namespace Mush\Modifier\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;

#[ORM\Entity]
#[ORM\Table(name: 'game_modifier')]
class GameModifier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: AbstractModifierConfig::class)]
    private AbstractModifierConfig $modifierConfig;

    #[ORM\OneToOne(inversedBy: 'gameModifier', targetEntity: ModifierHolder::class, cascade: ['All'])]
    private ModifierHolder $modifierHolder;

    #[ORM\OneToOne(targetEntity: ModifierProvider::class, cascade: ['All'])]
    private ModifierProvider $modifierProvider;

    public function __construct(ModifierHolderInterface $holder, AbstractModifierConfig $modifierConfig)
    {
        $this->modifierConfig = $modifierConfig;

        $modifierHolder = new ModifierHolder();
        $modifierHolder
            ->setModifierHolder($holder)
            ->setGameModifier($this);
        $this->modifierHolder = $modifierHolder;

        $holder->addModifier($this);
    }

    public static function createNullEventModifier(): self
    {
        $modifier = new self(holder: Player::createNull(), modifierConfig: EventModifierConfig::createNull());
        $modifier->setId(0);

        return $modifier;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getModifierConfig(): AbstractModifierConfig
    {
        return $this->modifierConfig;
    }

    public function getVariableModifierConfigOrThrow(): VariableEventModifierConfig
    {
        return $this->modifierConfig instanceof VariableEventModifierConfig ? $this->modifierConfig : throw new \LogicException("{$this->getName()} is not a variable event modifier!");
    }

    public function getTriggerModifierConfigOrThrow(): TriggerEventModifierConfig
    {
        return $this->modifierConfig instanceof TriggerEventModifierConfig ? $this->modifierConfig : throw new \RuntimeException("{$this->modifierConfig->getName()} is not a trigger event modifier!");
    }

    public function getModifierHolder(): ModifierHolderInterface
    {
        return $this->modifierHolder->getModifierHolder();
    }

    public function getModifierHolderJoinTable(): ModifierHolder
    {
        return $this->modifierHolder;
    }

    public function setModifierProvider(ModifierProviderInterface $modifierProvider): static
    {
        $modifierProviderEntity = new ModifierProvider();
        $modifierProviderEntity->setModifierProvider($modifierProvider);

        $this->modifierProvider = $modifierProviderEntity;

        return $this;
    }

    public function isNull(): bool
    {
        return $this->getId() === 0 || $this->getModifierConfig()->isNull();
    }

    public function getModifierProvider(): ModifierProviderInterface
    {
        return $this->modifierProvider->getModifierProvider();
    }

    public function isProviderActive(): bool
    {
        $modifierName = $this->modifierConfig->getModifierName();

        if ($modifierName === null) {
            return true;
        }

        $operationalStatus = $this->modifierProvider->getModifierProvider()->getOperationalStatus($modifierName);

        return $operationalStatus === ActionProviderOperationalStateEnum::OPERATIONAL;
    }

    public function getUsedCharge(): ?ChargeStatus
    {
        $modifierProvider = $this->modifierProvider->getModifierProvider();
        $modifierName = $this->modifierConfig->getModifierName();

        if ($modifierName === null) {
            return null;
        }

        return $modifierProvider->getUsedCharge($modifierName);
    }

    public function getModifierNameOrNull(): ?string
    {
        return $this->modifierConfig->getModifierName();
    }

    private function getName(): string
    {
        return $this->modifierConfig->getName();
    }

    private function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }
}
