<?php

namespace Mush\Daedalus\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Enum\NeronCpuPriorityEnum;

#[ORM\Entity]
class Neron
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\OneToOne(mappedBy: 'neron', targetEntity: DaedalusInfo::class)]
    private DaedalusInfo $daedalusInfo;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isInhibited = true;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => NeronCpuPriorityEnum::NONE])]
    private string $cpuPriority = NeronCpuPriorityEnum::NONE;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDaedalusInfo(): DaedalusInfo
    {
        return $this->daedalusInfo;
    }

    public function setDaedalusInfo(DaedalusInfo $daedalusInfo): self
    {
        $this->daedalusInfo = $daedalusInfo;

        return $this;
    }

    public function setIsInhibited(bool $isInhibited): self
    {
        $this->isInhibited = $isInhibited;

        return $this;
    }

    public function isInhibited(): bool
    {
        return $this->isInhibited;
    }

    public function setCpuPriority(string $cpuPriority): self
    {
        $this->cpuPriority = $cpuPriority;

        return $this;
    }

    public function getCpuPriority(): string
    {
        return $this->cpuPriority;
    }
}
