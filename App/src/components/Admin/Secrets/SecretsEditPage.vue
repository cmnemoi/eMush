<template>
    <div class="flex-row">
        <Input v-if="$route.params.secret" :label="$route.params.secret" v-model="secret.value" />
        <div v-else>
            <Input label="Secret name" v-model="secret.name" />
            <Input label="Secret value" v-model="secret.value" />
        </div>
        <button class="action-button"
                :disabled="secret.value === ''"
                type="submit"
                @click="updateSecret()">
            {{ $t('admin.save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import Input from "@/components/Utils/Input.vue";
import AdminService from "@/services/admin.service";

export default defineComponent({
    name: "SecretsEditPage",
    components: {
        Input
    },
    data() {
        return {
            secret: {
                name: '',
                value: '',
            },
        };
    },
    methods: {
        async updateSecret() {
            const secretName = this.$route.params.secret;
            if (typeof secretName == 'string') {
                this.secret.name = secretName;
            }
            console.log(this.secret);
            return await AdminService.editSecret({'name': this.secret.name, 'value': this.secret.value}).then((response) => {
                this.$router.push({ name: 'AdminSecretsList' });
            });
        }
    },
});
</script>

<style lang="scss" scoped>
</style>
