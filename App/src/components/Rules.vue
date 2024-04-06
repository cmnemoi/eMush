<template>
    <div class="box-container">
        <div class="paragraph">
            <h1 class="mainTitle">{{ $t("rules.title") }}</h1>
            <p class="text"> {{ $t( "rules.introduction.textIntro" ) }} </p>
            <div class="alphaLeeway">
                <span>
                    <img class="alpha" src="@/assets/images/pa_core.png">
                    {{ $t( "rules.introduction.alphaLeeway" ) }}
                </span>
            </div>
            <p class="text" v-html="$t('rules.introduction.lastUpdated')"></p>
        </div>

        <div class="paragraph">
            <h2 class="subtitle">{{ $t("rules.communication.title") }}</h2>
            <p class="text"> {{ $t("rules.communication.introText") }} </p>
            <ul class="rulesList">
                <li class="rule"> <span v-html="$t('rules.communication.courtesy')"></span></li>
                <li class="rule"><img class="alpha" src="@/assets/images/pa_core.png"> <span v-html="$t('rules.communication.spokenLanguage')"></span></li>
            </ul>
        </div>
       
        <div class="paragraph">
            <h2 class="subtitle">{{ $t("rules.behaviour.title") }}</h2>
            <p class="text"> {{ $t("rules.behaviour.introText") }} </p>
            <ul class="rulesList">
                <li class="rule"> <span v-html="$t('rules.behaviour.multiAccounts')"></span></li>
                <li class="rule"> <span v-html="$t('rules.behaviour.suicide')"></span></li>
                <li class="rule"><img class="alpha" src="@/assets/images/pa_core.png"> <span v-html="$t('rules.behaviour.spoilingHuman')"></span></li>
                <li class="rule"><img class="alpha" src="@/assets/images/pa_core.png"> <span  v-html="$t('rules.behaviour.spoilingMush')"></span></li>
            </ul>
        </div>
       
        <div class="paragraph sanctionsAppeal">
            <h2 class="subtitle">{{ $t("rules.sanctionsAppeal.title") }}</h2>
            <p class="text"> {{ $t("rules.sanctionsAppeal.sanctionText") }} </p>
            <p class="text" v-html="$t('rules.sanctionsAppeal.appealText')"></p>
            <p class="text"> {{ $t("rules.sanctionsAppeal.allPlatformText") }} </p>
        </div>

        <div class="alphaLeeway">
            <h2 class="subtitle"><img class="alpha" src="@/assets/images/pa_core.png"> {{ $t("rules.alphaRules.title") }}</h2>
            <p class="text"> {{ $t("rules.alphaRules.introText") }} </p>
            <ul class="rulesList">
                <li class="rule" v-html="$t('rules.alphaRules.spokenLanguage')"></li>
                <li class="rule" v-html="$t('rules.alphaRules.cooperation')"></li>
            </ul>
            <p class="text"> {{ $t("rules.alphaRules.endText") }} </p>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent ({
    name: 'Rules',
    props: {
        error: {
            type: Object,
            default: null
        }
    }
});

</script>

<style scoped lang="scss">
.box-container {
    &::v-deep(strong) {
        color: $cyan;
    }

    &::v-deep(em) {
        color: $red;
    }

    &::v-deep(a) {
        color: $green;
    }
}

.box-container {
    font-size:1.2em;
}

.mainTitle {
    font-size:2.2em;
}

.title {
    font-size:1.5em;
}

.text {
    margin:5px;
}

.alphaLeeway {
    margin: 1em 0;
    padding: .3em .8em;
    background: transparentize($red, .7);
    border: 1px solid $red;
    border-radius: 6px;
    font-style: italic;
}

.rulesList {
    list-style-type: disc;
    display: block;
    margin-left: 20px;
}

.rule {
    margin: 5px;
}

.alpha {
    width:20px;
    display:inline;
    margin-right:20px;
}

.alphaRule {
    
}

</style>
