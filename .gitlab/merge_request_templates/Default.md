# What changed ?

- feature 1
- feature 2
- bug fix 1

# Why did it change ?

- feature 1 was added because
- feature 2 was added because
- bug fix 1 was triggered by X and provoked Y

Closes #issue
 
# How did it change ?

%{all_commits}

# Checklist:

- [ ] `composer lint` does not fail with my changes
- [ ] I have performed a self-review of my code
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing tests pass locally with my changes
- [ ] I have launched the game locally to check for any uncaught bugs
- [ ] I have deployed this branch commit on staging server successfully [(here)](https://gitlab.com/eternaltwin/config)
- [ ] I have added a default value through Doctrine annotations for my new entity attributes [(example)](https://gitlab.com/eternaltwin/mush/mush/-/blob/develop/Api/src/Action/Entity/ActionConfig.php?ref_type=heads#L35)
- [ ] I have added my new Status/Equipment/Project config in [`GameConfigData`](https://gitlab.com/eternaltwin/mush/mush/-/blob/develop/Api/src/Game/ConfigData/GameConfigData.php?ref_type=heads#L25)

/milestone %Backlog
/label ~Bug