<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ContentStatus extends Status
{
    #[ORM\Column(type: 'text', nullable: false, options: ['default' => ''])]
    private string $content = '';

    public function getContent(): string
    {
        return $this->content === '' ? 'empty' : $this->content;
    }

    /**
     * @return static
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
