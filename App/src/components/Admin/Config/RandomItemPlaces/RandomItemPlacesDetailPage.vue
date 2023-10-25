<template>
    <div v-if="randomItemPlaces" class="center">
        <h2>{{ $t('admin.randomItemPlaces.pageTitle') }} <em>{{ randomItemPlaces.name }}</em></h2>
        <div class="flex-row wrap">
            <Input
                :label="$t('admin.randomItemPlaces.name')"
                id="randomItemPlaces_name"
                v-model="randomItemPlaces.name"
                type="text"
                :errors="errors.name"
            ></Input>
        </div>
        <h3>{{ $t('admin.randomItemPlaces.items') }}</h3>
        <StringArrayManager
            :array="randomItemPlaces.items"
            id="randomItemPlaces_items"
            @addElement="randomItemPlaces.items.push($event)"
            @removeElement="randomItemPlaces?.items.splice(randomItemPlaces?.items.indexOf($event), 1)"
        ></StringArrayManager>
        <h3>{{ $t('admin.randomItemPlaces.places') }}</h3>
        <StringArrayManager
            :array="randomItemPlaces.places"
            id="randomItemPlaces_places"
            @addElement="randomItemPlaces.places.push($event)"
            @removeElement="randomItemPlaces.places.splice(randomItemPlaces.places.indexOf($event), 1)"
        ></StringArrayManager>
        <UpdateConfigButtons @create="create" @update="update"/>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { RandomItemPlaces } from "@/entities/Config/RandomItemPlaces";
import { handleErrors } from "@/utils/apiValidationErrors";
import Input from "@/components/Utils/Input.vue";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";
import StringArrayManager from "@/components/Utils/StringArrayManager.vue";
import urlJoin from "url-join";

interface RandomItemPlacesState {
    randomItemPlaces: null|RandomItemPlaces
    errors: any
}

export default defineComponent({
    name: "RandomItemPlacesDetailPage",
    components: {
        Input,
        UpdateConfigButtons,
        StringArrayManager,

    },
    data: function (): RandomItemPlacesState {
        return {
            randomItemPlaces: null,
            errors: {}
        };
    },
    methods: {
        create(): void {
            if (this.randomItemPlaces === null) return;

            const newRandomItemPlaces = this.randomItemPlaces;
            newRandomItemPlaces.id = null;

            GameConfigService.createRandomItemPlaces(newRandomItemPlaces)
                .then((res: RandomItemPlaces | null) => {
                    const newRandomItemPlacesUrl = urlJoin(process.env.VUE_APP_URL+ '/config/random-item-places', String(res?.id));
                    window.location.href = newRandomItemPlacesUrl;
                })
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
                });
        },
        update(): void {
            if (this.randomItemPlaces === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateRandomItemPlaces(this.randomItemPlaces)
                .then((res: RandomItemPlaces | null) => {
                    this.randomItemPlaces = res;
                })
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
                });
        }
    },
    beforeMount() {
        const randomItemPlacesId = Number(this.$route.params.randomItemPlacesId);
        GameConfigService.loadRandomItemPlaces(randomItemPlacesId).then((res: RandomItemPlaces | null) => {
            this.randomItemPlaces = res;
        });
    }
});
</script>

<style lang="scss" scoped>

</style>
