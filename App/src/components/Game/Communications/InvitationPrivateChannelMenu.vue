<template>
    <GamePopUp v-if="invitablePlayerMenuOpen" @exit="closeInvitation" title="Inviter">
        <ul>
            <li v-for="(player, key) in invitablePlayers" :key="key">
                <img :src="characterBody(player.characterKey)" @click="invitePlayer({player: player, channel: invitationChannel})">
            </li>
        </ul>
    </GamePopUp>
</template>

<script>
import GamePopUp from "@/components/Utils/GamePopUp";
import {characterEnum} from "@/enums/character";
import {mapActions, mapGetters} from "vuex";
export default {
    name: "InvitationPrivateChannelMenu",
    components: {GamePopUp},
    computed: {
        ...mapGetters('communication', [
            'invitationChannel',
            'invitablePlayerMenuOpen',
            'invitablePlayers'
        ])
    },
    methods: {
        characterBody: function(character) {
            const images = characterEnum[character];
            return images.body;
        },
        ...mapActions('communication', [
            'invitePlayer',
            'closeInvitation'
        ])
    },
}
</script>

<style scoped>

</style>