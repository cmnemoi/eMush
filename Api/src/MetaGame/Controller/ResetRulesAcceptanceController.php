<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use Mush\MetaGame\UseCase\ResetRulesAcceptanceForAllUsersUseCase;

/**
 * Class for actions that can be performed by admins.
 *
 * @Route(path="/admin/actions/reset-rules-acceptance")
 */
final class ResetRulesAcceptanceController
{
    public function __construct(
        private readonly ResetRulesAcceptanceForAllUsersUseCase $resetRulesAcceptanceForAllUsersUseCase
    ) {
        $this->resetRulesAcceptanceForAllUsersUseCase = $resetRulesAcceptanceForAllUsersUseCase;
    }

    /**
     * Reset rules acceptance for all users.
     *
     * @OA\Tag(name="Admin")
     *
     * @Security(name="Bearer")
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @Rest\Put(path="/")
     */
    public function resetRulesAcceptanceEndpoint(): void
    {
        $this->resetRulesAcceptanceForAllUsersUseCase->execute();
    }
}
