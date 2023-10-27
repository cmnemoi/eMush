<template>
    <div class="box-container">
        <div v-if="code" class="flex-row wrap">
            <a class="cookie-link" href="javascript:(function(){let e=function e(i){let t=`; ${document.cookie}`,o=t.split('; sid=');if(2===o.length)return o.pop().split(';').shift()}('sid');if(e){let i=document.createElement('input');document.body.appendChild(i),i.value=e,i.focus(),i.select(),document.execCommand('copy'),i.remove(),alert('SID copied to clipboard')}})();">{{ $t('import.frcookie') }}</a>
            <a class="cookie-link" href="javascript:(function(){let e=function e(i){let t=`; ${document.cookie}`,o=t.split('; mush_sid=');if(2===o.length)return o.pop().split(';').shift()}('mush_sid');if(e){let i=document.createElement('input');document.body.appendChild(i),i.value=e,i.focus(),i.select(),document.execCommand('copy'),i.remove(),alert('SID copied to clipboard')}})();">{{ $t('import.encookie') }}</a>
        </div>
        <div class="flex-row wrap">
            <Input
                v-if="code"
                :label="$t('sid')"
                id="sid"
                v-model="sid"
                type="text"
            />
            <button v-if="!code || legacyUser" class="button" @click="connectToTwinoid()" >{{ $t("import.connectToTwinoid") }}</button>
            <button v-if="sid && code && !legacyUser" class="button" @click="importMyUser(sid, code, 'fr')" >{{ $t("import.importFrenchData") }}</button>
            <button v-if="sid && code && !legacyUser" class="button" @click="importMyUser(sid, code, 'en')" >{{ $t("import.importEnglishData") }}</button>
        </div>
        <div v-if="legacyUser" class="flew-row" >
            <p>{{ $t("import.success", {id: legacyUser.twinoidId, username: legacyUser.twinoidUsername, nbShips: legacyUser.historyShips.length}) }}</p>
            <p>{{ $t("import.successInfo") }}</p>
            <pre v-html="legacyUser.jsonEncode()"></pre>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import ImportService from "@/services/import.service";
import Input from "@/components/Utils/Input.vue";
import { LegacyUser } from "@/entities/LegacyUser";
import { mapGetters, mapActions } from "vuex";

interface LegacyUserState {
    channel: string;
    errors: any,
    legacyUser: LegacyUser | null;
    sid: string;
}

export default defineComponent ({
    name: "UserImport",
    components: {
        Input
    },
    computed: {
        ...mapGetters('twinoidImport', [
            'code',
        ])
    },
    methods: {
        ...mapActions('twinoidImport', [
            'updateCode',
        ]),
        connectToTwinoid() {
            this.getTwinoidOauthCode();
        },
        async importMyUser(sid: string, code: string, serverLanguage: string) {
            if (!sid || !code) {
                this.errors = {
                    sid: [this.$t('import.sidRequired')],
                    code: [this.$t('import.codeRequired')],
                };
                console.error(this.errors);
            }

            await ImportService.importMyProfile(sid, code, serverLanguage).then((response) => {
                this.legacyUser = (new LegacyUser()).load(response.data);
            }).catch((error) => {
                this.errors = {
                    error: [error],
                };
                console.error(error);
            });
        },
        getTwinoidOauthCode() {
            const responseType = "code";
            const clientId = this.getClientId();
            const redirectUri = this.getRedirectUri();
            const scope = "mush.twinoid.com+mush.twinoid.es+mush_ship_data+mush.vg+groups";
            const state = "auth";

            const url = `https://twinoid.com/oauth/auth?response_type=${responseType}&client_id=${clientId}&redirect_uri=${redirectUri}&scope=${scope}&state=${state}`;

            window.open(url, '_self');
        },
        getClientId() {
            console.log(this.channel);
            switch (this.channel) {
            case 'dev':
                return 407;
            case 'emush.staging':
                return 429;
            case 'emush.production':
                return 430;
            default:
                throw new Error('Unknown release channel');
            }
        },
        getRedirectUri() {
            switch (this.channel) {
            case 'dev':
                return 'http://localhost/import';
            case 'emush.staging':
                return 'https://staging.emush.eternaltwin.org/import';
            case 'emush.production':
                return 'https://emush.eternaltwin.org/import';
            default:
                throw new Error('Unknown release channel');
            }
        },
    },
    data: function (): LegacyUserState {
        return {
            channel: process.env.VUE_APP_API_RELEASE_CHANNEL as string,
            errors: null as any,
            legacyUser: null,
            sid : "",
        };
    },
    beforeMount() {
        const codeRegex = window.location.search.match(/code=(.*)/);
        if (codeRegex) {
            this.updateCode(codeRegex[1]);
        }
    }
});

</script>

<style lang="scss" scoped>
.button  {
    @include button-style();
}

.cookie-link {
    color: $green;
}

</style>
