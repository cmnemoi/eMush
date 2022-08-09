<template>
    <div v-if="loggedIn">
        <div v-if="getUserInfo && getUserInfo.currentGame !== null">
            <GameContent :player-id="getUserInfo.currentGame" />
        </div>
        <div v-else>
            <CharSelection />
        </div>
    </div>
</template>

<script lang="ts">
import GameContent from "@/components/Game/GameContent.vue";
import CharSelection from "@/components/CharSelection.vue";
import { mapGetters } from "vuex";
import { defineComponent } from "vue";

export default defineComponent ({
    name: "GamePage",
    components: {
        GameContent,
        CharSelection
    },
    computed: {
        ...mapGetters('auth', [
            'loggedIn',
            'getUserInfo'
        ])
    },
    beforeMount() {
        if (this.getUserInfo) {
            console.log(this.getUserInfo);
            console.log(this.getUserInfo.currentGame);
        }
    }
});
</script>
