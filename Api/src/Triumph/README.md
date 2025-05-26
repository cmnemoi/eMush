# Triumph

# How to add a new triumph ?

- Add the triumph config to [TriumphConfigData](./ConfigData/TriumphConfigData.php)
- If the triumph listens to a new event, it should :
    - implement [TriumphSourceEventInterface](./Event/TriumphSourceEventInterface.php)
    - be added to [TriumphSourceEventSubscriber](./Listener/TriumphSourceEventSubscriber.php)
- Add the triumph log in [triumph+intl-icu.fr.xlf](./translations/fr/triumph+intl-icu.fr.xlf)

