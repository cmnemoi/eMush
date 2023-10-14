<template>
    <Tippy
        tag="div"
        class="tab"
        :class="[selected ? 'checked' : '', isPirated ? 'pirated' : '']"
        @click="$emit('select')"
    >
        <img :src="icon">
        <span v-if="numberOfNewMessages" class="new-messages-number">{{ numberOfNewMessagesDisplayed }}</span>
        <template #content>
            <h1 v-html="formatContent(name)" />
            <p v-html="formatContent(description)" />
        </template>
    </Tippy>
</template>

<script lang="ts">

import { ChannelType } from "@/enums/communication.enum";
import { defineComponent } from "vue";

export default defineComponent ({
    props: {
        type: String,
        selected: Boolean,
        isPirated: Boolean,
        numberOfNewMessages: {
            type: Number,
            required: false,
            default: 0
        },
        name: String,
        description: String
    },
    emits: [
        'select'
    ],
    computed: {
        icon(): string {
            switch (this.type) {
            case ChannelType.FAVORITES:
                return 'src/assets/images/comms/fav.png';
            case ChannelType.MUSH:
                return 'src/assets/images/comms/mush.png';
            case ChannelType.PRIVATE:
                return 'src/assets/images/comms/private.png';
            case ChannelType.PUBLIC:
                return 'src/assets/images/comms/wall.png';
            case ChannelType.ROOM_LOG:
                return 'src/assets/images/comms/local.png';
            // TODO: not implemented yet
            // case ChannelType.TIPS:
            //     return 'src/assets/images/comms/tip.png';
            case "new":
            default:
                return 'src/assets/images/comms/newtab.png';
            }
        },
        numberOfNewMessagesDisplayed(): string|number {
            return this.numberOfNewMessages > 20 ? ">20" : this.numberOfNewMessages;
        }
    }
});
</script>

<style lang="scss" scoped>

.tab {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: visible;
    float: left;
    cursor: pointer;
    width: 31px;
    min-height: 25px;
    margin-right: 4px;
    * { z-index: 2; }

    &::after { // Background of the tab icons
        content: "";
        z-index: 1;
        position: absolute;
        width: 100%;
        height: 100%;
        background: #213578;

        @include corner-bezel(4.5px, 4.5px, 0);
    }



    &.checked,
    &.active,
    &:hover,
    &:focus {
        &::after {
            background: rgba(194, 243, 252, 1);
        }
    }
    &.pirated::after{ // Background of the tab icons
        background: red;
    }

    .new-messages-number {
        position: absolute;
        top: -6px;
        right: 3px;
        font-size: 0.93em;
        font-weight: 600;
        text-shadow: 0 0 3px black, 0 0 3px black, 0 0 3px black;
    }
}
</style>
