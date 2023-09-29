<template>
    <div v-if="player">
        {{ player.jsonEncode() }}
    </div>
</template>

<script lang="ts">
import { AdminViewPlayer } from "@/entities/AdminViewPlayer";
import { defineComponent } from "vue";
import AdminService from "@/services/admin.service";

interface AdminViewPlayerData {
    player: AdminViewPlayer | null,
    errors: any,
}

export default defineComponent({
    name: "AdminViewPlayerDetail",
    data() : AdminViewPlayerData {
        return {
            player: null,
            errors: {}
        };
    },
    beforeMount() {
        AdminService.getAdminViewPlayer(Number(this.$route.params.playerId))
            .then((response) => {
                this.player = new AdminViewPlayer().load(response.data);
            })
            .catch((error) => {
                this.errors = error.response.data.errors;
            });
    },
});
</script>


