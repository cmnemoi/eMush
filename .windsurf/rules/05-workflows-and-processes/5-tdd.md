---
trigger: model_decision
description: APPLY TDD WHEN writing developping a feature, fixing a bug or refactoring code
---

# How to work in TDD

0) Determine the next feature to implement
1) Write a failing test for the feature in `tests`
2) Run tests
3) If the test fails, go to 4), else go to 1)
4) Write the minimum amount of production code to make the test passes
5) Run tests
6) If the test passes, go to 7), else go to 4)
7) Refactor production code using Clean Code standards
8) Run tests
9) If the test passes, go to 10), else go to 4)
10) Refactor tests using Clean Code and Testing standards
11) If the test passes, go to 0), else revert and go to 10)