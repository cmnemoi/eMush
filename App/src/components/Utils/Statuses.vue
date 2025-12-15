<template>
    <Tippy
        tag="span"
        v-for="status in filterStatuses(statuses)"
        :key="status.id"
        class="status"
        :on-show="() => !onItem"
    >
        <div v-if="hasCharge(status) && !isEmptyElectricCharge(status) && !onItem" class="charge">
            {{ status.charge }}
            <img :src="statusIcon(status)"/>
        </div>
        <div v-else>
            <!--
                If statusIcon isn't part of the previous div, the space character between the charge and the icon gets
                collapsed, and none of the non-collapsible whitespace characters are the exact same width. Hence this
                v-else, even if minorly redundant.
            -->
            <img :src="statusIcon(status)"/>
        </div>
        <template #content>
            <h1 v-html="formatContent(status.name)" />
            <p v-html="formatContent(status.description)" />
        </template>
    </Tippy>
</template>

<script lang="ts">
import { statusPlayerEnum } from "@/enums/status.player.enum";
import { statusItemEnum, StatusItemNameEnum, StatusItemPriorityEnum } from "@/enums/status.item.enum";
import { statusRoomEnum } from "@/enums/status.room.enum";
import { defineComponent } from "vue";
import { Status } from "@/entities/Status";
import { getImgUrl } from "@/utils/getImgUrl";
import { Tippy } from "vue-tippy";
import { mixin } from "@/mixin/mixin";

export default defineComponent ({
    components: { Tippy },
    mixins: [mixin],
    props: {
        statuses: Array<Status>,
        type: String,
        onItem: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        statusIcon() {
            return (status: Status): string | null => {
                switch (this.type) {
                case "player":
                    return statusPlayerEnum[status.key]?.icon || null;
                case "disease":
                    switch (status.diseaseType) {
                    case "disease":
                        return getImgUrl('status/disease.png');
                    case "disorder":
                        return getImgUrl('status/disorder.png');
                    case "injury":
                        return getImgUrl('status/injury.png');
                    }
                    return null;
                case "item":
                case "equipment":
                    if (this.isEmptyElectricCharge(status)) {
                        return getImgUrl('status/nocharge.png');
                    }
                    return statusItemEnum[status.key]?.icon || null;
                case "room":
                    return statusRoomEnum[status.key]?.icon || null;
                default:
                    return null;
                }
            };
        }
    },
    methods: {
        filterStatuses(statuses: Status[]): Status[] {
            if (!this.onItem) {
                return statuses;
            }
            const toRemove = new Set([
                StatusItemNameEnum.ALIEN_ARTEFACT,
                StatusItemNameEnum.ELECTRIC_CHARGE,
                StatusItemNameEnum.HEAVY,
                StatusItemNameEnum.HIDDEN
            ]);
            const filterFunction = (status: Status) => !toRemove.has(status.key as StatusItemNameEnum) || this.isEmptyElectricCharge(status);
            const sortFunction = (a: Status, b: Status) => StatusItemPriorityEnum[a.key] - StatusItemPriorityEnum[b.key];
            return statuses.filter(filterFunction).sort(sortFunction).slice(0, 3);
        },
        hasCharge(status: Status): boolean {
            return status.charge !== null && status.charge !== undefined;
        },
        isEmptyElectricCharge(status: Status): boolean {
            return status.key === StatusItemNameEnum.ELECTRIC_CHARGE && status.charge === 0;
        }
    }
});
</script>

<style lang="scss" scoped>
.status {
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.charge {
    flex-direction: row;
    gap: 2px;
    text-shadow: 0 0 4px black;
    padding-bottom: 1px;
}
</style>
