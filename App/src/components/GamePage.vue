<template>
    <div v-if="loggedIn">
        <div v-if="getUserInfo && getUserInfo.currentGame !== null">
            <GameContent :player-id="getUserInfo.currentGame" />
        </div>
        <div v-else>
            <CharSelection />
        </div>
    </div>
    <div v-else>
        <home-page />
    </div>
</template>

<script>
import GameContent from "@/components/Game/GameContent";
import CharSelection from "@/components/CharSelection";
import { mapGetters } from "vuex";
import HomePage from "@/components/HomePage";

export default {
    name: "GamePage",
    components: {
        HomePage,
        GameContent,
        CharSelection
    },
    computed: {
        ...mapGetters('auth', [
            'loggedIn',
            'getUserInfo'
        ])
    }
};
</script>
