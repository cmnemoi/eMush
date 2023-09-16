<?php

namespace Mush\Action\Entity\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ActionRequest
{
    /**
     * @Assert\NotNull
     *
     * @Assert\Type(type="integer")
     */
    private int $action;

    private ?array $params = [];

    public function getAction(): int
    {
        return $this->action;
    }

    public function setAction(int $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getParams(): ?array
    {
        return $this->params;
    }

    public function setParams(?array $params): self
    {
        $this->params = $params;

        return $this;
    }
}
