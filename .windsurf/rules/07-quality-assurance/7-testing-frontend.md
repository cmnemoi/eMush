---
trigger: model_decision
description: Apply frontend testing standards for UI reliability and state management when writing frontend tests or modifying stores. Focus on test doubling boundaries, error handling, and state verification.
globs: apps/frontend/**/*.spec.*, apps/frontend/**/*.test.*, apps/frontend/**/tests/*, apps/frontend/**/__tests__/*
---

Test Doubles:
- Only external dependencies (API, browser) should be replaced by test doubles (fakes, stubs)
- Mocks only in absolutely necessary.

Error Handling:
- Test error throwing mechanisms.
- Avoid testing `console` output.

Components and State:
- Do not test presentational components.
- Test state stores thoroughly.
- Test container/smart components.
- Assert component/application state changes.