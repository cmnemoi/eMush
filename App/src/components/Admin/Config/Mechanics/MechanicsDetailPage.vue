<template>
    <div v-if="mechanics" class="center">
        <h2>{{ $t('admin.mechanics.pageTitle') }} {{ mechanics.name }}</h2>
        
        <div class="flex-row">
            <Input
                :label="$t('admin.mechanics.name')"
                id="mechanics_name"
                v-model="mechanics.name"
                type="text"
                :errors="errors.name"
            ></Input>
        </div>
        
        <h3>{{ $t('admin.mechanics.mechanics') }}</h3>
        <StringArrayManager
            :array="mechanics.mechanics"
            @addElement="mechanics.mechanics.push($event)"
            @removeElement="mechanics?.mechanics.splice(mechanics?.mechanics.indexOf($event), 1)"
        ></StringArrayManager>
        
        <h3>{{ $t('admin.mechanics.actions') }}</h3>
        <ChildCollectionManager :children="mechanics.actions" @addId="selectNewAction" @remove="removeAction">
            <template #header="child">
                <span>{{ child.id }} - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        
        <div v-if="mechanics.mechanicsType == 'Blueprint'">
            <h3>{{ $t('admin.mechanics.ingredients') }}</h3>
            <pre>{{ mechanics.ingredients }}</pre>
            <label for="ingredients">{{ $t('admin.mechanics.addIngredients') }}</label>
            <div class="flex-row">
                <select v-model="ingredientToAdd">
                    <option v-for="ingredient in ingredients" :value="ingredient" v-bind:key="ingredient">{{ ingredient }}</option>
                </select>
                <button class="action-button" @click="addIngredient(ingredientToAdd)">{{ $t("admin.buttons.add") }}</button>
                <button class="action-button" @click="removeIngredient(ingredientToAdd)">{{ $t("admin.buttons.delete") }}</button>
            </div>
        </div>

        <div v-if="mechanics.mechanicsType == 'Document'">
            <h3> {{ $t('admin.mechanics.content') }}</h3>
            <textarea v-model="mechanics.content"></textarea>
            
            <div class="flex-row">
                <span id="booleans">
                    <input type="checkbox"
                           class="mechanicsCheckbox"
                           id="isTranslated"
                           v-model="mechanics.isTranslated" />
                    <label for="isTranslated">{{ mechanics.isTranslated ? $t('admin.mechanics.isTranslated') : $t('admin.mechanics.isNotTranslated') }}</label>
                    
                    <input type="checkbox"
                           class="mechanicsCheckbox"
                           id="canShred"
                           v-model="mechanics.canShred" />
                    <label for="canShred">{{ mechanics.canShred ? $t('admin.mechanics.canShred') : $t('admin.mechanics.cannotShred') }}</label>
                </span>
            </div>
        </div>
        
        <button class="action-button" type="submit" @click="update">
            {{ $t('admin.save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import ActionService from "@/services/action.service";
import GameConfigService from "@/services/game_config.service";
import ApiService from "@/services/api.service";
import { Action } from "@/entities/Action";
import { EquipmentConfig } from "@/entities/Config/EquipmentConfig";
import { Mechanics } from "@/entities/Config/Mechanics";
import { handleErrors } from "@/utils/apiValidationErrors";
import Input from "@/components/Utils/Input.vue";
import StringArrayManager from "@/components/Utils/StringArrayManager.vue";
import ChildCollectionManager from "@/components/Utils/ChildcollectionManager.vue";
import urlJoin from "url-join";
import { removeItem } from "@/utils/misc";

interface MechanicsState {
    mechanics: null|Mechanics
    errors: any,
    ingredients: string[],
    ingredientToAdd: string
}

export default defineComponent({
    name: "MechanicsDetailPage",
    components: {
        ChildCollectionManager,
        Input,
        StringArrayManager
    },
    data: function (): MechanicsState {
        return {
            mechanics: null,
            errors: {},
            ingredients: ["metal_scraps", "plastic_scraps", "soap", "old_t_shirt", "thick_tube", "oxygen_capsule", "fuel_capsule"],
            ingredientToAdd: ""
        };
    },
    methods: {
        update(): void {
            if (this.mechanics === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateMechanics(this.mechanics)
                .then((res: Mechanics | null) => {
                    this.mechanics = res;
                    if (this.mechanics !== null) {
                        ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'mechanics', String(this.mechanics.id), 'actions'))
                            .then((result) => {
                                const actions : Action[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const currentAction = (new Action()).load(datum);
                                    actions.push(currentAction);
                                });
                                if (this.mechanics instanceof Mechanics) {
                                    this.mechanics.actions = actions;
                                }
                            });
                    }
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
        selectNewAction(selectedId: any) {
            ActionService.loadAction(selectedId).then((res) => {
                if (res && this.mechanics && this.mechanics.actions) {
                    this.mechanics.actions.push(res);
                }
            });
        },
        removeAction(child: any) {
            if (this.mechanics && this.mechanics.actions) {
                this.mechanics.actions = removeItem(this.mechanics.actions, child);
            }
        },
        addIngredient(product: string): void {
            if (this.mechanics && this.mechanics.ingredients) {
                if (this.mechanics.ingredients.get(product) !== undefined) {
                    this.mechanics.ingredients.set(product, this.mechanics.ingredients.get(product) + 1);
                } else {
                    this.mechanics.ingredients.set(product, 1);
                }
            }
        },
        removeIngredient(product: string): void {
            if (this.mechanics && this.mechanics.ingredients) {
                if (this.mechanics.ingredients.get(product) !== undefined) {
                    if (this.mechanics.ingredients.get(product) > 1) {
                        this.mechanics.ingredients.set(product, this.mechanics.ingredients.get(product) - 1);
                    } else {
                        this.mechanics.ingredients.delete(product);
                    }
                }
            }
        },
    },
    beforeMount() {
        const mechanicsId = Number(this.$route.params.mechanicsId);
        GameConfigService.loadMechanics(Number(mechanicsId)).then((res: Mechanics | null) => {
            if (res instanceof Mechanics) {
                this.mechanics = res;
                ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'mechanics', String(mechanicsId), 'actions'))
                    .then((result) => {
                        const actions : Action[] = [];
                        result.data['hydra:member'].forEach((datum: any) => {
                            const currentAction = (new Action()).load(datum);
                            actions.push(currentAction);
                        });
                        if (this.mechanics instanceof Mechanics) {
                            this.mechanics.actions = actions;
                        }
                    });
                if (this.mechanics.mechanicsType == "Blueprint"){
                    ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'blueprints', String(mechanicsId), 'equipment'))
                        .then((result) => {
                            const equipment : EquipmentConfig = (new EquipmentConfig()).load(result.data);
                            if (this.mechanics instanceof Mechanics) {
                                this.mechanics.equipment = equipment;
                            }
                        });
                } 
            }
        });
    }
});
</script>

<style lang="scss" scoped>

#booleans {
    margin: 15px;
}
.mechanicsCheckbox {
    margin: 0 5px;
}

</style>
