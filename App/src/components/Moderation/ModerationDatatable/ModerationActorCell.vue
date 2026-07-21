<template>
        <span  v-if="isMush || !isAlive">&nbsp;</span> <!-- Keep actors name centered -->
        <span class="user">
            <img
                v-if="actor.playerName"
                class="icon"
                :src="characterEnum[actor.playerName]?.head"
                alt="Character Image"
                style="max-width: 16px;"
            >
            {{ actor.username }}
        </span>
        <span>
            <img class="icon" v-if="isMush"  :src="getImgUrl('status/berzerk.png')" />
            <img class="icon" v-if="!isAlive"  :src="getImgUrl('ui_icons/dead.png')" />
        </span>
</template>

<script setup lang="ts">
import {SanctionActor} from "@/entities/ModerationSanction";
import {characterEnum} from "@/enums/character";
import {getImgUrl} from "@/utils/getImgUrl";

withDefaults(defineProps<{
    actor: SanctionActor,
    isAlive?: boolean
    isMush?: boolean
}>(), {
    isAlive: true,
    isMush: false,
});
</script>

<style lang="scss" scoped>
.icon {
    width: 16px;
    height: 16px;
}

.user {
    display: inline-flex;
    gap: 5px;
}
</style>
