<template>
    <div v-if="user" class="user_detail">
        <h2 class="user_detail_title">
            User: <em>{{ user.userId }}</em>
        </h2>
        <div class="flex-row wrap">
            <Input
                :label="$t('admin.user.username')"
                id="user_username"
                v-model="user.username"
                type="text"
            />
        </div>
        <div class="select-default">
            <label for="user_roles">{{ $t('admin.user.roles') }}</label>
            <select v-model="user.roles" :size="rolesOption.length" multiple>
                <option v-for="option in rolesOption" :value="option.key" :key="option.key">
                    {{ option.text }}
                </option>
            </select>
            <ErrorList v-if="errors.roles" :errors="errors.roles"></ErrorList>
        </div>
        <UpdateConfigButtons :create="false" @update="update"/>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import UserService from "@/services/user.service";
import { User } from "@/entities/User";
import { UserRole } from "@/enums/user_role.enum";
import { handleErrors } from "@/utils/apiValidationErrors";
import ErrorList from "@/components/Utils/ErrorList.vue";
import Input from "@/components/Utils/Input.vue";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";

interface UserDetailData {
    user: User | null,
    errors: any,
    rolesOption: object[],
}

export default defineComponent({
    name: "UserDetailPage",
    components: {
        Input,
        ErrorList,
        UpdateConfigButtons
    },
    data() : UserDetailData {
        return {
            user: null,
            errors: {},
            rolesOption: [
                {
                    key: UserRole.USER,
                    text: 'User'
                },
                {
                    key: UserRole.MODERATOR,
                    text: 'Moderator'
                },
                {
                    key: UserRole.ADMIN,
                    text: 'Admin'
                },
                {
                    key: UserRole.SUPER_ADMIN,
                    text: 'Super Admin'
                },
                {
                    key: 'error',
                    text: 'Super Admin error'
                }
            ]
        };
    },
    methods: {
        update(): void {
            UserService.updateUser(this.user)
                .then((result: User) => (this.user = result))
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
            ;
        }
    },
    beforeMount() {
        const userId = this.$route.params.userId;
        if (typeof userId === 'string') {
            UserService.loadUser(userId)
                .then((result: User) => (this.user = result))
            ;
        }
    }
});
</script>

<style lang="scss" scoped>

h2 { font-size: 1.6em; }

h3 { font-size: 1.3em; }

h2, h3 {
    margin: 2.8em 0 0.6em;

    &:first-child { margin-top: 0.6em; }
}

.select-default {
    padding: 1.8em 0;

    label {
        padding: 0 0.8em;
        transform: translateY(0.45em);
        word-break: break-word;
    }

    select {
        padding-top: 0.2em;
        font-size: 1.3em;
        color: white;
        background: #222b6b;
        border: 1px solid transparentize(white, 0.8);
        border-radius: 1px;

        &:focus {
            outline: none;
            box-shadow: 0 0 0 3px transparentize(white, 0.85);
        }

        & option:checked {
            background: transparentize($blue, 0.7);
            font-weight: bold;
        }
    }

    option {
        color: white;
        padding: 0.5em 0.8em;
        background: #222b6b;
    }
}

.select-default > * {
    width: 31%;
    min-width: 200px;
    height: fit-content;
}



</style>
