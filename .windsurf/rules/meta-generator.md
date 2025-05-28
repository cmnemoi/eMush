---
trigger: model_decision
description: Rules Generator
globs: .cursor/rules/**
---

# Rule Template

```mdc
---
description: to_generate
globs: to_generate
alwaysApply: to_generate
---

Sub rule 1:
- Rule 1
- Rule 2

...
```

## Steps

- Between each steps wait for user approval.

### 1. Structure

- Check if this is an existing rule to update under all .cursor/rules sub-folders

#### File name format
```text
#-rule-name[@version][-specificity].mdc
```
#### Directory structure
- Write in .cursor/rules/{folder}/{rule}.mdc

{folder}:
- `00-architecture`
- `01-standards`
- `02-programming-languages`
- `03-frameworks-and-libraries`
- `04-tools-and-configurations`
- `05-workflows-and-processes`
- `06-templates-and-models`
- `07-quality-assurance`
- `08-domain-specific-rules`
- `09-other`

### 2. frontmatter header

#### Generate `description`

One line. Comprehensive description that provides full context and clearly indicates when this rule should be applied. Include key scenarios, impacted areas, and why following this rule is important. While being thorough, remain focused and relevant. The description should be detailed enough that the agent can confidently determine whether to apply the rule in any given situation.

#### Generate `globs`

- Appropriate extension language based (e.g. `*.tsx` for React)
- Appropriate sub-folder: can be `features`, `controller` etc
- Appropriate repo: `apps/frontend`, `apps/backend`

> Example for React: `globs: apps/frontend/**/*.tsx`

#### Generate `alwaysApply`
- Mostly false, double check if this REALLY needs to be applied globally

### 3. Rule content

#### Groups (optional)
- Not needed for short rules content
- Identify rule groups
- Wait for user validation
- No MD titles, use "Group's name :"

#### Generate/Change Rules content
- Backticks for code ref
- Bullet points only
- Translate example into generic rule format
- Remove non-essential, no fluff
- Write commands only
- 1 ultra short (3–7 words) rule per bullet point

### 4. Filled template
- Write proper filled template in proper dir WITH frontmatter header
