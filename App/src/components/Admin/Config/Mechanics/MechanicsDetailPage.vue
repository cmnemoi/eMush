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
            />
            <Input
                :label="$t('admin.mechanics.mechanicsType')"
                id="mechanics_mechanicsType"
                v-model="mechanics.mechanicsType"
                type="text"
                :errors="errors.mechanicsType"
            />
        </div>

        <h3>{{ $t('admin.mechanics.mechanics') }}</h3>
        <StringArrayManager
            :array="mechanics.mechanics"
            @addElement="mechanics.mechanics.push($event)"
            @removeElement="mechanics.mechanics.splice(mechanics.mechanics.indexOf($event), 1)"
        />
        
        <h3>{{ $t('admin.mechanics.actions') }}</h3>
        <ChildCollectionManager :children="mechanics.actions" @addId="selectNewAction" @remove="removeAction">
            <template #header="child">
                <span>{{ child.id }} - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        
        <div v-if="mechanics.mechanicsType == 'Blueprint'">
            <h3>{{ $t('admin.mechanics.equipment') }}</h3>
            <ChildManager :child="mechanics.equipment" @addId="selectNewEquipment" @remove="removeEquipment">
                <template #header="child">
                    <span>{{ child.id }} - {{ child.name }}</span>
                </template>
            </ChildManager>

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

        <div class="flex-row" v-if="mechanics.mechanicsType == 'Book'">
            <Input :label="$t('admin.mechanics.skill')"
                   id="mechanics_skill"
                   v-model="mechanics.skill"
                   type="text"
                   :errors="errors.skill"
            />
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

        <div v-if="mechanics.mechanics?.includes('ration')">
            <div class="flex-row">
                <input type="checkbox"
                       class="mechanicsCheckbox"
                       id="isPerishable"
                       v-model="mechanics.isPerishable" />
                <label for="isPerishable">{{ mechanics.isPerishable ? $t('admin.mechanics.isPerishable') : $t('admin.mechanics.isNotPerishable') }}</label>
                <Input
                    :label="$t('admin.mechanics.satiety')"
                    id="mechanics_satiety"
                    v-model="mechanics.satiety"
                    type="number"
                    :errors="errors.satiety"
                ></Input>
            </div>
            <MapManager :label="$t('admin.mechanics.actionPoints')"
                        :map="mechanics.actionPoints" 
                        mapIndexesType="number"
                        @addTuple="addActionPoints" 
                        @removeIndex="removeActionPoints"></MapManager>
            <MapManager :map="mechanics.moralPoints"
                        :label="$t('admin.mechanics.moralPoints')"
                        mapIndexesType="number"
                        @addTuple="addMoralPoints" 
                        @removeIndex="removeMoralPoints"></MapManager>
            <MapManager :map="mechanics.healthPoints" 
                        :label="$t('admin.mechanics.healthPoints')"
                        mapIndexesType="number"
                        @addTuple="addHealthPoints" 
                        @removeIndex="removeHealthPoints"></MapManager>
            <MapManager :map="mechanics.movementPoints" 
                        :label="$t('admin.mechanics.movementPoints')"
                        mapIndexesType="number"
                        @addTuple="addMovementPoints" 
                        @removeIndex="removeMovementPoints"></MapManager>
            <MapManager :map="mechanics.extraEffects" 
                        :label="$t('admin.mechanics.extraEffects')"
                        mapIndexesType="string"
                        @addTuple="addExtraEffects" 
                        @removeIndex="removeExtraEffects"></MapManager>
        </div>

        <div v-if="mechanics.mechanicsType == 'Gear'">
            <h3>{{ $t('admin.mechanics.modifierConfigs') }}</h3>
            <ChildCollectionManager :children="mechanics.modifierConfigs" @addId="selectNewModifierConfig" @remove="removeModifierConfig">
                <template #header="child">
                    <span>{{ child.id }} - {{ child.name }}</span>
                </template>
            </ChildCollectionManager>
        </div>

        <div v-if="mechanics.mechanicsType == 'Plant'">
            <h3>{{ $t('admin.mechanics.maturationTime') }}</h3>
            <MapManager :map="mechanics.maturationTime" 
                        mapIndexesType="number"
                        @addTuple="addMaturationTime" 
                        @removeIndex="removeMaturationTime"></MapManager>
            
            <h3>{{ $t('admin.mechanics.oxygen') }}</h3>
            <MapManager :map="mechanics.oxygen" 
                        mapIndexesType="number"
                        @addTuple="addOxygen" 
                        @removeIndex="removeOxygen"></MapManager>
        </div>

        <div v-if="mechanics.mechanicsType == 'Weapon'">
            <div class="flex-row">
                <Input
                    :label="$t('admin.mechanics.baseAccuracy')"
                    id="mechanics_baseAccuracy"
                    v-model="mechanics.baseAccuracy"
                    type="number"
                    :errors="errors.baseAccuracy"
                ></Input>
                <Input
                    :label="$t('admin.mechanics.expeditionBonus')"
                    id="mechanics_expeditionBonus"
                    v-model="mechanics.expeditionBonus"
                    type="number"
                    :errors="errors.expeditionBonus"
                ></Input>
                <Input
                    :label="$t('admin.mechanics.criticalSuccessRate')"
                    id="mechanics_criticalSuccessRate"
                    v-model="mechanics.criticalSuccessRate"
                    type="number"
                    :errors="errors.criticalSuccessRate"
                ></Input>
                <Input
                    :label="$t('admin.mechanics.criticalFailRate')"
                    id="mechanics_criticalFailRate"
                    v-model="mechanics.criticalFailRate"
                    type="number"
                    :errors="errors.criticalFailRate"
                ></Input>
                <Input
                    :label="$t('admin.mechanics.oneShotRate')"
                    id="mechanics_oneShotRate"
                    v-model="mechanics.oneShotRate"
                    type="number"
                    :errors="errors.oneShotRate"
                ></Input>
            </div>
            <h3>{{ $t('admin.mechanics.baseDamageRange') }}</h3>
            <MapManager :map="mechanics.baseDamageRange" 
                        mapIndexesType="number"
                        @addTuple="addBaseDamageRange" 
                        @removeIndex="removeBaseDamageRange"></MapManager>
        </div>
        <UpdateConfigButtons @create="create" @update="update"/>
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
import { ModifierConfig } from "@/entities/Config/ModifierConfig";
import { handleErrors } from "@/utils/apiValidationErrors";
import ChildCollectionManager from "@/components/Utils/ChildcollectionManager.vue";
import ChildManager from "@/components/Utils/ChildManager.vue";
import Input from "@/components/Utils/Input.vue";
import MapManager from "@/components/Utils/MapManager.vue";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";
import StringArrayManager from "@/components/Utils/StringArrayManager.vue";
import urlJoin from "url-join";
import { removeItem } from "@/utils/misc";

