<template>
    <div class="textual" @click="$emit('clickOnNothing')">
        <h1>{{ $t('alpha.doors') }}</h1>
        <div v-for="door in room.doors" :key="door.id" class="door">
            <p>{{ door.direction }} :</p>
            <ActionButton
                v-for="(action, key) in door.actions"
                :key="key"
                class="door-action-button"
                :action="action"
                @click="$emit('clickOnDoor', { action, door })"
            />
        </div>

        <h1>{{ $t('alpha.inventory') }}</h1>
        <p @click="$emit('clickOnInventory'); $event.stopPropagation()">
            {{ $t('alpha.inventoryDescription') }}
        </p>

        <h1>{{ $t('alpha.equipement') }}</h1>
        <div v-for="(equipment,key) in room.equipments" :key="key">
            <p @click="$emit('clickOnTarget', equipment); $event.stopPropagation()">
                {{ equipment.name }}
                <Statuses :statuses="equipment.statuses" type="equipment" />
            </p>
        </div>
        <h1>{{ $t('alpha.player') }}</h1>
        <div v-for="(player,key) in room.players" :key="key">
            <p @click="$emit('clickOnTarget', player); $event.stopPropagation()">
                {{ player.character.name }}
            </p>
        </div>
    </div>
</template>

<script lang="ts">
import ActionButton from "@/components/Utils/ActionButton.vue";
import Statuses from "@/components/Utils/Statuses.vue";
import { Room } from "@/entities/Room";
import { defineComponent } from "vue";

export default defineComponent ({
    components: {
        ActionButton,
        Statuses
    },
    props: {
        room: Room
    },
    emits: [
        "clickOnDoor",
        "clickOnInventory",
        "clickOnTarget",
        "clickOnNothing"
    ]
});
</script>

<style lang="scss" scoped>

.textual {
    overflow: auto;
    padding: 18px 12px;
    font-size: 0.83em;

    h1,
    h2,
    h3 {
        color: #cf1830;
        font-size: 1.5em;
        font-variant: small-caps;
        margin: 12px 0 4px 0;
    }

    p {
        margin: 12px 0 4px 0;
        font-weight: 700;
    }

    & > *:last-child {
        padding-bottom: 188px;
    }

    .door-action-button {
        max-width: 120px;
    }
}

@keyframes self-position-color {
    0% { background: #ff0; }
    39% { background: #ff0; }
    40% { background: #889c28; }
    100% { background: #889c28; }
}

</style>
