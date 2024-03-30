<template>
    <div class="banner">
        <button class="menu-toggle" @click="show = !show"><img :src="getImgUrl('comms/newtab.png')"></button>
        <div v-if="show" class="admin-menu">
            <router-link :to="{ name: 'AdminGameConfigList' }">Game Config</router-link>
            <router-link :to="{ name: 'AdminDaedalusConfigList' }">Daedalus Config</router-link>
            <router-link :to="{ name: 'AdminDifficultyConfigList' }">Difficulty Config</router-link>
            <router-link :to="{ name: 'AdminCharacterConfigList' }">Character Config</router-link>
            <router-link :to="{ name: 'AdminPlaceConfigList' }">Place Config</router-link>
            <router-link :to="{ name: 'AdminRandomItemPlacesList' }">Random Item Places</router-link>
            <router-link :to="{ name: 'AdminEquipmentConfigList' }">Equipment Config</router-link>
            <router-link :to="{ name: 'AdminMechanicsList' }">Mechanics</router-link>

            <router-link :to="{ name: 'AdminDiseaseCauseConfigList' }">Disease Cause Config</router-link>
            <router-link :to="{ name: 'AdminDiseaseConfigList' }">Disease Config</router-link>
            <router-link :to="{ name: 'AdminConsumableDiseaseConfigList' }">Consumable Disease Config</router-link>
            <router-link :to="{ name: 'AdminModifierConfigList' }">Modifier Config</router-link>

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
import { getImgUrl } from "@/utils/getImgUrl";


export default defineComponent ({
    name: 'AdminConfigBanner',
    data() {
        return {
            show: false
        };
    },
    mounted() {
        this.handleResize();
        window.addEventListener('resize', this.handleResize);
    },
    unmounted() {
        window.removeEventListener('resize', this.handleResize);
    },
    methods: {
        getImgUrl,
        handleResize() {
            this.show = window.matchMedia('(max-width: 768px)').matches ? false : true;
        }
    }
});
</script>


<style lang="scss" scoped>

.banner {
    // position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-right: 1.2em;
}

.menu-toggle {
    display: none;
    padding: 0.8em;
    background: $greyBlue;
    border-radius: 50%;
}

.admin-menu {
    margin-top: 1.4em;
    min-width: 255px;
    border-radius: 4px;

    @extend %game-scrollbar;
}

.admin-menu span, .admin-menu a {
    padding: 0.6em 0.8em;
    color: white;
    font-size: 1.1rem;
    font-weight: normal;
    letter-spacing: .03em;
    text-decoration: none;
    border-bottom: 1px solid transparentize(white, 0.8);


    &:hover,
    &:active {
        color: #dffaff;
        text-shadow: 0 0 1px white, 0 0 1px white;
    }

    &.router-link-active { font-weight: bold; }

    .unavailable {
        text-decoration: line-through;
        opacity: 0.6;
    }
}

.admin-menu > * {
    border-bottom: 1px solid transparentize(white, 0.8);
}

.logo {
    height: 100%;
    margin: -0.05em 5em 0.1em 5em;
}

@media only screen and (max-width: $breakpoint-desktop-m) {
    .menu-toggle { display: flex; }

    // .banner { min-height: 35em; }
    .admin-menu {
        position: absolute;
        z-index: 5;
        top: 2.8em;
        left: 2.8em;
        margin-top: 0;
        max-height: calc(100% - 3.5em);
        background: $slightlyDeepBlue;
        box-shadow: 0 0 14px $deepBlue;
        overflow-y: auto;
    }
}

</style>
