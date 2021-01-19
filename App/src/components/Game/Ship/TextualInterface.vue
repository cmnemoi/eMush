<template>
    <div class="textual" @click="$emit('clickOnNothing')">
        <h1>Doors</h1>
        <div v-for="door in room.doors" :key="door.id" class="door">
            <p>{{ roomsTrad[door.direction].name }} :</p>
            <ActionButton
                v-for="(action, key) in door.actions"
                :key="key"
                class="door-action-button"
                :action="action"
                @click="$emit('clickOnDoor', { action, door })"
            />
        </div>

        <h1>Inventory</h1>
        <p @click="$emit('clickOnInventory'); $event.stopPropagation()">
            Click here to open the Room Inventory
        </p>

        <h1>Equipment</h1>
        <div v-for="(equipment,key) in room.equipments" :key="key">
            <p @click="$emit('clickOnTarget', equipment); $event.stopPropagation()">
                {{ equipment.name }}
                <Statuses :statuses="equipment.statuses" type="equipment" />
            </p>
        </div>
        <h1>Players</h1>
        <div v-for="(player,key) in room.players" :key="key">
            <p @click="$emit('clickOnTarget', player); $event.stopPropagation()">
                {{ player.characterValue }}
            </p>
        </div>
    </div>
</template>

<script>
import ActionButton from "@/components/Utils/ActionButton";
import Statuses from "@/components/Utils/Statuses";
import { Room } from "@/entities/Room";
import { mapGetters } from "vuex";

export default {
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
    ],
    computed: {
        ...mapGetters('traduction', [
            'roomsTrad'
        ])
    }
};
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
        margin-bottom: 188px;
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
