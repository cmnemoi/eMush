<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;

#[ORM\Entity]
class NeronVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'integer', length: 255, nullable: false, options: ['default' => 1])]
    private int $major;

    #[ORM\Column(type: 'integer', length: 255, nullable: false, options: ['default' => 0])]
    private int $minor;

    private int $daedalusId;

    #[ORM\OneToOne(targetEntity: Daedalus::class)]
    #[ORM\JoinColumn(name: 'daedalus_id')]
    private Daedalus $daedalus;

    public function __construct(int $daedalusId, int $major = 1, int $minor = 0)
    {
        if ($major < 1) {
            throw new \InvalidArgumentException('Major version must be greater than 0');
        }
        if ($minor < 0 || $minor >= 100) {
            throw new \InvalidArgumentException('Minor version must be between 0 and 99');
        }

        $this->major = $major;
        $this->minor = $minor;
        $this->daedalusId = $daedalusId;
    }

    public function getMajor(): int
    {
        return $this->major;
    }

    public function getMinor(): int
    {
        return $this->minor;
    }

    public function getDaedalusId(): int
    {
        return $this->daedalusId;
    }

    public function increment(int $minorIncrement): void
    {
        if ($minorIncrement <= 0) {
            throw new \InvalidArgumentException('The minor increment cannot be negative');
        }

        $this->minor += $minorIncrement;

        if ($this->minor >= 100) {
            ++$this->major;
            $this->minor = 0;
        }
    }

    public function majorHasBeenUpdated(): bool
    {
        return $this->minor === 0;
    }

    public function toString(): string
    {
        return \sprintf('%d.%02d', $this->major, $this->minor);
    }

    /**
     * @deprecated should be used only in Doctrine repositories
     */
    public function setDaedalusId(int $daedalusId): void
    {
        $this->daedalusId = $daedalusId;
    }

    /**
     * @deprecated Should be used only in Doctrine repositories. Use getDaedalusId() instead.
     */
    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    /**
     * @deprecated should be used only in Doctrine repositories
     */
    public function setDaedalus(Daedalus $daedalus): void
    {
        $this->daedalus = $daedalus;
    }
}
