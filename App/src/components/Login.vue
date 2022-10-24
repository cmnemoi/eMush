<template>
    <div>
        <a v-if="!loggedIn" class="login-button" @click="redirectToLogin">{{ $t('login') }}</a>
        <a v-else class="logout-button" @click="logout">{{ $t('logout') }}</a>
    </div>
</template>

<script lang="ts">
import { mapActions, mapGetters } from "vuex";
import { defineComponent } from "vue";

export default defineComponent ({
    name: 'Login',
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
            'redirectToLogin',
            'logout'
        ])
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
    letter-spacing: .06em;

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



