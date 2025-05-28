---
trigger: model_decision
description: APPLY Symfony 6.4 framework standards WHEN developing backend applications to ensure maintainability, performance, and adherence to best practices
globs: Api/src/**/*.php
---

Architecture:
- Use Domain-Driven Design
- Organize by bounded contexts
- Keep controllers thin
- Separate interfaces from implementations

Controllers:
- Extend AbstractFOSRestController
- Use attributes for routing
- Return View objects
- Validate permissions first
- Call services, not repositories
- Use DTO for input/output

Entities:
- Use ORM attributes
- Implement domain behavior
- Make entities immutable when possible
- Use value objects for complex values
- Keep getters/setters minimal : Tell, Don't Ask

Repositories:
- Extend ServiceEntityRepository
- Implement repository interfaces
- Use typed repository annotations
- Return domain objects
- Throw exceptions for not found
- Hydrate manually aggregates references when needed

Services:
- Make services final and readonly
- Use constructor injection
- Expose a single `execute` public method
- Keep methods small and focused
- Return void for commands
- Return values for queries

Event Subscribers:
- Implement EventSubscriberInterface
- Define clear event priorities
- Keep handlers focused on single responsibility
- Use dependency injection

Normalizers:
- Implement NormalizerInterface
- Define supported types
- Handle translations
- Return structured arrays
- Extract complex logic to private methods

Value Objects:
- Make immutable
- Validate in constructor
- Use factory methods
- Implement toString for serialization

Error Handling:
- Throw domain-specific exceptions
- Use custom exception classes
- Validate early
- Provide clear error messages

Testing:
- Use Codeception
- Replace dependencies by test doubles (fakes, stubs) 
- Test edge cases
- Use data providers for variations
- Use Factories to facilitate setup