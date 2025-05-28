---
trigger: model_decision
description: Apply PHP backend testing standards to ensure reliable, maintainable test suites that focus on behavior verification and proper test isolation WHEN writing tests for PHP backend
globs: ["Api/tests/unit/**/*Test.php", "Api/tests/functional/**/*Cest.php"]
---

Test Organization:
- Name test classes with `Test` suffix for unit tests, `Cest` for functional tests
- Follow `test[Should|Can|Will]DoSomething` method naming
- Place tests in matching domain structure
- Group related tests by behavior
- Test real behaviors, not implementation details

Test Structure:
- Use `Given-When-Then` pattern in method names
- Extract test steps into `givenXxx()`, `whenXxx()`, `thenXxx()`
- Setup common test data in `setUp()` (Codeception) or `_before()` (PHPUnit)
- Keep tests independent and idempotent
- Test both success and failure paths

Test Data:
- Use in-memory repositories over mocks
- Create test data in setup methods
- Use factories for test entities
- Inject dependencies via constructor
- Reset state between tests via setup methods

Assertions:
- Assert one behavior per test
- Use descriptive assertion methods
- Keep assertions minimal and focused
- Verify outcomes, not implementation
- Test edge cases and error paths

Test Doubles:
- Only external dependencies should be replace by test doubles, by implementing interfaces
- Prefer stubs and fakes to replace dependencies
- Use spies to verify interactions only if necessary
