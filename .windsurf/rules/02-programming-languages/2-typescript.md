---
trigger: model_decision
description: APPLY TypeScript best practices WHEN writing TypeScript code
globs: **/*.ts, **/*/.tsx
---

Strict Types:
- Type everything explicitly
- Never use `any` or `unknown`
- Avoid `as` for type conversion
- Use type guards for assertions
- Use generics for reusable functions

Interfaces and Types:
- Use `interface` for extensible objects
- Use `type` for unions and primitives

Nullability:
- Avoid `null` and `undefined` in returns

Enumerations:
- Prefer string literal unions to enums
- Use const enums if needed
- Define enum values explicitly

Lint & Error:
- Catch errors as `unknown | Error`

Generics:
- Use descriptive type parameter names