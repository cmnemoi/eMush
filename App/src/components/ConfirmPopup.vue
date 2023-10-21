<template>
    <PopUp :is-open=confirmPopup.isOpen @close="closeConfirmPopup">
        <h1 class="title">
            Attention
        </h1>
        <p>Vous êtes sur le point d'effectuer une action importante ou irrémédiable. Continuer ?</p>
        <div class="actions">
            <button class="action-button" @click="acceptConfirmPopup()">OK</button>
        </div>
    </PopUp>
</template>

<script lang="ts">
import { mapState, mapActions } from "vuex";
import PopUp from "@/components/Utils/PopUp.vue";
import { defineComponent } from "vue";

export default defineComponent ({
    components: {
        PopUp
    },
    computed: {
        ...mapState('player', [
            'confirmPopup'
        ])
    },
    methods: {
        ...mapActions('player', [
            'closeConfirmPopup',
            'acceptConfirmPopup'
        ]),
    }
});
</script>

<style lang="scss" scoped>

.actions {
    flex-direction: row;
    align-self: center;

     button, a {
        min-width: 160px;
     }
}

</style>
