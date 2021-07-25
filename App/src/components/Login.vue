<template>
    <div>
        <a v-if="! loggedIn" class="login-button" @click="openPopup">{{ $t('login') }}</a>
        <a v-if="loggedIn" class="logout-button" @click="logout">{{ $t('logout') }}</a>
        <PopUp :is-open="isPassphrasePopupOpen" @close="closePopup">
            <span>{{ $t('alpha.description') }}</span>
            <label for="passphrase" class="passphrase">{{ $t('alpha.passphrase') }}</label>
            <input
                id="passphrase"
                ref="passphrase_input"
                v-model="passphrase"
                type="text"
                @keyup.enter="submitPassphrase"
            >
            <button type="submit" @click="submitPassphrase">
                {{ $t('alpha.valide') }}
            </button>
        </PopUp>
    </div>
</template>

<script lang="ts">
import { mapActions, mapGetters } from "vuex";
import PopUp from "@/components/Utils/PopUp.vue";
import { defineComponent } from "vue";

export default defineComponent ({
    name: 'Login',
    components: {
        PopUp
    },
    data() {
        return {
            isPassphrasePopupOpen: false,
            passphrase: "",
            loginError: null
        };
    },
    computed: {
        ...mapGetters('auth', [
            'loggedIn'
        ])
    },
    methods: {
        ...mapActions('auth', [
            'redirect',
            'logout'
        ]),
        submitPassphrase(): void {
            if (this.passphrase !== "") {
                this.redirect({ passphrase: this.passphrase });
                this.passphrase = "";
            }
            this.closePopup();
        },
        async openPopup(): Promise<void> {
            this.isPassphrasePopupOpen = true;
            await this.$nextTick;
            const ref: HTMLElement = this.$refs.passphrase_input as HTMLHtmlElement;
            ref.focus();
        },
        closePopup(): void {
            this.isPassphrasePopupOpen = false;
        }
    }
});
</script>

<style lang="scss" scoped>
.login-button,
.logout-button {
    cursor: pointer;
    margin: 0 20px;
    padding: 5px 10px;
    color: white;
    font-size: 1.1em;

    &:hover,
    &:active {
        color: #dffaff;
        text-shadow: 0 0 1px rgb(255, 255, 255), 0 0 1px rgb(255, 255, 255);
    }
}

.modal-box { font-size: 1em; }

.passphrase {
    margin-top: 15px;
    font-size: 1.4em;
    font-weight: 700;
    font-variant: small-caps;
}

input {
    margin: 7px 0;
    padding: 5px 8px;
    font-style: italic;
    opacity: 0.85;
    box-shadow: 0 1px 0 rgba(255, 255, 255, 0.3);
    border: 1px solid #aad4e5;
    border-radius: 3px;

    &:active,
    &:focus {
        font-style: initial;
        opacity: 1;
    }
}

button {
    cursor: pointer;

    @include button-style(1em);

    margin: 7px 0;
    padding-top: 4px;
    padding-bottom: 6px;
    border: 0;
}

</style>



