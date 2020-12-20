<?php

namespace Mush\Player\Normalizer;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTargetEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class OtherPlayerNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TokenStorageInterface $tokenStorage;
    private ActionServiceInterface $actionService;
    private TranslatorInterface $translator;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ActionServiceInterface $actionService,
        TranslatorInterface $translator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->actionService = $actionService;
        $this->translator = $translator;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Player && $data !== $this->getUser()->getCurrentGame();
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        $player = $object;
        $statuses = [];
        foreach ($player->getStatuses() as $status) {
            $normedStatus = $this->normalizer->normalize($status, null, ['player' => $player]);
            if (count($normedStatus) > 0) {
                $statuses[] = $normedStatus;
            }
        }

        //Handle tools
        $tools = $this->getUser()->getCurrentGame()->getReachableTools()
            ->filter(function (GameEquipment $gameEquipment) {
                /** @var Tool $tool */
                $tool = $gameEquipment->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL);

                return !$tool->getGrantActions()->isEmpty();
            })
        ;

        $playerActions = ActionEnum::getPermanentPlayerActions();
        $actions = [];

        foreach ($tools as $tool) {
            $toolActions = $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getActionsTarget();

            $toolSelfActions = $tool->getEquipment()
                ->getMechanicByName(EquipmentMechanicEnum::TOOL)
                ->getGrantActions()
                ->filter(
                    fn (string $actionName) => $toolActions[$actionName] === ActionTargetEnum::TARGET_PLAYER
                );

            foreach ($toolSelfActions as $toolSelfAction) {
                $playerActions[] = $toolSelfAction;
            }
        }

        foreach ($playerActions as $actionName) {
            $actionClass = $this->actionService->getAction($actionName);
            if ($actionClass) {
                $normedAction = $this->normalizer->normalize($actionClass, $format, ['player' => $player]);
                if (count($normedAction) > 0) {
                    $actions[] = $normedAction;
                }
            }
        }

        return [
            'id' => $player->getId(),
            'character' => [
                'key' => $player->getPerson(),
                'value' => $this->translator->trans($player->getPerson() . '.name', [], 'characters'),
            ],
            'statuses' => $statuses,
            'skills' => $player->getSkills(),
            'actions' => $actions,
        ];
    }

    private function getUser(): User
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        return $user;
    }
}
