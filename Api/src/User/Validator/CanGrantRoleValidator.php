<?php

namespace Mush\User\Validator;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CanGrantRoleValidator extends ConstraintValidator
{
    private RoleHierarchyInterface $roleHierarchy;
    private TokenStorageInterface $tokenStorage;

    public function __construct(RoleHierarchyInterface $roleHierarchy, TokenStorageInterface $tokenStorage)
    {
        $this->roleHierarchy = $roleHierarchy;
        $this->tokenStorage = $tokenStorage;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!\is_array($value)) {
            throw new \InvalidArgumentException();
        }

        if (!$constraint instanceof CanGrantRole) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\CanGrantRole');
        }

        $loggedInUserRoles = $this->tokenStorage->getToken()?->getRoleNames() ?? [];
        $reachableRoles = $this->roleHierarchy->getReachableRoleNames($loggedInUserRoles);

        foreach ($value as $role) {
            if (!\in_array($role, $reachableRoles, true)) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->setCode(CanGrantRole::CANNOT_GRANT_ROLE)
                    ->addViolation();

                return;
            }
        }
    }
}
