<?php

namespace Mush\Daedalus\Entity;

use Doctrine\ORM\Mapping as ORM;

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
}
