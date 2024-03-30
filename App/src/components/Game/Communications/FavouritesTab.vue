<template>
    <TabContainer id="favourites-tab" :channel="channel">
        <section class="unit">
            <div class="message main-message">
                <div class="char-portrait">
                    <img :src="getImgUrl('char/body/stephen.png')">
                </div>
                <p><span class="author">Stephen :</span>Comrades. Today starts the revolution</p>
                <span class="timestamp">2 jours</span>
            </div>
            <a href="#" class="chat-expand">Afficher les 18 réponses</a>
            <div class="message child-message">
                <p>
                    <img :src="getImgUrl('char/head/frieda.png')">
                    <span class="author">Frieda :</span>The ghost is ok with that. BoooOOOOooOOOooo.
                </p>
                <span class="timestamp">2 jours</span>
            </div>
            <div class="message child-message">
                <p>
                    <img :src="getImgUrl('char/head/jin_su.png')">
                    <span class="author">Jin Su :</span>I, Leader of the Ship, must show my disagreement, you mustached crazy moron !
                </p>
                <span class="timestamp">2 jours</span>
            </div>
        </section>
    </TabContainer>
</template>

<script lang="ts">
import { Channel } from "@/entities/Channel";
import TabContainer from "@/components/Game/Communications/TabContainer.vue";
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";


export default defineComponent ({
    name: "FavouritesTab",
    components: {
        TabContainer
    },
    props: {
        channel: Channel
    },
    methods: {
        getImgUrl
    }
});
</script>

<style lang="scss" scoped>

/* --- PROVISIONAL UNTIL LINE 269 --- */

.message {
    position: relative;
    align-items: flex-start;
    flex-direction: row;

    .char-portrait {
        align-items: flex-start;
        justify-content: flex-start;
        min-width: 36px;
        margin-top: 4px;
        padding: 6px 2px;
    }

    p:not(.timestamp) {
        position: relative;
        flex: 1;
        margin: 3px 0;
        padding: 4px 6px;
        border-radius: 3px;
        background: white;
        word-break: break-word;

        &::v-deep(em) { color: $red; } //Makes italic text red

        .author {
            color: $blue;
            font-weight: 700;
            font-variant: small-caps;
            padding-right: 0.25em;
        }
    }

    &.new p { //New messages styling
        border-left: 2px solid #ea9104;

        &::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            left: -6px;
            min-height: 11px;
            width: 11px;
            background: transparent url('/src/assets/images/comms/thinklinked.png')') center no-repeat;
        }
    }

    &.main-message {

        p { min-height: 52px; }

        p::before { //Bubble triangle*/
            $size: 8px;

            content: "";
            position: absolute;
            top: 4px;
            left: -$size;
            width: 0;
            height: 0;
            border-top: $size solid transparent;
            border-bottom: $size solid transparent;
            border-right: $size solid white;
        }

        &.new p::before { border-right-color: #ea9104; }
    }

    &.child-message {
        margin-left: 50px;
        img { margin-right: 3px; }
        p { margin-top: 10px; }

        p::before { //Bubble triangle
            $size: 8px;

            content: "";
            position: absolute;
            top: -$size;
            left: 4px;
            width: 0;
            height: 0;
            border-left: $size solid transparent;
            border-right: $size solid transparent;
            border-bottom: $size solid white;
        }

        /* MESSAGES LINKTREE */

        &::before {
            --border-radius: 5px;

            content: "";
            position: absolute;
            top: calc(0px - var(--border-radius));
            left: -36px;
            width: calc(28px + var(--border-radius));
            height: calc(26px + var(--border-radius));
            border-left: 1px solid #aad4e5;
            border-bottom: 1px solid #aad4e5;
            border-radius: var(--border-radius);
            clip-path:
                polygon(
                    0 var(--border-radius),
                    calc(100% - var(--border-radius)) var(--border-radius),
                    calc(100% - var(--border-radius)) 100%,
                    0 100%
                );
        }

        &:not(:last-child)::after {
            --border-radius: 5px;

            content: "";
            position: absolute;
            top: 25px;
            left: -36px;
            width: calc(28px + var(--border-radius));
            bottom: calc(-4px - var(--border-radius));
            border-left: 1px solid #aad4e5;
            border-top: 1px solid #aad4e5;
            border-radius: var(--border-radius);
            clip-path:
                polygon(
                    0 0,
                    calc(100% - var(--border-radius)) 0,
                    calc(100% - var(--border-radius)) calc(100% - var(--border-radius)),
                    0 calc(100% - var(--border-radius))
                );
        }
    }

    &.neron { //Neron messages styling

        .char-portrait {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 2;
            margin: 4px 6px;
        }

        p {
            background: #74cbf3;
            font-variant: small-caps;

            .author { color: inherit; }
        }

        &.main-message {
            p {
                padding-left: 46px;
                &::before { content: none; } //removes the bubble triangle
            }
        }

        &.child-message p::before { border-color: #74cbf3; }
    }

    .actions { //buttons styling
        visibility: hidden;
        opacity: 0;
        position: absolute;
        right: 3px;
        top: -3px;
        height: 14px;
        transition: visibility 0s 0.15s, opacity 0.15s 0s, top 0.15s 0s;
    }
}

.message:hover,
.message:focus,
.message:focus-within,
.message:active {
    .actions {
        visibility: visible;
        opacity: 1;
        top: 5px;
        transition: visibility 0s 0.5s, opacity 0.15s 0.5s, top 0.15s 0.5s;
    }
}

/* --- END OF PROVISIONAL --- */

#favourites-tab {
    .unit {
        border-bottom: 1px solid rgb(170, 212, 229);
    }
}

.chat-expand {
    display: flex;
    padding: 4px;
    color: #67b000;
    border-radius: 3px;
    font-size: 0.95em;
    text-decoration: underline;

    &.active,
    &:hover,
    &:focus {
        background: $lightCyan;
    }
}

</style>
