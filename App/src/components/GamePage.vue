<template>
    <div v-if="loggedIn">
        <div v-if="getUserInfo && getUserInfo.playerInfo !== null">
            <GameContent :player-id="getUserInfo.playerInfo" />
        </div>
        <div v-else>
            <CharSelection />
        </div>
    </div>
</template>

<script lang="ts">
import GameContent from "@/components/Game/GameContent.vue";
import CharSelection from "@/components/CharSelection.vue";
import { mapActions, mapGetters } from "vuex";
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
            'getUserInfo',
            'userId'
        ])
    },
    methods: {
        ...mapActions({
            loadUserWarnings: 'moderation/loadUserWarnings'
        })
    },
    beforeMount(): void {
        if (this.userId) {
            this.loadUserWarnings(this.userId);
        }
    }
});
</script>
