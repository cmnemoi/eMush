---
trigger: model_decision
description: APPLY integration testing standards to verify interactions between components/services WHEN writing integration tests.
globs: apps/**/*.integration.*
---

Test Structure:
- Name test methods using `should` prefix
- Split test setup into `given` methods
- Split test actions into `when` methods
- Split assertions into `then` methods
- Group related test scenarios in same class

Test Setup:
- Use dependency injection for services
- Initialize test entities in setup
- Store test entities as class properties

Test Scenarios:
- Test one scenario per method
- Use data providers for similar scenarios
- Test edge cases and error conditions
- Test business rules independently

Assertions:
- Assert entity state changes according to business rules
- Assert error messages
- Assert visibility / execution conditions