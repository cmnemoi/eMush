<template>
    <div class="actions">
        <img :src="getImgUrl('ui_icons/sanction_details.png')" alt="Details" @click="$emit('detail', sanction)"/>
        <router-link
            v-if="goToPlayer && sanction.user.playerId"
            :to="{ name: 'ModerationViewPlayerDetail', params: { playerId: sanction.user.playerId } }"
            custom
            v-slot="{ navigate }"
        >
            <img
                :src="getImgUrl('ui_icons/player_details.png')"
                alt="Sanction List"
                @click="navigate"
            />
        </router-link>
        <router-link
            v-if="sanctionList"
            :to="{ name: 'SanctionListPage', params: { username: sanction.user.username, userId: sanction.user.id } }"
            custom
            v-slot="{ navigate }"
        >
            <img
                :src="getImgUrl('ui_icons/sanction_list.png')"
                alt="Sanction List"
                @click="navigate"
            />
        </router-link>
    </div>
</template>

<script setup lang="ts">
import {ModerationSanction} from "@/entities/ModerationSanction";
import {getImgUrl} from "@/utils/getImgUrl";

withDefaults(defineProps<{
    sanction: ModerationSanction,
    sanctionDetails?: boolean,
    goToPlayer?: boolean,
    goToUser?: boolean,
    sanctionList?: boolean
}>(), {
    sanctionDetails: true,
    goToPlayer: false,
    goToUser: false,
    sanctionList: false
});

defineEmits<{
    detail: [sanction: ModerationSanction],
}>();
</script>

<style lang="scss" scoped>
.actions {
    align-items: center;

    img {
        cursor: pointer;
        width: 26px;
        height: 26px;

        &:hover {
            opacity: 0.7;
        }
    }
}
</style>