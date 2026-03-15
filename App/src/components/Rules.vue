<template>
    <NewRulesPopUp />
    <div class="box-container">
        <div class="paragraph">
            <h1>{{ $t("rules.title") }}</h1>
            <p class="text"> {{ $t( "rules.introduction.textIntro" ) }} </p>
            <p class="text" v-html="$t('rules.introduction.lastUpdated')"></p>
        </div>

        <div class="paragraph">
            <h2>{{ $t("rules.communication.title") }}</h2>
            <p class="text"> {{ $t("rules.communication.introText") }} </p>
            <ul class="rules">
                <li class="text"> <span v-html="$t('rules.communication.mandatoryCommunication')"></span></li>
                <li class="text"> <span v-html="$t('rules.communication.courtesy')"></span></li>
                <li class="text"> <span v-html="$t('rules.communication.spokenLanguage')"></span></li>
                <li class="text"> <span v-html="$t('rules.communication.flood')"></span></li>
                <li class="text"> <span v-html="$t('rules.communication.metagaming')"></span></li>
            </ul>
        </div>

        <div class="paragraph">
            <h2>{{ $t("rules.behaviour.title") }}</h2>
            <p class="text"> {{ $t("rules.behaviour.introText") }} </p>
            <ul class="rules">
                <li class="text"><span v-html="$t('rules.behaviour.multiAccounts')"></span></li>
                <li class="text"><span v-html="$t('rules.behaviour.suicide')"></span></li>
                <li class="text"><span v-html="$t('rules.behaviour.spoilingHuman')"></span></li>
                <li class="text"><span  v-html="$t('rules.behaviour.spoilingMush')"></span></li>
                <li class="text"><span  v-html="$t('rules.behaviour.mushCooperation')"></span></li>
            </ul>
        </div>

        <div class="paragraph sanctionsAppeal">
            <h2>{{ $t("rules.sanctionsAppeal.title") }}</h2>
            <p class="text"> {{ $t("rules.sanctionsAppeal.sanctionText") }} </p>
            <p class="text" v-html="$t('rules.sanctionsAppeal.appealText')"></p>
            <p class="text"> {{ $t("rules.sanctionsAppeal.allPlatformText") }} </p>
        </div>

        <div class="beta-leeway">
            <h2><img class="alpha" width="20" :src="getImgUrl('ui_icons/action_points/pa_core.png')"> {{ $t("rules.alphaRules.title") }}</h2>
            <p class="text"> {{ $t("rules.alphaRules.introText") }} </p>
            <p class="text" v-html="$t('rules.alphaRules.tests')"/>
            <p class="text"> {{ $t("rules.alphaRules.endText") }} </p>
        </div>
        <div class="flex-row" v-if="userConnected && !hasAcceptedRules">
            <input type="checkbox" v-model="hasReadRules" class="checkbox" />
            <label for="hasReadRules" class="text" @click="hasReadRules = !hasReadRules">
                {{ $t("rules.iHaveReadRules") }}
            </label>
        </div>
        <div class="flex-row justify-center">
            <button class="action-button" @click="acceptRulesAndRedirectToHomePage" v-if="hasReadRules">
                {{ $t("rules.accept") }}
            </button>
        </div>
    </div>
</template>

<script lang="ts">
import { getImgUrl } from "@/utils/getImgUrl";
import { defineComponent } from "vue";
import { mapActions, mapGetters } from "vuex";
import NewRulesPopUp from "./Utils/NewRulesPopUp.vue";

export default defineComponent ({
    name: 'Rules',
    components: {
        NewRulesPopUp
    },
    props: {
        error: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            hasReadRules: false
        };
    },
    computed: {
        ...mapGetters({
            userConnected: 'auth/getUserInfo',
            hasAcceptedRules: 'auth/hasAcceptedRules'
        })
    },
    methods: {
        ...mapActions({
            'acceptRules': 'auth/acceptRules',
            'loadHasAcceptedRules': 'auth/loadHasAcceptedRules',
            'openSuccessToast': 'toast/openSuccessToast',
            'openNewRulesPopUp': 'popup/openNewRulesPopUp'
        }),
        acceptRulesAndRedirectToHomePage() {
            this.acceptRules();
            this.$router.push({ name: 'HomePage' });
            this.openSuccessToast(this.$t('rules.thanks'));
        },
        getImgUrl
    },
    async beforeMount() {
        await this.loadHasAcceptedRules();
        if (this.userConnected && !this.hasAcceptedRules) {
            this.openNewRulesPopUp();
        }
    }
});

</script>

<style scoped lang="scss">
@use "sass:color";
:deep(strong) {
    color: $cyan;
}

:deep(em) {
    color: $red;
}

:deep(a) {
    color: $green;
}

.text {
    margin:5px;
    font-size:1.2em;
}

.rules {
    list-style-type: disc;
    display: block;
    margin-left: 20px;
}

.beta-leeway {
    margin: 1em 0;
    padding: .3em .8em;
    background: color.adjust($red, $alpha: -0.7);
    border: 1px solid $red;
    border-radius: 6px;
    font-style: italic;
}
</style>
