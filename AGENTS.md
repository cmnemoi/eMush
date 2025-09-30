# Mush Project Guidelines

## Overview
This document outlines the project-specific guidelines for the Mush project.

## Project Structure
The Mush project is a web-based game with a Symfony backend and Vue.js frontend:
- `Api/`: Symfony 6.4 backend following Clean Architecture principles
- `App/`: Vue.js 3 frontend following Feature-Based Architecture
- `docker/`: Docker configuration for development and deployment

## Backend Guidelines (PHP 8.4)

### Architecture
- Follow Clean Architecture principles with clear separation of layers:
  - Domain Layer: Business logic and entities
  - Application Layer: Use cases and orchestration
  - Infrastructure Layer: External systems integration
  - Presentation Layer: API controllers and responses
- Organize code by domain concepts (e.g., Player, Daedalus, Action)
- Each domain directory should contain:
  - Entity/: Domain entities
  - ValueObject/: Immutable value objects
  - Repository/: Data access interfaces and implementations
  - Service/: Domain services
  - UseCase/: Application use cases
  - Controller/: API endpoints
  - Event/: Domain events
  - Listener/: Event listeners
  - Enum/: Type definitions
  - Factory/: Object creation
  - Normalizer/: Response formatting

### Coding Standards
- Use strict typing with `declare(strict_types=1)`
- Type all properties, parameters, and return types
- Make classes `final` by default
- Use constructor property promotion
- Make immutable classes `readonly`
- Use PHP 8.1 backed enums for constants
- Follow PSR-4 autoloading standard
- Keep functions under 10 lines: Extract Until You Drop
- Limit files to 200 lines
- One responsibility per file
- Fail fast and throw errors early
- Use custom domain errors

### Naming Conventions
- Use PascalCase for classes, interfaces, traits, and namespace segments
- Use camelCase for methods, functions, properties, and variables
- Use UPPER_SNAKE_CASE for constants and enum cases
- Suffix interfaces with `Interface` and traits with `Trait`
- Prefix boolean-returning methods with `is`, `has`, or `should`
- Use verbs for action methods and nouns for value-returning methods
- Use plural for arrays/collections

### Testing
- Name test classes with `Test` suffix for unit tests, `Cest` for functional tests
- Follow `testShouldDoSomething` method naming
- Use Given-When-Then pattern in test structure
- Extract test steps into `givenXxx()`, `whenXxx()`, `thenXxx()`
- Use in-memory repositories over mocks
- Test behaviors, not implementation details
- Test both success and failure paths
- Keep tests independent and deterministic

## Frontend Guidelines (Vue.js 3)

### Architecture
- Follow Feature-Based Architecture
- Organize code by business domain
- Keep features self-contained
- Use smart/dumb component pattern:
  - Smart components handle data and logic
  - Dumb components display only, use interfaces

### Component Structure
- Use Composition API exclusively
- Keep components focused on a single responsibility
- Extract reusable logic into composables
- Type component props
- Avoid complex expressions in templates
- Extract complex logic to computed properties

### State Management (Vuex 4)
- Organize modules by domain feature
- Use namespaced modules only
- Define strong TypeScript interfaces
- Keep mutations synchronous only
- Handle all async operations in actions
- Isolate API calls in services

### Coding Standards
- Type everything explicitly
- Never use `any` or `unknown`
- Avoid `null` and `undefined` in returns
- Prefer string literal unions to enums
- Use descriptive type parameter names
- Follow ESLint and Prettier configurations

### Naming Conventions
- Use PascalCase for components and interfaces
- Use camelCase for functions, methods, and variables
- Use verbs for actions and nouns for value-returning functions
- Prefix booleans with is, has, should

## Development Workflow

### Test-Driven Development
1. Write a failing test for the feature
2. Write the minimum code to make the test pass
3. Refactor production code using Clean Code standards
4. Refactor tests using Clean Code and Testing standards
5. Repeat for the next feature

### Planning Process
1. Gather information and context about the task
2. Ask clarifying questions
3. Create a detailed plan
4. Discuss and refine the plan
5. Implement the solution

### Package Management
- Use Composer for PHP dependencies
- Use Yarn for JavaScript dependencies
- Check existing packages before proposing new ones
- Prefer stable versions
- Always ask before installing new packages

## Quality Assurance
- Run linters before committing code:
  - PHP CS Fixer for PHP code style
  - Psalm for PHP static analysis
  - PHPMD for PHP mess detection
  - ESLint for JavaScript/TypeScript
  - Stylelint for CSS/SCSS
- Write tests for all new features and bug fixes
- Run tests before committing code
- Follow the testing standards for each language

## Build & Test Commands
- **Test single module**: `docker exec -u dev mush-php php -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" vendor/bin/codecept run tests/functional/[module] && docker exec -u dev mush-php php -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" vendor/bin/codecept run tests/unit/[module]`
- **Run all tests**: `docker exec -u dev mush-php php -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" vendor/bin/codecept run`
- **Unit tests only**: `docker exec -u dev mush-php php -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" vendor/bin/codecept run tests/unit`
- **Functional tests**: `docker exec -u dev mush-php php -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" vendor/bin/codecept run tests/functional`
- **Lint backend**: `docker exec -u dev mush-php php -d error_reporting="E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED" vendor/bin/php-cs-fixer fix`
- **Lint frontend**: `docker exec -u node mush-front yarn lint`
- **Build frontend**: `docker exec -u node mush-front yarn build`

## Code Style & Structure
- **Architecture**: Domain-first modular structure in `Api/src/[ModuleName]/`
- **PHP**: Strict types (`declare(strict_types=1)`), final classes, readonly properties, constructor injection
- **Naming**: PascalCase classes, camelCase methods/properties, descriptive service names (e.g., `CreateLinkWithSolService`)
- **Services**: Single responsibility command pattern with `execute()` method
- **Tests**: Use InMemory repositories, Given-When-Then pattern, descriptive method names
- **Frontend**: Vue 3 + TypeScript, 4-space indentation, semicolons required, object spacing