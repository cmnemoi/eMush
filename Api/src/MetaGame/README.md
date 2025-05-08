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

# How to add new config on admin pages?

1. Expose the new config you want in admin panel with **API Platform**.
  
    a. Create an endpoint from which will recover your config data by creating a YAML file in `Api/config/api_platform/resources`. You may also define endpoint to access sub-resources. 
    Example : you can recover all equipment configs by calling the `/api/v1/equipment_configs/` URL / endpoint. 
    You can recover the actions provided by a piece of equipment by calling the `/api/v1/equipment_configs/{id}/action_configs` URL / endpoint, as a sub-resource.
    
    b. Choose the attributes you want to see exposed by creating a YAML file in `Api/config/api_platform/serialization`. Usually, it's all of them.
    
    c. Add some validation rules for your config by creating a YAML file in `Api/config/api_platform/validation`. Example : a `ProjectConfig` `efficiency` should be between 1 and 99.

2. Create a new front-end entity in `App/src/entities/<your_config>.ts` for your config.

3. Create a  `<your_config>.service.ts` in `App/src/services` which contains functions to create, update and load your config data. Please take inspiration from `App/src/services/hunter.config.service.ts`

4. Create new Vue components with the user interfaces to list, edit and create your config data in `App/src/components/Admin/Config/<your_config>List.vue` and `App/src/components/Admin/Config/<your_config>Detail.vue`. You probably want to update the banner in `App/src/components/Admin/AdminConfigBanner.vue` and configure the routing in `App/src/router/adminConfigPages.ts` so your pages are accessible.

5. Use your `<your_config>.service.ts` to actually fetch your config data in your newly created interface. Please take inspiration from already existing Vue components in `App/src/components/Admin/Config`

See this MR for API Platform endpoint creation for HunterConfig : https://gitlab.com/eternaltwin/mush/mush/-/merge_requests/1186

And this MR for its exposure in admin panel : https://gitlab.com/eternaltwin/mush/mush/-/merge_requests/1188

