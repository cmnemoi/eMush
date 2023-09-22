<?php

namespace Mush\Tests\unit\User\Validator;

use Mockery;
use Mush\User\Enum\RoleEnum;
use Mush\User\Validator\CanGrantRole;
use Mush\User\Validator\CanGrantRoleValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class CanGrantRoleTest extends TestCase
{
    /** @var RoleHierarchyInterface|Mockery\MockInterface */
    private RoleHierarchyInterface $roleHierarchy;
    /** @var TokenStorageInterface|Mockery\MockInterface */
    private TokenStorageInterface $tokenStorage;

    private CanGrantRoleValidator $validator;

    protected function setUp(): void
    {
        $this->roleHierarchy = \Mockery::mock(RoleHierarchyInterface::class);
        $this->tokenStorage = \Mockery::mock(TokenStorageInterface::class);

        $this->tokenStorage
            ->shouldReceive('getToken')
            ->andReturn(null)
            ->byDefault()
        ;

        $this->validator = new CanGrantRoleValidator(
            $this->roleHierarchy,
            $this->tokenStorage
        );
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testValid()
    {
        $constraint = new CanGrantRole();

        $this->initValidator();

        $roles = [RoleEnum::ADMIN];
        $this->roleHierarchy
            ->shouldReceive('getReachableRoleNames')
            ->andReturn([RoleEnum::MODERATOR, RoleEnum::ADMIN])
            ->once()
        ;

        $this->validator->validate($roles, $constraint);
    }

    public function testNotValid()
    {
        $constraint = new CanGrantRole();
        $this->initValidator('You cannot grant this role');

        $roles = [RoleEnum::ADMIN];
        $this->roleHierarchy
            ->shouldReceive('getReachableRoleNames')
            ->andReturn([RoleEnum::USER, RoleEnum::MODERATOR])
            ->once()
        ;

        $this->validator->validate($roles, $constraint);
    }

    protected function initValidator(string $expectedMessage = null)
    {
        $builder = \Mockery::mock(ConstraintViolationBuilder::class);
        $context = \Mockery::mock(ExecutionContext::class);

        if ($expectedMessage) {
            $builder->shouldReceive('addViolation')->andReturn($builder)->once();
            $builder->shouldReceive('setCode')->andReturn($builder)->once();
            $context->shouldReceive('buildViolation')->with($expectedMessage)->andReturn($builder)->once();
        } else {
            $context->shouldReceive('buildViolation')->never();
        }

        /* @var ExecutionContext $context */
        $this->validator->initialize($context);

        return $this->validator;
    }
}
