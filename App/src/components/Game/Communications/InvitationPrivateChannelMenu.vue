<template>
    <GamePopUp v-if="invitablePlayerMenuOpen" title="Inviter" @exit="closeInvitation">
        <div class="invite-selection">
            <button v-for="(player, key) in invitablePlayers" :key="key" @click="invitePlayer({player: player, channel: invitationChannel})">
                <img :src="characterBody(player.characterKey)">
                <p>name</p>
            </button>
        </div>
    </GamePopUp>
</template>

<script>
import GamePopUp from "@/components/Utils/GamePopUp";
import { characterEnum } from "@/enums/character";
import { mapActions, mapGetters } from "vuex";
export default {
    name: "InvitationPrivateChannelMenu",
    components: { GamePopUp },
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
    }
};
</script>

<style lang="scss" scoped>

.invite-selection {
    flex-flow: row wrap;

    button {

        margin: 0 .1em;
        padding: .2em;
        border-radius: 3px;
        transition: all 0.15s;

        p {
            color: white;
            margin: auto .2em auto .4em;
        }

        &:hover, &:focus, &:active { background-color: #17448E; }
    }

}

</style>