interface MechanicsState {
    mechanics: null|Mechanics
    errors: any,
    ingredients: string[],
    ingredientToAdd: string,
}

export default defineComponent({
    name: "MechanicsDetailPage",
    components: {
        ChildCollectionManager,
        ChildManager,
        Input,
        MapManager,
        UpdateConfigButtons,
        StringArrayManager
    },
    data: function (): MechanicsState {
        return {
            mechanics: null,
            errors: {},
            ingredients: ["metal_scraps", "plastic_scraps", "soap", "old_t_shirt", "thick_tube", "oxygen_capsule", "fuel_capsule"],
            ingredientToAdd: "",
        };
    },
    methods: {
        create(): void {
            if(this.mechanics === null) return;

            const newMechanics = this.mechanics;
            newMechanics.id = null;

            // @ts-ignore
            GameConfigService.createMechanics(newMechanics)
                .then((res: Mechanics | null) => {
                    const newMechanicsUrl = urlJoin(process.env.VUE_APP_URL + '/config/mechanics', String(res?.id));
                    window.location.href = newMechanicsUrl;
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
            if (this.mechanics === null) {
                return;
            }
            this.errors = {};
            // @ts-ignore
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
                        ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'gears', String(this.mechanics.id), 'modifier_configs'))
                            .then((result) => {
                                const modifierConfigs : ModifierConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const currentModifierConfig = (new ModifierConfig()).load(datum);
                                    modifierConfigs.push(currentModifierConfig);
                                });
                                if (this.mechanics instanceof Mechanics) {
                                    this.mechanics.modifierConfigs = modifierConfigs;
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
        selectNewEquipment(selectedId: any) {
            GameConfigService.loadEquipmentConfig(selectedId).then((res) => {
                if (res && this.mechanics && this.mechanics.equipment) {
                    this.mechanics.equipment = res;
                }
            });
        },
        removeEquipment() {
            if (this.mechanics && this.mechanics.equipment) {
                this.mechanics.equipment = null;
            }
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
        selectNewModifierConfig(selectedId: any) {
            GameConfigService.loadModifierConfig(selectedId).then((res) => {
                if (res && this.mechanics && this.mechanics.modifierConfigs) {
                    this.mechanics.modifierConfigs.push(res);
                }
            });
        },
        removeModifierConfig(child: any) {
            if (this.mechanics && this.mechanics.modifierConfigs) {
                this.mechanics.modifierConfigs = removeItem(this.mechanics.modifierConfigs, child);
            }
        },
        addIngredient(product: string): void {
            if (this.mechanics && this.mechanics.ingredients) {
                const productKey = this.mechanics.ingredients.get(product);
                if (productKey !== undefined) {
                    this.mechanics.ingredients.set(product, productKey + 1);
                } else {
                    this.mechanics.ingredients.set(product, 1);
                }
            }
        },
        removeIngredient(product: string): void {
            if (this.mechanics && this.mechanics.ingredients) {
                const productKey = this.mechanics.ingredients.get(product);
                if (productKey !== undefined) {
                    if (productKey > 1) {
                        this.mechanics.ingredients.set(product, productKey - 1);
                    } else {
                        this.mechanics.ingredients.delete(product);
                    }
                }
            }
        },
        addMaturationTime(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.mechanics && this.mechanics.maturationTime) {
                this.mechanics.maturationTime.set(index, value);
            }
        },
        removeMaturationTime(index: number): void {
            console.log(index);
            if (this.mechanics && this.mechanics.maturationTime) {
                this.mechanics.maturationTime.delete(index);
            }
        },
        addOxygen(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.mechanics && this.mechanics.oxygen) {
                this.mechanics.oxygen.set(index, value);
            }
        },
        removeOxygen(index: number): void {
            if (this.mechanics && this.mechanics.oxygen) {
                this.mechanics.oxygen.delete(index);
            }
        },
        addActionPoints(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.mechanics && this.mechanics.actionPoints) {
                this.mechanics.actionPoints.set(index, value);
            }
        },
        removeActionPoints(index: number): void {
            if (this.mechanics && this.mechanics.actionPoints) {
                this.mechanics.actionPoints.delete(index);
            }
        },
        addMoralPoints(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.mechanics && this.mechanics.moralPoints) {
                this.mechanics.moralPoints.set(index, value);
            }
        },
        removeMoralPoints(index: number): void {
            if (this.mechanics && this.mechanics.moralPoints) {
                this.mechanics.moralPoints.delete(index);
            }
        },
        addHealthPoints(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.mechanics && this.mechanics.healthPoints) {
                this.mechanics.healthPoints.set(index, value);
            }
        },
        removeHealthPoints(index: number): void {
            if (this.mechanics && this.mechanics.healthPoints) {
                this.mechanics.healthPoints.delete(index);
            }
        },
        addMovementPoints(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.mechanics && this.mechanics.movementPoints) {
                this.mechanics.movementPoints.set(index, value);
            }
        },
        removeMovementPoints(index: number): void {
            if (this.mechanics && this.mechanics.movementPoints) {
                this.mechanics.movementPoints.delete(index);
            }
        },
        addExtraEffects(tuple: any): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.mechanics && this.mechanics.extraEffects) {
                this.mechanics.extraEffects.set(index, value);
            }
        },
        removeExtraEffects(index: string): void {
            if (this.mechanics && this.mechanics.extraEffects) {
                this.mechanics.extraEffects.delete(index);
            }
        },
        addBaseDamageRange(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.mechanics && this.mechanics.baseDamageRange) {
                this.mechanics.baseDamageRange.set(index, value);
            }
        },
        removeBaseDamageRange(index: number): void {
            if (this.mechanics && this.mechanics.baseDamageRange) {
                this.mechanics.baseDamageRange.delete(index);
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
                if (this.mechanics.mechanicsType == "Gear"){
                    ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'gears', String(mechanicsId), 'modifier_configs'))
                        .then((result) => {
                            const modifierConfigs : ModifierConfig[] = [];
                            result.data['hydra:member'].forEach((datum: any) => {
                                const currentModifierConfig = (new ModifierConfig()).load(datum);
                                modifierConfigs.push(currentModifierConfig);
                            });
                            if (this.mechanics instanceof Mechanics) {
                                this.mechanics.modifierConfigs = modifierConfigs;
                            }
                        });
                }
                if (this.mechanics.mechanicsType == "Fruit"){
                    ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'plants', String(mechanicsId), 'fruit'))
                        .then((result) => {
                            const fruit : EquipmentConfig = (new EquipmentConfig()).load(result.data);
                            if (this.mechanics instanceof Mechanics) {
                                this.mechanics.fruit = fruit;
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
