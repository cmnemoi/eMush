# Game
This module handles all the features outside a ship : News, moderation, and admin actions.

# Architecture 

## Directory Tree:
    |-- config
    |-- Controller
    |-- Entity
    |-- Service

# Maintenance mode

Admins can put the game in maintenance mode. 

This will return a 503 error to all requests for non-admin users and display a specific page :

![Maintenance mode](https://gitlab.com/eternaltwin/mush/mush/uploads/db4375659dba5c7161902dfe5fedb8c1/Screenshot_2023-10-14_at_19-47-07_Mush_-_Humanity_s_last_ship_lost_in_space..._with_a_traitor_aboard_.png)

Adminstrators can toggle the maintenance mode from the admin panel :

![Maintenance mode](/uploads/5ab40d54ed47c9c4d818cc43b642371a/Screenshot_2023-10-14_at_19-58-11_Mush_-_Jeu_de_survie_dans_l_espace_Vous_êtes_le_seul_espoir_de_l_humanité__.png)

Internally, this works by the `AdminService` creating and deleting a file named `maintenance` in the `var` directory. If this file exists, the game is in maintenance mode. The front end then displays the maintenance page if the API returns a 503 error.

