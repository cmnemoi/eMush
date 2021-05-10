<template>
    <TabContainer id="mush-tab" :channel="channel" new-message-allowed>
        <div class="actions">
            <a href="#"><img src="@/assets/images/comms/refresh.gif">Rafr.</a>
            <a href="#"><img src="@/assets/images/comms/alert.png">Plainte</a>
        </div>
        <section class="unit">
            <div class="banner cycle-banner">
                <img class="expand" src="@/assets/images/comms/less.png">
                <span>Jour 5 Cycle 6</span>
            </div>
            <div class="message new">
                <div class="char-portrait">
                    <img src="@/assets/images/char/body/ian.png">
                </div>
                <p><img src="@/assets/images/comms/talkie.png"> <span class="author">Ian :</span><strong><em>Piloting</em></strong></p>
                <span class="timestamp">~1d</span>
            </div>
        </section>
        <section class="unit">
            <div class="banner cycle-banner">
                <img class="expand" src="@/assets/images/comms/less.png">
                <span>Jour 5 Cycle 5</span>
            </div>
            <div class="message new">
                <div class="char-portrait">
                    <img src="@/assets/images/char/body/jin_su.png">
                </div>
                <p><img src="@/assets/images/comms/talkie.png"> <span class="author">Jin Su :</span>So far eight hunters shot total (3 + 5), no scrap collected yet.</p>
                <span class="timestamp">~3d</span>
            </div>
            <div class="message">
                <div class="char-portrait">
                    <img src="@/assets/images/char/body/ian.png">
                </div>
                <p><img src="@/assets/images/comms/talkie.png"> <span class="author">Ian :</span>Excellent sir, I can see why they have you training the new pilots :P</p>
                <span class="timestamp">~3d</span>
            </div>
            <section class="log">
                <p class="text-log">
                    <img src="@/assets/images/triumph.png"> Bienvenue parmi le Mush <strong>Ian</strong>. Vous avez été récompensé avec <strong>120 points de Triomphe</strong>.
                </p>
                <span class="timestamp">~5d</span>
            </section>
        </section>
    </TabContainer>
</template>

<script>
import { Channel } from "@/entities/Channel";
import TabContainer from "@/components/Game/Communications/TabContainer";


export default {
    name: "MushTab",
    components: {
        TabContainer
    },
    props: {
        channel: Channel
    }
};
</script>

<style lang="scss" scoped>

/* --- PROVISIONAL UNTIL LINE 185 --- */

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
            color: #2081e2;
            font-weight: 700;
            font-variant: small-caps;
            padding-right: 0.25em;
        }

        em { color: #cf1830; }
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

.log {
    position: relative;
    padding: 4px 5px;
    margin: 1px 0;
    border-bottom: 1px solid rgb(170, 212, 229);

    p {
        margin: 0;
        font-size: 0.95em;
        >>> img { vertical-align: middle; }
    }
}

/* --- END OF PROVISIONAL --- */

#mush-tab {
    .unit {
        padding: 5px 0;
    }

    >>> .chat-input .submit { //change the submit button color
        $color: #ff3867;
        $hover-color: #fa6480;

        background: $color;
        background:
            linear-gradient(
                0deg,
                darken(adjust-hue($color, 13), 5.49) 2%,
                $color 6%,
                $color 46%,
                lighten(adjust-hue($color, -6), 3.5) 54%,
                lighten(adjust-hue($color, -6), 3.5) 94%,
                lighten(desaturate($color, 25), 15.49) 96%
            );

        &:hover,
        &:focus {
            background: $hover-color;
            background:
                linear-gradient(
                    0deg,
                    darken(adjust-hue($hover-color, 14), 3.92) 2%,
                    $hover-color 6%,
                    $hover-color 46%,
                    lighten(adjust-hue($hover-color, -4), 1) 54%,
                    lighten(adjust-hue($hover-color, -4), 1) 94%,
                    lighten(desaturate($hover-color, 18.1), 13.14) 96%
                );
        }
    }

    .actions {
        flex-direction: row;
        justify-content: flex-end;
        align-items: stretch;

        a {
            @include button-style(0.83em, 400, initial);

            height: 100%;
            margin-left: 3px;
        }
    }

    .banner {
        margin-bottom: 6px;
        background: #e7bacc !important;
    }
}

#mush-tab .unit > .message:nth-of-type(odd) {
    flex-direction: row-reverse;

    .char-portrait { align-items: flex-end; }

    .timestamp { right: 41px; }

    p::before {
        left: initial;
        right: -8px;
        transform: rotate(180deg);
    }

    &.new p::before { border-right-color: white; }
}

</style>
