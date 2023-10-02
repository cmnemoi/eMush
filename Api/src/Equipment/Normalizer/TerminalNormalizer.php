<?php

declare(strict_types=1);

namespace Mush\Equipment\Normalizer;

use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TerminalNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;

    public function __construct(
        TranslationServiceInterface $translationService
    ) {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof GameEquipment;
    }

    public function normalize(mixed $object, string $format = null, array $context = [])
    {   

        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        /** @var ?GameEquipment $terminal */
        $terminal = $object;

        $context['terminal'] = $terminal;

        if ($terminal === null) {
            return [];
        }

        if ($currentPlayer->getFocusedTerminal() !== $terminal) {
            return [];
        }

        $daedalus = $currentPlayer->getDaedalus();
        $terminalKey = $terminal->getName();

        return [
            'id' => $terminal->getId(),
            'key' => $terminalKey,
            'name' => $this->translationService->translate(
                $terminalKey . '.name',
                [],
                'terminal',
                $daedalus->getLanguage()
            ),
            'tips' => $this->translationService->translate(
                $terminalKey . '.tips',
                [],
                'terminal',
                $daedalus->getLanguage()
            ),
            'actions' => $this->getActions($terminal, $format, $context),
        ];
    }

    private function getActions(GameEquipment $terminal, ?string $format, array $context = []): array
    {
        $actions = $terminal->getActions()->filter(fn (Action $action) => $action->getScope() === ActionScopeEnum::TERMINAL);

        $normalizedActions = [];
        /** @var Action $action */
        foreach ($actions as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, $context);
            if (is_array($normedAction) && count($normedAction) > 0) {
                $normalizedActions[] = $normedAction;
            }
        }

        return $normalizedActions;
    }
}
