# Contributing

When contributing to this repository, please first discuss the change you wish to make via [issue](https://gitlab.com/eternaltwin/mush/mush/-/issues/?sort=updated_desc&state=opened&first_page_size=100),
or [discord](https://discord.com/channels/693082011484684348) before making a change. 

Please note we have a code of conduct, please follow it in all your interactions with the project.

## Pull Request Process

1- Please make any changes on a local branch from the development branch, and prefix the branch name by feat for a new feature and fix for a fix:
```
git checkout -b feat-some-feature
```
Or for a fix
```
git checkout -b fix-some-fix
```
2- Verify coding style before creating the merge request with `composer lint`

3- Merge go through a pipeline that check unit test and syntax. Be sure to run following commands before merging:
```
composer lint
composer test
```
In case of trouble when running tests, run following commands:
```
composer codecept:clean
composer psalm:clear-cache
```
Test should cover at least 70 % of the lines. To check test coverage run:
```
XDEBUG_MODE=coverage php vendor/bin/codecept run  --coverage
```

4- Issue a Merge Request describing summarizing what you have done, wait a moment (ideally 24h) or the approval of another developer before merging

eMush uses [semantic-release](https://semantic-release.gitbook.io/semantic-release/) to generate changelogs and versioning.
Therefore, your Merge Request title must follow the [Conventional commits convention](https://www.conventionalcommits.org/en/v1.0.0/#summary).

In short, your MR title must be prefixed by one of the following to appear in the changelog:
- feat: A new feature
- fix: A bug fix

You can also use the following prefixes. They will not appear in the changelog, so they are only for informative purposes:
- docs: Documentation only changes
- style: Changes that do not affect the meaning of the code (white-space, formatting, missing semi-colons, etc)
- refactor: A code change that neither fixes a bug nor adds a feature
- test: Adding missing tests or correcting existing tests
- ci: Changes to our CI configuration files and scripts
- internal: Changes that do not affect the users. Use this for bugs that are in production, or internal tools (admin panel, composer commands etc.)

5- We advise new back-end developers to add an [action](./Api/src/Action/README.md). Do not hesitate to read the [API README](./src/Api/README.md).

## Code of Conduct

### Our Pledge

In the interest of fostering an open and welcoming environment, we as
contributors and maintainers pledge to making participation in our project and
our community a harassment-free experience for everyone, regardless of age, body
size, disability, ethnicity, gender identity and expression, level of experience,
nationality, personal appearance, race, religion, or sexual identity and
orientation.

### Our Standards

Examples of behavior that contributes to creating a positive environment
include:

* Using welcoming and inclusive language
* Being respectful of differing viewpoints and experiences
* Gracefully accepting constructive criticism
* Focusing on what is best for the community
* Showing empathy towards other community members

Examples of unacceptable behavior by participants include:

* The use of sexualized language or imagery and unwelcome sexual attention or
advances
* Trolling, insulting/derogatory comments, and personal or political attacks
* Public or private harassment
* Publishing others' private information, such as a physical or electronic
  address, without explicit permission
* Other conduct which could reasonably be considered inappropriate in a
  professional setting

### Our Responsibilities

Project maintainers are responsible for clarifying the standards of acceptable
behavior and are expected to take appropriate and fair corrective action in
response to any instances of unacceptable behavior.

Project maintainers have the right and responsibility to remove, edit, or
reject comments, commits, code, wiki edits, issues, and other contributions
that are not aligned to this Code of Conduct, or to ban temporarily or
permanently any contributor for other behaviors that they deem inappropriate,
threatening, offensive, or harmful.

### Scope

This Code of Conduct applies both within project spaces and in public spaces
when an individual is representing the project or its community. Examples of
representing a project or community include using an official project e-mail
address, posting via an official social media account, or acting as an appointed
representative at an online or offline event. Representation of a project may be
further defined and clarified by project maintainers.

### Enforcement

Instances of abusive, harassing, or otherwise unacceptable behavior may be
reported by contacting the project team at `contact<@>eternaltwin.org`. All
complaints will be reviewed and investigated and will result in a response that
is deemed necessary and appropriate to the circumstances. The project team is
obligated to maintain confidentiality with regard to the reporter of an incident.
Further details of specific enforcement policies may be posted separately.

Project maintainers who do not follow or enforce the Code of Conduct in good
faith may face temporary or permanent repercussions as determined by other
members of the project's leadership.

### Attribution

This Code of Conduct is adapted from the [Contributor Covenant][homepage], version 1.4,
available at [http://contributor-covenant.org/version/1/4][version]

[homepage]: http://contributor-covenant.org
[version]: http://contributor-covenant.org/version/1/4/
