<template>
    <div class="user">
        <img
            v-if="actor.playerName"
            :src="characterEnum[actor.playerName]?.head"
            alt="Character Image"
            style="max-width: 16px;">
        {{ actor.username }}
        <div v-if="status?.isAlive" class="alive">
            {{ $t('moderation.sanction.alive') }}
        </div>
        <div v-if="status?.isMush" class="isMush">
            MUSH
        </div>
    </div>
</template>

<script setup lang="ts">
import { SanctionActor } from "@/entities/ModerationSanction";
import { characterEnum } from "@/enums/character";

defineProps<{
    actor: SanctionActor,
    status?: { isAlive: boolean | null, isMush: boolean | null } | null,
}>();
</script>

<style lang="scss" scoped>
.user {
    display: inline-flex;
    flex-direction: row;
    align-items: center;
    gap: 5px;
}

.alive {
    color: red;
}

.isMush {
    color: pink;
}
</style>
