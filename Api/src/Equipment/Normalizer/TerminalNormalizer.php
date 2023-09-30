<?php

declare(strict_types=1);

namespace Mush\Equipment\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
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

        if ($terminal === null) {
            return [];
        }

        if ($currentPlayer->getCurrentlyFocusedTerminal() !== $terminal) {
            return [];
        }

        $daedalus = $currentPlayer->getDaedalus();
        $terminalKey = $terminal->getName();

        $context = $this->setupNormalizerContext($terminal, $context);

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
            'actions' => $this->getActions($currentPlayer, $terminal, $format, $context),
        ];
    }

    private function getActions(Player $player, GameEquipment $terminal, ?string $format, array $context = []): array
    {
        $actions = new ArrayCollection();
        $focusedStatus = $player->getStatusByName(PlayerStatusEnum::FOCUSED);
        if ($focusedStatus?->getTarget() === $terminal) {
            $actions = $terminal->getActions()->filter(fn (Action $action) => $action->getScope() === ActionScopeEnum::TERMINAL);
        }

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

    private function setupNormalizerContext(GameEquipment $terminal, array $context): array
    {
        $context['terminal'] = $terminal;

        // add specific terminal action parameters to context
        foreach (EquipmentEnum::$terminalActionParametersMap[$terminal->getName()] as $actionParameter) {
            if (!array_key_exists($actionParameter, $context)) {
                $context[$actionParameter] = null;
            }
        }

        return $context;
    }
}
