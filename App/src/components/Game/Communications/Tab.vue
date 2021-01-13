<template>
    <div class="tab" :class="selected ? 'checked' : ''" @click="$emit('select')">
        <img :src="icon">
        <span v-if="numberOfNewMessages" class="new-messages-number">{{ numberOfNewMessagesDisplayed }}</span>
    </div>
</template>

<script>
import { PRIVATE, PUBLIC, ROOM_LOG, TIPS, MUSH, FAVORITES } from '@/enums/communication.enum';

export default {
    props: {
        type: String,
        selected: Boolean,
        numberOfNewMessages: Number
    },
    emits: [
        'select'
    ],
    computed: {
        icon() {
            switch (this.type) {
            case FAVORITES:
                return require('@/assets/images/comms/fav.png');
            case MUSH:
                return require('@/assets/images/comms/mush.png');
            case PRIVATE:
                return require('@/assets/images/comms/private.png');
            case PUBLIC:
                return require('@/assets/images/comms/wall.png');
            case ROOM_LOG:
                return require('@/assets/images/comms/local.png');
            case TIPS:
                return require('@/assets/images/comms/tip.png');
            case "new":
            default:
                return require('@/assets/images/comms/newtab.png');
            }
        },
        numberOfNewMessagesDisplayed() {
            return this.numberOfNewMessages > 20 ? ">20" : this.numberOfNewMessages;
        }
    }
};
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

    .new-messages-number {
        position: absolute;
        top: -6px;
        right: 3px;
        font-size: 0.82em;
        font-weight: 600;
        text-shadow: 0 0 3px black, 0 0 3px black, 0 0 3px black;
    }
}
</style>
