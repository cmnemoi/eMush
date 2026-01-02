<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\Config\NpcConfig;
use Mush\Equipment\Enum\AIHandlerEnum;

#[ORM\Entity]
class Npc extends GameItem
{
    #[ORM\OneToOne(mappedBy: 'npc', targetEntity: NpcData::class, cascade: ['persist', 'remove'])]
    private NpcData $data;

    public function __construct(
        EquipmentHolderInterface $equipmentHolder,
    ) {
        parent::__construct($equipmentHolder);

        $this->data = new NpcData($this);
    }

    public function getAiHandler(): AIHandlerEnum
    {
        $config = $this->getEquipment();
        if ($config instanceof NpcConfig) {
            return $config->getAiHandler();
        }

        throw new \RuntimeException("NPC {$this->getName()} should have an NPC Config");
    }

    public function getMemory(): array
    {
        return $this->data->getMemory();
    }

    public function clearMemory(): self
    {
        $this->data->setMemory([]);

        return $this;
    }

    public function addDataToMemory(string $key, $value): self
    {
        $memory = $this->getMemory();
        $memory[$key] = $value;

        $this->data->setMemory($memory);

        return $this;
    }

    public function removeDataFromMemory(string $key): self
    {
        $memory = $this->getMemory();
        unset($memory[$key]);

        $this->data->setMemory($memory);

        return $this;
    }

    public function hasInMemory(string $key): bool
    {
        return \array_key_exists($key, $this->getMemory());
    }

    public function getDataFromMemory(string $key): mixed
    {
        if (!$this->hasInMemory($key)) {
            return null;
        }

        return $this->getMemory()[$key];
    }

    public function getStringFromMemory(string $key): string
    {
        $data = $this->getDataFromMemory($key);
        if (!\is_string($data)) {
            return '';
        }

        return $data;
    }

    public function getIntFromMemory(string $key): int
    {
        $data = $this->getDataFromMemory($key);
        if (!\is_int($data)) {
            return PHP_INT_MIN;
        }

        return $data;
    }
}
