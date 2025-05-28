---
trigger: model_decision
description: APPLY PHP 8 best practices WHEN writing PHP code to ensure type safety, readability, and maintainability
globs: **/*.php
---

Strict Types:
- Always use `declare(strict_types=1)`
- Type all properties, parameters, and return types
- Use union types instead of docblocks
- Use nullable types with `?` prefix

Classes and Functions:
- Make classes `final` by default
- Use constructor property promotion
- Make immutable classes `readonly`
- Use named arguments for clarity
- Use attribute validation over manual checks

Nullability:
- Return early to avoid nested conditionals
- Use null coalescing operator `??` over ternary
- Use null safe operator `?->` for method chains
- Throw exceptions instead of returning null

Enumerations:
- Use native PHP 8.1 backed enums for constants
- Access enum cases with `::` not string values

Error Handling:
- Use typed exceptions for domain errors
- Catch specific exceptions, not generic `Exception`
- Use match expressions over switch statements
- Throw early, catch late

Interfaces and Traits:
- Suffix interfaces with `Interface`

Modern Features:
- Use named arguments for clarity
- Use spread operator for array merging
- Use first-class callable syntax
- Use array unpacking for destructuring