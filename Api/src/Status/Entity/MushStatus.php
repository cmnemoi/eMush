<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Status\Enum\PlayerStatusEnum;


/**
 * Class ChargeStatus.
 *
 * @ORM\Entity
 */
class MushStatus extends Status
{
    protected ?string $name = PlayerStatusEnum::MUSH;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $spores = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $ = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $threshold = null;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $autoRemove = false;

    

    public function isAutoRemove(): bool
    {
        return $this->autoRemove;
    }

    public function setAutoRemove(bool $autoRemove): MushStatus
    {
        $this->autoRemove = $autoRemove;

        return $this;
    }
}
