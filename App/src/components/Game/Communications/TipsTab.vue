<template>
    <TabContainer id="tips-tab" :channel="channel" style="display: none;">
        <section class="unit">
            <div class="banner">
                <span><img src="@/assets/images/comms/tip.png"> Conseils <img src="@/assets/images/comms/tip.png"></span>
            </div>
            <div class="tip focus">
                <span class="title">Rétablir la communication</span>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque et arcu sit amet arcu convallis dictum. Nam vehicula gravida pretium. Nunc nec malesuada turpis. Vivamus vitae orci leo. Suspendisse potenti. In eu felis quam. Nullam non urna quis sem molestie blandit sit amet eget tortor. Duis aliquet eros quis tempor ornare. Maecenas eu semper quam. Cras malesuada bibendum leo, vel mollis lectus lacinia eget. Nam et lorem eu diam consectetur tempus. Proin fermentum dolor in ullamcorper fermentum. Sed consequat ex vel odio tempus, vitae scelerisque ante tristique.</p>
            </div>
            <div class="tip">
                <span class="title">Avancer un projet</span>
                <p>Vivamus iaculis magna nec egestas venenatis. Curabitur sed aliquam ex, vel lobortis risus. Aenean consequat pulvinar metus quis accumsan. Duis dictum dictum neque at pharetra. Sed enim justo, porttitor vitae urna eu, rhoncus mattis felis. Nullam in felis quis mi faucibus consequat. Pellentesque congue sagittis libero sed porta. In vitae sagittis sapien. Suspendisse potenti. Duis dignissim eget lorem vel finibus.</p>
            </div>
        </section>

        <section class="unit">
            <div class="banner">
                <span><img src="@/assets/images/comms/star.png"> Annonce Générale <img src="@/assets/images/comms/star.png"></span>
            </div>
            <div class="message">
                <div class="char-portrait">
                    <img src="@/assets/images/char/body/paola.png">
                </div>
                <p>
                    <span class="author">Paola :</span><strong>Salut à tous !</strong><br>
                    Ceci est une annonce générale !
                </p>
                <span class="timestamp">~1d</span>
            </div>
        </section>

        <section class="unit">
            <div class="banner">
                <span><img src="@/assets/images/comms/notebook.png"> Missions <img src="@/assets/images/comms/notebook.png"></span>
            </div>
            <div class="message">
                <div class="char-portrait">
                    <img src="@/assets/images/char/body/jin_su.png">
                </div>
                <p><span class="author">Jin Su :</span>Mission pour rétablir les comms.</p>
                <span class="timestamp">~1d</span>
            </div>
        </section>
    </TabContainer>
</template>

<script lang="ts">
import { Channel } from "@/entities/Channel";
import TabContainer from "@/components/Game/Communications/TabContainer.vue";
import { defineComponent } from "vue";


export default defineComponent ({
    name: "TipsTab",
    components: {
        TabContainer
    },
    props: {
        channel: Channel
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
            color: $blue;
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
            background: transparent url('~@/assets/images/comms/thinklinked.png') center no-repeat;
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
    .unit {
        padding: 1px 0 !important;
    }

    .tip {
        margin: 10px 6px;
        padding: 6px;
        background: white;
        box-shadow: 0 1px 1px 0 rgba(9, 10, 97, 0.15);

        &.focus {
            border: 3px solid $green;
            box-shadow: 0 7px 6px -4px rgba(9, 10, 97, 0.5);
        }

        span {
            text-align: center;
            font-weight: 700;
            font-variant: small-caps;
        }

        p { margin: 4px 0; }
    }
}

</style>
