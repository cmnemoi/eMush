<?php

namespace Mush\Action\Normalizer;

use Mush\Action\Actions\Action;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ActionNormalizer implements ContextAwareNormalizerInterface
{
    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Action;
    }

    /**
     * @param Action $action
     *
     * @return array
     */
    public function normalize($action, string $format = null, array $context = [])
    {
        if ($action->canExecute()) {
            $actionName=$action->getActionName();
            return [
                'key' => $actionName,
                'name' => $this->translator->trans("{$actionName}.name", [], 'actions'),
                'description' => $this->translator->trans("{$actionName}.description", [], 'actions'),
                'actionPointCost' => $action->getActionCost()->getActionPointCost(),
                'movementPointCost' => $action->getActionCost()->getMovementPointCost(),
                'moralPointCost' => $action->getActionCost()->getMoralPointCost(),
            ];
        }
        return [];
    }


}
