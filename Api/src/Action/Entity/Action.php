<?php

namespace Mush\Action\Entity;

class Action
{
    private ActionConfig $actionConfig;
    private ActionProviderInterface $actionProvider;

    public function setActionConfig(ActionConfig $actionConfig): self
    {
        $this->actionConfig = $actionConfig;

        return $this;
    }

    public function getActionConfig(): ActionConfig
    {
        return $this->actionConfig;
    }

    public function setActionProvider(ActionProviderInterface $actionProvider): self
    {
        $this->actionProvider = $actionProvider;

        return $this;
    }

    public function getActionProvider(): ActionProviderInterface
    {
        return $this->actionProvider;
    }
}
