<template>
    <TabContainer id="tips-tab" :channel="channel" v-if="channel?.tips">
        <section class="unit">
            <div class="banner">
                <span><img :src="getImgUrl('comms/tip.png')"> {{ channel.name }} <img :src="getImgUrl('comms/tip.png')"></span>
            </div>
            <div :class="'tip ' + (player.isMush() ? ' red' : 'cyan')">
                <span class="title" v-html="formatText(channel.tips.teamObjectives.title)"/>
                <ul class="list">
                    <li v-for="element in channel.tips.teamObjectives.elements" :key="element" v-html="formatText(element)"/>
                </ul>
                <a
                    v-if="channel.tips.teamObjectives.tutorial"
                    :href="channel.tips.teamObjectives.tutorial.link"
                    class="link"
                    target="_blank"
                    rel="noopener"
                    v-html="formatText(channel.tips.teamObjectives.tutorial.title)"/>
            </div>
            <div class="tip focus">
                <span class="title" v-html="formatText(channel.tips.characterObjectives.title)"/>
                <ul class="list">
                    <li v-for="element in channel.tips.characterObjectives.elements" :key="element" v-html="formatText(element)"/>
                </ul>
                <a
                    :href="channel.tips.characterObjectives.tutorial.link"
                    class="link"
                    target="_blank"
                    rel="noopener"
                    v-html="formatText(channel.tips.characterObjectives.tutorial.title)"/>
            </div>
            <div class="tip">
                <span class="title">{{ channel.tips.externalResources.title }}</span>
                <ul class="list">
                    <li v-for="element in channel.tips.externalResources.elements" :key="element.text">
                        <a
                            :href="element.link"
                            v-if="element.link"
                            class="link"
                            target="_blank"
                            rel="noopener"
                            v-html="formatText(element.text)"/>
                        <p v-else v-html="formatText(element.text)"/>
                    </li>
                </ul>
            </div>
        </section>
    </TabContainer>
</template>

<script lang="ts">
import { Channel } from "@/entities/Channel";
import TabContainer from "@/components/Game/Communications/TabContainer.vue";
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { formatText } from "@/utils/formatText";
import { mapGetters } from "vuex";


export default defineComponent ({
    name: "TipsTab",
    components: {
        TabContainer
    },
    props: {
        channel: Channel
    },
    computed: {
        ...mapGetters({
            player: "player/player"
        })
    },
    methods: {
        getImgUrl,
        formatText
    }
});
</script>

<style lang="scss" scoped>

/* --- PROVISIONAL UNTIL LINE 166 --- */

.message {
    position: relative;
    align-items: flex-start;
    flex-direction: row;

    .char-portrait {
        align-items: flex-start;
        justify-content: flex-start;
        min-width: 36px;
        padding: 2px;
    }

    p:not(.timestamp) {
        position: relative;
        flex: 1;
        margin: 3px 0;
        padding: 4px 6px;
        border-radius: 3px;
        background: white;
        word-break: break-word;

        .author {
            color: $cyan;
            font-weight: 700;
            font-variant: small-caps;
            padding-right: 0.25em;
        }

        em { color: $red; }
    }

    &.new p {
        border-left: 2px solid #ea9104;

        &::after {
            content: "";
            position: absolute;
            top: 7px;
            left: -6px;
            height: 11px;
            width: 11px;
            background: transparent url('/src/assets/images/comms/thinklinked.png') center no-repeat;
        }
    }

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

    &.new p {
        &::before { border-right-color: #ea9104; }
        &::after { top: 22px; }
    }
}

/* ----- */

.chat-input {
    position: relative;
    flex-direction: row;
    padding: 7px 7px 4px 7px;

    a {
        @include button-style();

        width: 24px;
        margin-left: 4px;
    }

    textarea {
        position: relative;
        flex: 1;
        resize: vertical;
        min-height: 29px;
        padding: 3px 5px;
        font-style: italic;
        opacity: 0.85;
        box-shadow: 0 1px 0 white;
        border: 1px solid #aad4e5;
        border-radius: 3px;

        &:active,
        &:focus {
            min-height: 48px;

            /* max-height: 80%; */
            font-style: initial;
            opacity: 1;
        }
    }
}

/* ----- */

.log {
    position: relative;
    padding: 4px 5px;
    margin: 1px 0;
    border-bottom: 1px solid rgb(170, 212, 229);

    p {
        margin: 0;
        font-size: 0.95em;
        &::v-deep(img) { vertical-align: middle; }
    }
}

/* --- END OF PROVISIONAL --- */

#tips-tab {
    a { color: $deepGreen; }

    .unit {
        padding: 1px 0 !important;
    }

    .tip {
        margin: 10px 6px;
        padding: 6px;
        background: white;
        box-shadow: 0 1px 1px 0 rgba(9, 10, 97, 0.15);

        &.cyan {
            border: 3px solid $cyan;
            box-shadow: 0 7px 6px -4px rgba(9, 10, 97, 0.5);
        }
        &.focus {
            border: 3px solid $green;
            box-shadow: 0 7px 6px -4px rgba(9, 10, 97, 0.5);
        }
        &.red {
            border: 3px solid $mushRed;
            box-shadow: 0 7px 6px -4px rgba(9, 10, 97, 0.5);
        }

        span {
            text-align: center;
            font-weight: 700;
            font-variant: small-caps;
        }

        p { margin: 4px 0; }
    }

    .list {
        flex-direction: column;

        li {
            list-style: disc;
            margin-left: 20px;
        }
    }
}

</style>
