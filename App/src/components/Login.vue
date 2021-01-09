<template>
    <div>
        <a v-if="!loggedIn" @click="openPopup">Login</a>
        <a v-if="loggedIn" @click="logout">Logout</a>
        <PopUp :is-open="isPassphrasePopupOpen" @close="closePopup">
            <span>Ceci est une alpha reservé aux testeurs</span>
            <span>This is an alpha for tester only</span>
            <label for="passphrase" class="passphrase">Passphrase:</label>
            <input id="passphrase" v-model="passphrase" type="text">
            <button type="submit" @click="handleSubmit">
                {{ ('form.submit') }}
            </button>
        </PopUp>
    </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";
import PopUp from "@/components/Utils/PopUp";

export default {
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
        handleSubmit() {
            // Perform a simple validation that email and password have been typed in
            if (this.passphrase !== '') {
                this.submitted = true;
                this.redirect({ passphrase: this.passphrase });
                this.passphrase = "";
            }
            this.isPassphrasePopupOpen = false;
        },
        openPopup() {
            this.isPassphrasePopupOpen = true;
        },
        closePopup() {
            this.isPassphrasePopupOpen = false;
        }
    }
};
</script>

<style lang="scss" scoped>
a {
    margin: 0 20px;
    padding: 5px 10px;
    color: white;

    &:hover,
    &:active {
        color: #dffaff;
        text-shadow: 0 0 1px rgb(255, 255, 255), 0 0 1px rgb(255, 255, 255);
    }
}

.passphrase {
    margin: 0 0 15px;
    font-size: 150%;
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
    @include button-style(1em);

    margin: 7px 0;
    padding-top: 4px;
    padding-bottom: 6px;
    border: 0;
}

</style>



