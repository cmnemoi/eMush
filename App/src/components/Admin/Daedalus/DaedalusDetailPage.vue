<template>
    <div class="daedalus_detail">
        <h1 class="daedalus_detail_title">
            {{$t('admin.daedalus.createDaedalus')}}
        </h1>
        <div class="daedalus_detail_content">
            <label for="daedalus_name">{{ $t('admin.daedalus.name') }}</label>
            <input
                id="daedalus_name"
                ref="daedalus_name"
                v-model="name"
                type="text"
            >
            <ErrorList v-if="errors.name" :errors="errors.name"></ErrorList>
        </div>
        <button class="action-button" type="submit" @click="save">
            {{ $t('admin.save') }}
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
        }
    }
});
</script>

<style lang="scss" scoped>

.daedalus_detail {
    width: 35%;
    min-width: 338px;
}

.daedalus_detail_content {
    margin-bottom: 1.6em;
}

button {
    @include button-style();
    padding: 2px 15px 4px;
}

</style>
