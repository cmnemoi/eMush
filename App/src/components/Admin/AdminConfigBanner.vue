<template>
    <div class="banner">
        <div class="admin-menu">
            <router-link :to="{ name: 'AdminGameConfigList' }">Game Config</router-link>
            <router-link :to="{ name: 'AdminDaedalusConfigList' }">Daedalus Config</router-link>
            <router-link :to="{ name: 'AdminDifficultyConfigList' }">Difficulty Config</router-link>
            <router-link :to="{ name: 'AdminCharacterConfigList' }">Character Config</router-link>
            <router-link :to="{ name: 'AdminPlaceConfigList' }">Place Config</router-link>
            <router-link :to="{ name: 'AdminRandomItemPlacesList' }">Random Item Places</router-link>
            <router-link :to="{ name: 'AdminEquipmentConfigList' }">Equipment Config</router-link>
            <router-link :to="{ name: 'AdminMechanicsList' }">Mechanics</router-link>
        </div>
        <div class="admin-menu">
            <router-link :to="{ name: 'AdminDiseaseCauseConfigList' }">Disease Cause Config</router-link>
            <router-link :to="{ name: 'AdminDiseaseConfigList' }">Disease Config</router-link>
            <router-link :to="{ name: 'AdminConsumableDiseaseConfigList' }">Consumable Disease Config</router-link>
            <router-link :to="{ name: 'AdminModifierConfigList' }">Modifier Config</router-link>
        </div>
        <div class="admin-menu">
            <router-link :to="{ name: 'AdminStatusConfigList' }">Status Config</router-link>
            <router-link :to="{ name: 'AdminActionConfigList' }">Action Config</router-link>
            <router-link :to="{ name: 'AdminTriumphConfigList' }">Triumph Config</router-link>
            <router-link :to="{ name: 'AdminEventConfigList' }">Event Config</router-link>
            <router-link :to="{ name: 'AdminHunterConfigList' }">Hunter Config</router-link>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";


export default defineComponent ({
    name: 'AdminConfigBanner'
});
</script>

<style lang="scss" scoped>

.admin-menu {
    display: flex;
    flex-direction: row;

    span {
        margin: 0 1.4em;
        padding: .3em .6em;
        color: white;
        font-size: 1.1rem;
        font-weight: normal;
        letter-spacing: .06em;
        text-decoration: none;
    }

    a {
        margin: 0 1.4em;
        padding: .3em .6em;
        color: white;
        font-size: 1.1rem;
        font-weight: normal;
        letter-spacing: .06em;
        text-decoration: none;

        &:hover,
        &:active {
            color: #dffaff;
            text-shadow: 0 0 1px white, 0 0 1px white;
        }

        .unavailable {
            text-decoration: line-through;
            opacity: 0.6;
        }
    }
}

.banner {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.logo {
    height: 100%;
    margin: -0.05em 5em 0.1em 5em;
}

</style>
