<template>
    <div v-show="loading" class="spinner_overlay">
        <div class="spinner-container">
            <span>{{ $t('loading') }}</span>
            <div class="spinner">
                <img src="@/assets/images/floor-tile.gif">
                <img src="@/assets/images/floor-tile.gif">
                <img src="@/assets/images/floor-tile.gif">
                <img src="@/assets/images/floor-tile.gif">
                <img src="@/assets/images/floor-tile.gif">
                <img class="cat" src="@/assets/images/char/body/cat.png">
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent ({
    name: 'Spinner',
    props: {
        loading: {
            type: Boolean,
            default: true
        }
    }
});
</script>

<style  lang="scss" scoped>

.spinner_overlay { //dark background
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    align-items: center;
    justify-content: center;
    background-color: rgba(15, 15, 67, .6);
    z-index: 100;
}

.spinner-container {
    align-items: center;
    // width: 96px;

    span {
        text-align: center;
        margin-bottom: .4em;
        font-size: 1.2em;
        font-variant: small-caps;
        font-weight: bold;
        letter-spacing: .02em;
    }
}

.spinner { //moving tiles
    position: relative;
    z-index: 100;
    flex-direction: row;
    align-items: flex-start;

    img:not(.cat) {
        width: 32px;
        margin-left: -16px;
        animation: movingtile 4s cubic-bezier(0.25, 0, 0.15, 1) infinite both;
    }

    img.cat { // cat is put on 4th tile
        position: absolute;
        left: 37px;
        top: 27px;
        width: 22px;
        animation: movingtile 4s 2s cubic-bezier(0.25, 0, 0.15, 1) infinite both;
    }

    @for $i from 1 through 5 {
        img:nth-child(#{$i}) {
            margin-top: calc(#{$i} * 8px);
            animation-delay: calc(#{$i} * .5s);
        }
    }
}

@keyframes movingtile {
    0%, 5% {
        opacity: 0;
        transform: translateY(28px);
    }
    45%, 60% {
        opacity: 1;
        transform: translateY(0);
    }
    85%, 100% {
        opacity: 0;
        transform: translateY(-8px);
    }
}

</style>
