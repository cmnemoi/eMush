<template>
    <div
        class="confirm-popover-wrapper"
        :style="{
            top: offsetTop !== null ? `${offsetTop}px` : undefined,
            bottom: offsetBottom !== null ? `${offsetBottom}px` : undefined,
            left: offsetLeft !== null ? `${offsetLeft}px` : undefined,
            right: offsetRight !== null ? `${offsetRight}px` : undefined
        }"
    >
        <div class="confirm-popover">
            <div class="confirm-header">
                <span>{{ $t(title) }}</span>
            </div>
            <div class="confirm-buttons">
                <button class="confirm-btn yes-btn" @click="$emit('yes')">
                    {{ $t(yesButton) }}
                </button>
                <button class="confirm-btn no-btn" @click="$emit('no')">
                    {{ $t(noButton)}}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps({
    title: {
        type: String,
        default: 'confirmPopOver.title'
    },
    yesButton: {
        type: String,
        default: 'confirmPopOver.yes'
    },
    noButton: {
        type: String,
        default: 'confirmPopOver.no'
    },
    offsetTop: {
        type: Number,
        default: null
    },
    offsetBottom: {
        type: Number,
        default: null
    },
    offsetLeft: {
        type: Number,
        default: null
    },
    offsetRight: {
        type: Number,
        default: null
    }
});

defineEmits(['yes', 'no']);
</script>

<style scoped lang="scss">
.confirm-popover-wrapper {
    overflow: auto;
    position: absolute;
    z-index: 2000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.9);
    pointer-events: auto;

    .confirm-popover {
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-width: 300px;
        max-width: 400px;
        z-index: 2000;
        padding: 15px;
        background-color: rgba(35, 37, 100, 0.9);
        box-shadow: inset 0 0 12px 3px #3965fb, inset 0 0 0 2px #3965fb;
        pointer-events: auto;

        @media screen and (max-width: $breakpoint-mobile-l) {
            max-width: 285px;
        }

        .confirm-header {
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            font-size: 1em;
            line-height: 1.4;
        }

        .confirm-buttons {
            display: flex;
            flex-direction: row;
            gap: 10px;
            justify-content: center;

            .confirm-btn {
                justify-content: center;
                align-items: center;
                cursor: pointer;
                min-width: 75px;
                padding: 8px 16px;
                border: 2px solid #3965fb;
                border-radius: 3px;
                background-color: rgba(57, 101, 251, 0.3);
                color: white;
                font-weight: bold;
                transition: all 0.15s;

                &:hover {
                    background-color: rgba(57, 101, 251, 0.6);
                    transform: scale(1.05);
                }

                &:active {
                    transform: scale(0.95);
                }

                &.yes-btn {
                    border-color: #3965fb;
                }

                &.no-btn {
                    border-color: #fb3939;
                    background-color: rgba(251, 57, 57, 0.3);

                    &:hover {
                        background-color: rgba(251, 57, 57, 0.6);
                    }
                }
            }
        }
    }
}
</style>
