---
trigger: model_decision
description: APPLY general testing standards for clarity and consistency WHEN writing any test. Covers organization, naming, and core principles like Given/When/Then.
globs: apps/**/*.spec.*, apps/**/*.test.*
---

Organization:
- Organize tests by domain/feature in appropriate directory structure
- Group related tests by behavior using descriptive test methods

Naming:
- Name test methods with `should` prefix describing behavior
- Use descriptive names for test helper methods
- Follow Given-When-Then pattern in method names

Test Structure:
- Extract test steps into descriptive methods
- Use `givenXxx()` for test setup
- Use `whenXxx()` for executing behavior
- Use `thenXxx()` for assertions
- Keep assertions focused and minimal

Test Data:
- Use in-memory repositories over mocks
- Create test data in setup methods
- Use factories for test entities

Test Quality:
- Test behaviors not implementation details
- Test both success and failure paths
- Keep tests independent and deterministic
- Test edge cases and error conditions