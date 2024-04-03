<template>
    <PopUp :is-open=reportPopup.isOpen @close="closeReportPopup()" v-if="user && player">
        <h1 class="title">{{ $t('reportPopup.title') }}</h1>
        <p class="message" v-html="formatText($t('reportPopup.message', {username: user.username, daedalusId: player.daedalus.id}))"></p>
        <div class="actions">
            <button class="action-button" @click="closeReportPopup()">{{ $t('reportPopup.ok') }}</button>
        </div>
    </PopUp>
</template>

<script lang="ts">
import { mapActions, mapGetters } from "vuex";
import PopUp from "@/components/Utils/PopUp.vue";
import { defineComponent } from "vue";
import { formatText } from "@/utils/formatText";

export default defineComponent ({
    components: {
        PopUp
    },
    computed: {
        ...mapGetters({
            user: 'auth/getUserInfo',
            player: 'player/player',
            reportPopup: 'popup/reportPopup'
        })
    },
    methods: {
        ...mapActions('popup', [
            'closeReportPopup'
        ]),
        formatText
    }
});
</script>

<style lang="scss" scoped>
.message {
    :deep(a) {
        color: $green;
        text-decoration: none;
        &:hover, &:focus, &:active { color: white; }
    }
}

.actions {
    flex-direction: row;
    align-self: center;

     button, a {
        min-width: 160px;
     }
}

</style>
