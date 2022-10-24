<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ContentStatus extends Status
{
    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $content = null;

    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @return static
     */
    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
