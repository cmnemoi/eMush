<template>
    <div class="daedalus_detail">
        <div class="daedalus_detail_title">
            Daedalus:
        </div>
        <div class="daedalus_detail_content">
            <div class="flex-row">
                <div class="grow">
                    <label for="daedalus_name">{{ $t('daedalus.name') }}</label>
                    <input
                        id="daedalus_name"
                        ref="daedalus_name"
                        v-model="name"
                        type="text"
                    >
                    <ErrorList v-if="errors.name" :errors="errors.name"></ErrorList>
                </div>
            </div>
        </div>
        <button class="action-button" type="submit" @click="save">
            {{ $t('save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import DaedalusService from "@/services/daedalus.service";
import ErrorList from "@/components/Utils/ErrorList.vue";
import { handleErrors } from "@/utils/apiValidationErrors";
import store from "@/store";

export default defineComponent({
    name: "DaedalusDetailPage",
    components: {
        ErrorList
    },
    data() {
        return {
            name: '',
            errors: {}
        };
    },
    methods: {
        save(): void {
            store.dispatch('gameConfig/setLoading', { loading: true });
            DaedalusService.createDaedalus(this.name)
                .then(() => (this.$router.push({ name: 'AdminDaedalusList' })))
                .catch((error) => {
                    if (error.response) {
                        if (error.response.data.violations) {
                            this.errors = handleErrors(error.response.data.violations);
                        }
                    } else if (error.request) {
                        // The request was made but no response was received
                        console.error(error.request);
                    } else {
                        // Something happened in setting up the request that triggered an Error
                        console.error('Error', error.message);
                    }
                })
                .finally(() => store.dispatch('gameConfig/setLoading', { loading: false }))
            ;
        },
    },
});
</script>

<style lang="scss" scoped>

</style>
