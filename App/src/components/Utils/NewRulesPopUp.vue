<template>
    <PopUp :is-open=newRulesPopup.isOpen @close="closeNewRulesPopup()" class="pop-up">
        <h1 class="title">{{ $t('rules.newRulesPopUp.title') }}</h1>
        <p class="message" v-html="formatText($t('rules.newRulesPopUp.message'))"></p>
        <div class="actions">
            <button class="action-button" @click="closeNewRulesPopup()">{{ $t('rules.newRulesPopUp.ok') }}</button>
        </div>
    </PopUp>
</template>

<script lang="ts">
import { mapActions, mapGetters } from "vuex";
import PopUp from "@/components/Utils/PopUp.vue";
import { defineComponent } from "vue";
import { formatText } from "@/utils/formatText";

export default defineComponent({
    name: 'NewRulesPopUp',
    components: {
        PopUp
    },
    computed: {
        ...mapGetters({
            newRulesPopup: 'popup/newRulesPopUp'
        })
    },
    methods: {
        ...mapActions({
            closeNewRulesPopup: 'popup/closeNewRulesPopUp'
        }),
        formatText
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
