<template>
    <div v-if="mechanics" class="center">
        <h2>{{ $t('admin.mechanics.pageTitle') }} <em>{{ mechanics.name }}</em></h2>
        <div class="flex-row wrap">
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
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>

        <template v-if="mechanics.mechanicsType == 'Blueprint'">
            <h3>{{ $t('admin.mechanics.equipment') }}</h3>
            <div class="flex-row">
                <Input
                    :label="$t('admin.mechanics.equipment')"
                    id="mechanics_equipment"
                    v-model="mechanics.equipment"
                    type="text"
                    :errors="errors.equipment"
                />
            </div>
            <h3>{{ $t('admin.mechanics.ingredients') }}</h3>
            <StringArrayManager
                :label="$t('admin.mechanics.addIngredients')"
                :array="mechanics.ingredients"
                :selection="ingredients"
                @addElement="addIngredient"
                @removeElement="removeIngredient"
            />
        </template>

        <template v-if="mechanics.mechanicsType == 'Book'">
            <h3>{{ $t('admin.mechanics.skill') }}</h3>
            <div class="flex-row">
                <Input
                    :label="$t('admin.mechanics.skill')"
                    id="mechanics_skill"
                    v-model="mechanics.skill"
                    type="text"
                    :errors="errors.skill"
                />
            </div>
        </template>

        <template v-if="mechanics.mechanicsType == 'Document'">
            <h3> {{ $t('admin.mechanics.content') }}</h3>
            <textarea v-model="mechanics.content"></textarea>
            
            <div class="flex-row wrap">
                <div class="checkbox-container">
                    <input
                        type="checkbox"
                        id="isTranslated"
                        v-model="mechanics.isTranslated" 
                    />
                    <label for="isTranslated">{{ mechanics.isTranslated ? $t('admin.mechanics.isTranslated') : $t('admin.mechanics.isNotTranslated') }}</label>
                </div>
                <div class="checkbox-container">
                    <input
                        type="checkbox"
                        id="canShred"
                        v-model="mechanics.canShred" 
                    />
                    <label for="canShred">{{ mechanics.canShred ? $t('admin.mechanics.canShred') : $t('admin.mechanics.cannotShred') }}</label>
                </div>
            </div>
        </template>

        <template v-if="mechanics.mechanics?.includes('ration')"> <!-- When is this selected ??-->
            <div class="flex-row">
                <Input
                    v-if="mechanics.mechanicsType == 'Fruit'"
                    :label="$t('admin.mechanics.plantName')"
                    id="mechanics_plantName"
                    v-model="mechanics.plantName"
                    type="text"
                    :errors="errors.plantName"
                />
                <Input
                    :label="$t('admin.mechanics.satiety')"
                    id="mechanics_satiety"
                    v-model="mechanics.satiety"
                    type="number"
                    :errors="errors.satiety"
                ></Input>
                <input type="checkbox"
                       class="mechanicsCheckbox"
                       id="isPerishable"
                       v-model="mechanics.isPerishable" />
                <label for="isPerishable">{{ mechanics.isPerishable ? $t('admin.mechanics.isPerishable') : $t('admin.mechanics.isNotPerishable') }}</label>
            </div>
            <MapManager :label="$t('admin.mechanics.actionPoints')"
                        :map="mechanics.actionPoints" 
                        mapIndexesType="number"
                        mapValuesType="number"
                        @addTuple="addActionPoints"
                        @removeIndex="removeActionPoints"></MapManager>
            <MapManager :map="mechanics.moralPoints"
                        :label="$t('admin.mechanics.moralPoints')"
                        mapIndexesType="number"
                        mapValuesType="number"
                        @addTuple="addMoralPoints" 
                        @removeIndex="removeMoralPoints"></MapManager>
            <MapManager :map="mechanics.healthPoints" 
                        :label="$t('admin.mechanics.healthPoints')"
                        mapIndexesType="number"
                        mapValuesType="number"
                        @addTuple="addHealthPoints" 
                        @removeIndex="removeHealthPoints"></MapManager>
            <MapManager :map="mechanics.movementPoints" 
                        :label="$t('admin.mechanics.movementPoints')"
                        mapIndexesType="number"
                        mapValuesType="number"
                        @addTuple="addMovementPoints" 
                        @removeIndex="removeMovementPoints"></MapManager>
            <MapManager :map="mechanics.extraEffects" 
                        :label="$t('admin.mechanics.extraEffects')"
                        mapIndexesType="string"
                        mapValuesType="number"
                        @addTuple="addExtraEffects" 
                        @removeIndex="removeExtraEffects"></MapManager>
        </template>

        <template v-if="mechanics.mechanicsType == 'Gear'">
            <h3>{{ $t('admin.mechanics.modifierConfigs') }}</h3>
            <ChildCollectionManager :children="mechanics.modifierConfigs" @addId="selectNewModifierConfig" @remove="removeModifierConfig">
                <template #header="child">
                    <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
                </template>
            </ChildCollectionManager>
        </template>

        <template v-if="mechanics.mechanicsType == 'Plant'">
            <h3>{{ $t('admin.mechanics.maturationTime') }}</h3>
            <MapManager :map="mechanics.maturationTime" 
                        mapIndexesType="number"
                        mapValuesType="number"
                        @addTuple="addMaturationTime"
                        @removeIndex="removeMaturationTime"></MapManager>
            
            <h3>{{ $t('admin.mechanics.oxygen') }}</h3>
            <MapManager :map="mechanics.oxygen" 
                        mapIndexesType="number"
                        mapValuesType="number"
                        @addTuple="addOxygen"
                        @removeIndex="removeOxygen"></MapManager>
        </template>

        <template v-if="mechanics.mechanicsType == 'Weapon'">
            <h3>Weapon stats</h3>
            <div class="flex-row wrap">
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
                        mapValuesType="number"
                        @addTuple="addBaseDamageRange"
                        @removeIndex="removeBaseDamageRange"></MapManager>
        </template>
        <template v-if="mechanics.mechanicsType == 'PatrolShip'">
            <h3>{{ $t('admin.mechanics.dockingPlace') }}</h3>
            <div class="flex-row">
                <Input 
                    :label="$t('admin.mechanics.dockingPlace')"
                    id="mechanics_dockingPlace"
                    v-model="mechanics.dockingPlace"
                    type="text"
                    :errors="errors.dockingPlace"
                />
            </div>
            <h3>{{ $t('admin.mechanics.collectScrapNumber') }}</h3>
            <MapManager :map="mechanics.collectScrapNumber"
                        mapIndexesType="number"
                        mapValuesType="number"
                        @addTuple="addCollectScrapNumber"
                        @removeIndex="removeCollectScrapNumber"/>
            <h3>{{ $t('admin.mechanics.collectScrapPatrolShipDamage') }}</h3>
            <MapManager :map="mechanics.collectScrapPatrolShipDamage"
                        mapIndexesType="number"
                        mapValuesType="number"
                        @addTuple="addCollectScrapPatrolShipDamage"
                        @removeIndex="removeCollectScrapPatrolShipDamage"/>
            <h3>{{ $t('admin.mechanics.collectScrapPlayerDamage') }}</h3>
            <MapManager :map="mechanics.collectScrapPlayerDamage"
                        mapIndexesType="number"
                        mapValuesType="number"
                        @addTuple="addCollectScrapPlayerDamage"
                        @removeIndex="removeCollectScrapPlayerDamage"/>
            <h3>{{ $t('admin.mechanics.failedManoeuvreDaedalusDamage') }}</h3>
            <MapManager :map="mechanics.failedManoeuvreDaedalusDamage"
                        mapIndexesType="number"
                        mapValuesType="number"
                        @addTuple="addFailedManoeuvreDaedalusDamage"
                        @removeIndex="removeFailedManoeuvreDaedalusDamage"/>
            <h3>{{ $t('admin.mechanics.failedManoeuvrePatrolShipDamage') }}</h3>
            <MapManager :map="mechanics.failedManoeuvrePatrolShipDamage"
                        mapIndexesType="number"
                        mapValuesType="number"
                        @addTuple="addFailedManoeuvrePatrolShipDamage"
                        @removeIndex="removeFailedManoeuvrePatrolShipDamage"/>
            <h3>{{ $t('admin.mechanics.failedManoeuvrePlayerDamage') }}</h3>
            <MapManager :map="mechanics.failedManoeuvrePlayerDamage"
                        mapIndexesType="number"
                        mapValuesType="number"
                        @addTuple="addFailedManoeuvrePlayerDamage"
                        @removeIndex="removeFailedManoeuvrePlayerDamage"/>
        </template>
        <UpdateConfigButtons @create="create" @update="update"/>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import ActionService from "@/services/action.service";
