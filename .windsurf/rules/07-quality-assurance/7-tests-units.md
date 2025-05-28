---
trigger: model_decision
description: APPLY unit testing standards to verify behavior of individual classes and services WHEN writing unit tests. Focus on Given-When-Then pattern, test doubles, and comprehensive test cases.
globs: Api/tests/unit/**/*Test.php
---

Test Structure:
- Use Given-When-Then pattern in test method names and comments
- One test method per scenario
- Test methods should be final
- Group test cases using data providers

Test Doubles:
- Use fakes in memory repositories for persistence layer
- Use spies for event dispatching verification
- Use stubs for simple dependencies

Test Coverage:
- Test success and failure scenarios
- Test edge cases with data providers
- Verify state changes and event dispatching

Test Setup:
- Use setUp for common test objects
- Create test data in private given* methods
- Create assertions in private then* methods

Naming:
- Test class name: matches class under test + Test suffix
- Test method name: testShould* format
- Given method name: given* format
- When method name: when* format
- Then method name: then* format