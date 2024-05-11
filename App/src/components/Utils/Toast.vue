<template>
    <template v-if="isOpen">
        <div class="toast" :class="type">
            <div class="icon">
                <img v-if="type === 'warning'" :src="getImgUrl('att.png')" alt="warning" />
                <img v-else-if="type === 'error'" :src="getImgUrl('neron_eye.gif')" alt="error" />
                <img v-else-if="type === 'success'" :src="getImgUrl('ready.png')" alt="success" />
                <img v-else :src="getImgUrl('info.png')" alt="info" />
            </div>
            <div class="content">
                <h1 v-if="title">
                    {{ title }}
                </h1>
                <button class="modal-close" @click="close">
                    {{ $t('game.popUp.close') }}
                </button>
                <slot />
            </div>
        </div>
    </template>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { mapActions, mapGetters } from "vuex";

export default defineComponent ({
    name: 'Toast',
    computed: {
        ...mapGetters({
            isOpen: 'toast/isOpen',
            title: 'toast/title',
            type: 'toast/type'
        })
    },
    methods: {
        ...mapActions({
            close: 'toast/closeToast'
        }),
        getImgUrl
    }
});
</script>

<style lang="scss" scoped>

$info-color: #3965fb;
$success-color: #16b64b;
$warning-color: #e7b719;
$error-color: #e72719;
.toast {

    position: relative;
    width: 100%;
    max-width: 520px;
    flex-direction: row;
    background-color: rgba(35, 37, 100, .9);
    border: 2px solid $info-color;
    border-radius: 5px;
    box-shadow: 0 0 22px 8px transparentize(#09092d, 0.2);

    animation: appear 0.8s ease-out 1;
    animation-fill-mode: both;

    :deep(p) { margin: 0.4em 0 0; }
    :deep(a) { color: $green; }

    .icon {
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: transparentize($info-color, 0.4);
        width: 3.6em;

        img { max-width: 36px; }
    }

    &.success {
        border-color: $success-color;

        .icon { background: transparentize($success-color, 0.4); }
    }

    &.warning {
        border-color: $warning-color;

        .icon { background: transparentize($warning-color, 0.4); }
    }

    &.error {
        border-color: $error-color;
        .icon { background: transparentize($error-color, 0.4); }
    }

    .content {
        display: block;
        width: 100%;
        margin: 0.5em 1.2em 0.8em;
    }

    @for $i from 1 through 12 {
        &:nth-child(#{$i}) { animation-delay: $i*400ms ; }
    }

    @media screen and (max-width: $breakpoint-desktop-m) { box-shadow: 0 0 14px 4px transparentize(#09092d, 0.2); }
}

h1 {
    font-size: 1.2em;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    text-align: left;
    width: calc(100% - 2.6em);
    margin: 0.4em 0 0;
}

.modal-close {
    cursor: pointer;
    position: absolute;
    text-align: center;
    right: 0;
    top: 0;
    padding: 12px;
    color: transparentize(white, 0.4);
    font-size: 1em;
    letter-spacing: 0.03em;
    text-decoration: none;
    font-variant: small-caps;
    transition: all 0.15s;

    &:hover, &:focus, &:active { color: white; }
}

@keyframes appear {
  from {
    opacity: 0;
    transform: translateY(-6em);
}
  to {
    opacity: 1;
    transform: translateY(0);
}
}
</style>