import GameConfigService from "@/services/game_config.service";
import ApiService from "@/services/api.service";
import { Action } from "@/entities/Action";
import { Mechanics } from "@/entities/Config/Mechanics";
import { ModifierConfig } from "@/entities/Config/ModifierConfig";
import { handleErrors } from "@/utils/apiValidationErrors";
import ChildCollectionManager from "@/components/Utils/ChildcollectionManager.vue";
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
        addCollectScrapNumber(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.mechanics && this.mechanics.collectScrapNumber) {
                this.mechanics.collectScrapNumber.set(index, value);
            }
        },
        removeCollectScrapNumber(index: number): void {
            if (this.mechanics && this.mechanics.collectScrapNumber) {
                this.mechanics.collectScrapNumber.delete(index);
            }
        },
        addCollectScrapPatrolShipDamage(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.mechanics && this.mechanics.collectScrapPatrolShipDamage) {
                this.mechanics.collectScrapPatrolShipDamage.set(index, value);
            }
        },
        removeCollectScrapPatrolShipDamage(index: number): void {
            if (this.mechanics && this.mechanics.collectScrapPatrolShipDamage) {
                this.mechanics.collectScrapPatrolShipDamage.delete(index);
            }
        },
        addCollectScrapPlayerDamage(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.mechanics && this.mechanics.collectScrapPlayerDamage) {
                this.mechanics.collectScrapPlayerDamage.set(index, value);
            }
        },
        removeCollectScrapPlayerDamage(index: number): void {
            if (this.mechanics && this.mechanics.collectScrapPlayerDamage) {
                this.mechanics.collectScrapPlayerDamage.delete(index);
            }
        },
        addFailedManoeuvreDaedalusDamage(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.mechanics && this.mechanics.failedManoeuvreDaedalusDamage) {
                this.mechanics.failedManoeuvreDaedalusDamage.set(index, value);
            }
        },
        removeFailedManoeuvreDaedalusDamage(index: number): void {
            if (this.mechanics && this.mechanics.failedManoeuvreDaedalusDamage) {
                this.mechanics.failedManoeuvreDaedalusDamage.delete(index);
            }
        },
        addFailedManoeuvrePatrolShipDamage(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.mechanics && this.mechanics.failedManoeuvrePatrolShipDamage) {
                this.mechanics.failedManoeuvrePatrolShipDamage.set(index, value);
            }
        },
        removeFailedManoeuvrePatrolShipDamage(index: number): void {
            if (this.mechanics && this.mechanics.failedManoeuvrePatrolShipDamage) {
                this.mechanics.failedManoeuvrePatrolShipDamage.delete(index);
            }
        },
        addFailedManoeuvrePlayerDamage(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.mechanics && this.mechanics.failedManoeuvrePlayerDamage) {
                this.mechanics.failedManoeuvrePlayerDamage.set(index, value);
            }
        },
        removeFailedManoeuvrePlayerDamage(index: number): void {
            if (this.mechanics && this.mechanics.failedManoeuvrePlayerDamage) {
                this.mechanics.failedManoeuvrePlayerDamage.delete(index);
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
            }
        });
    }
});
</script>

<style lang="scss" scoped>

textarea {
    height: 12em;
    min-height: 4em;
    padding: 0.5em 0.8em;
    resize: vertical;
    color: white;
    font-size: 1.15em;
    line-height: 1.5em;
    background: #222b6b;
    border: 1px solid transparentize(white, 0.8);
    outline: none;
}

</style>
