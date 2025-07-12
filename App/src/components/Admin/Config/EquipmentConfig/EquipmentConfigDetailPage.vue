<template>
    <div v-if="equipmentConfig" class="center">
        <h2>{{ $t('admin.equipmentConfig.pageTitle') }} <em>{{ equipmentConfig.equipmentName }}</em></h2>
        <div class="flex-row wrap">
            <Input
                :label="$t('admin.equipmentConfig.name')"
                id="equipmentConfig_name"
                v-model="equipmentConfig.name"
                type="text"
                :errors="errors.name"
            ></Input>
            <Input
                :label="$t('admin.equipmentConfig.equipmentName')"
                id="equipmentConfig_equipmentName"
                v-model="equipmentConfig.equipmentName"
                type="text"
                :errors="errors.equipmentName"
            ></Input>
        </div>
        <div class="flex-row wrap">
            <div class="select-container">
                <label for="equipmentConfig_breakableType">{{ $t('admin.equipmentConfig.breakableType') }}</label>
                <select
                    :label="$t('admin.equipmentConfig.breakableType')"
                    id="equipmentConfig_breakableType"
                    v-model="equipmentConfig.breakableType"
                >
                    <option
                        v-for="option in breakableType"
                        :key="option"
                        :value="option"
                    >
                        {{ $t('admin.equipmentConfig.' + option) }}
                    </option>
                </select>
            </div>
            <div class="checkbox-container" v-if="equipmentConfig.equipmentType === 'ItemConfig'">
                <input
                    type="checkbox"
                    id="equipmentConfig_isPersonal"
                    v-model="equipmentConfig.isPersonal"
                />
                <label for="equipmentConfig_isPersonal">{{ equipmentConfig.isPersonal ? $t('admin.equipmentConfig.isPersonal') : $t('admin.equipmentConfig.isNotPersonal') }}</label>
            </div>
            <div class="checkbox-container" v-if="equipmentConfig.equipmentType === 'ItemConfig'">
                <input
                    type="checkbox"
                    id="equipmentConfig_isStackable"
                    v-model="equipmentConfig.isStackable"
                />
                <label for="equipmentConfig_isStackable">{{ equipmentConfig.isStackable ? $t('admin.equipmentConfig.isStackable') : $t('admin.equipmentConfig.isNotStackable') }}</label>
            </div>
            <template v-if="equipmentConfig.equipmentType == 'SpaceShip'">
                <h3>{{ $t('admin.equipmentConfig.collectScrapNumber') }}</h3>
                <MapManager
                    :map="equipmentConfig.collectScrapNumber"
                    id="mechanics_collectScrapNumber"
                    map-indexes-type="number"
                    map-values-type="number"
                    @add-tuple="addCollectScrapNumber"
                    @remove-index="removeCollectScrapNumber"
                />
                <h3>{{ $t('admin.equipmentConfig.collectScrapPatrolShipDamage') }}</h3>
                <MapManager
                    :map="equipmentConfig.collectScrapPatrolShipDamage"
                    id="mechanics_collectScrapPatrolShipDamage"
                    map-indexes-type="number"
                    map-values-type="number"
                    @add-tuple="addCollectScrapPatrolShipDamage"
                    @remove-index="removeCollectScrapPatrolShipDamage"
                />
                <h3>{{ $t('admin.equipmentConfig.collectScrapPlayerDamage') }}</h3>
                <MapManager
                    :map="equipmentConfig.collectScrapPlayerDamage"
                    id="mechanics_collectScrapPlayerDamage"
                    map-indexes-type="number"
                    map-values-type="number"
                    @add-tuple="addCollectScrapPlayerDamage"
                    @remove-index="removeCollectScrapPlayerDamage"
                />
                <h3>{{ $t('admin.equipmentConfig.failedManoeuvreDaedalusDamage') }}</h3>
                <MapManager
                    :map="equipmentConfig.failedManoeuvreDaedalusDamage"
                    id="mechanics_failedManoeuvreDaedalusDamage"
                    map-indexes-type="number"
                    map-values-type="number"
                    @add-tuple="addFailedManoeuvreDaedalusDamage"
                    @remove-index="removeFailedManoeuvreDaedalusDamage"
                />
                <h3>{{ $t('admin.equipmentConfig.failedManoeuvrePatrolShipDamage') }}</h3>
                <MapManager
                    :map="equipmentConfig.failedManoeuvrePatrolShipDamage"
                    id="mechanics_failedManoeuvrePatrolShipDamage"
                    map-indexes-type="number"
                    map-values-type="number"
                    @add-tuple="addFailedManoeuvrePatrolShipDamage"
                    @remove-index="removeFailedManoeuvrePatrolShipDamage"
                />
                <h3>{{ $t('admin.equipmentConfig.failedManoeuvrePlayerDamage') }}</h3>
                <MapManager
                    :map="equipmentConfig.failedManoeuvrePlayerDamage"
                    id="mechanics_failedManoeuvrePlayerDamage"
                    map-indexes-type="number"
                    map-values-type="number"
                    @add-tuple="addFailedManoeuvrePlayerDamage"
                    @remove-index="removeFailedManoeuvrePlayerDamage"
                />
            </template>
        </div>

        <h3>{{ $t('admin.equipmentConfig.dismountedProducts') }}</h3>
        <StringArrayManager
            :label="$t('admin.equipmentConfig.addDismountedProducts')"
            :array="equipmentConfig.dismountedProducts"
            :selection="products"
            id="equipmentConfig_addDismountedProducts"
            @add-element="addDismountedProduct"
            @remove-element="removeDismountedProduct"
        />

        <h3>{{ $t('admin.equipmentConfig.actions') }}</h3>
        <ChildCollectionManager
            :children="equipmentConfig.actions"
            id="equipmentConfig_actions"
            @add-id="selectNewActionConfig"
            @remove="removeActionConfig"
        >
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>

        <h3>{{ $t('admin.equipmentConfig.initStatuses') }}</h3>
        <ChildCollectionManager
            :children="equipmentConfig.initStatuses"
            id="equipmentConfig_initStatuses"
            @add-id="selectNewInitStatuses"
            @remove="removeInitStatuses"
        >
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>

        <h3>{{ $t('admin.equipmentConfig.mechanics') }}</h3>
        <ChildCollectionManager
            :children="equipmentConfig.mechanics"
            id="equipmentConfig_mechanics"
            @add-id="selectNewMechanics"
            @remove="removeMechanics"
        >
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>

        <UpdateConfigButtons @create="create" @update="update"/>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import ActionConfigService from "@/services/action.service";
import { EquipmentConfig } from "@/entities/Config/EquipmentConfig";
import { handleErrors } from "@/utils/apiValidationErrors";
import Input from "@/components/Utils/Input.vue";
import { removeItem } from "@/utils/misc";
import ChildCollectionManager from "@/components/Utils/ChildcollectionManager.vue";
import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { ActionConfig } from "@/entities/Config/ActionConfig";
import { StatusConfig } from "@/entities/Config/StatusConfig";
import { Mechanics } from "@/entities/Config/Mechanics";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";
import StringArrayManager from "@/components/Utils/StringArrayManager.vue";
import MapManager from "@/components/Utils/MapManager.vue";

interface EquipmentConfigState {
    equipmentConfig: null|EquipmentConfig
    errors: any,
    breakableType: Array<string>
    products: Array<string>,
    productToAdd: string
}

export default defineComponent({
    name: "EquipmentConfigDetailPage",
    components: {
        MapManager,
        ChildCollectionManager,
        Input,
        StringArrayManager,
        UpdateConfigButtons
    },
    data: function (): EquipmentConfigState {
        return {
            equipmentConfig: null,
            errors: {},
            breakableType: ["none", "breakable", "destroyOnBreak"],
            products: ["metal_scraps", "plastic_scraps", "thick_tube"],
            productToAdd: ""
        };
    },
    methods: {
        create(): void {
            if (this.equipmentConfig === null) return;

            const newEquipmentConfig = this.equipmentConfig;
            newEquipmentConfig.id = null;

            // @ts-ignore
            GameConfigService.createEquipmentConfig(newEquipmentConfig).then((res: EquipmentConfig | null) => {
                const newEquipmentConfigUrl = urlJoin(import.meta.env.VITE_APP_URL + '/config/equipment-config', String(res?.id));
                window.location.href = newEquipmentConfigUrl;
            });
        },
        update(): void {
            if (this.equipmentConfig === null) {
                return;
            }
            this.errors = {};
            // @ts-ignore
            GameConfigService.updateEquipmentConfig(this.equipmentConfig)
                .then((res: EquipmentConfig | null) => {
                    this.equipmentConfig = res;
                    if (this.equipmentConfig !== null) {
                        ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'equipment_configs', String(this.equipmentConfig.id), 'action_configs'))
                            .then((result) => {
                                const actions: ActionConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const currentActionConfig = (new ActionConfigConfig()).load(datum);
                                    actions.push(currentActionConfig);
                                });
                                if (this.equipmentConfig instanceof EquipmentConfig) {
                                    this.equipmentConfig.actions = actions;
                                }
                            });
                        ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'equipment_configs', String(this.equipmentConfig.id), 'init_statuses'))
                            .then((result) => {
                                const statuses: StatusConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const currentStatus = (new StatusConfig()).load(datum);
                                    statuses.push(currentStatus);
                                });
                                if (this.equipmentConfig instanceof EquipmentConfig) {
                                    this.equipmentConfig.initStatuses = statuses;
                                }
                            });
                        ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'equipment_configs', String(this.equipmentConfig.id), 'mechanics'))
                            .then((result) => {
                                const mechanics: Mechanics[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const currentMechanics = (new Mechanics()).load(datum);
                                    equipmentConfig.push(currentMechanics);
                                });
                                if (this.equipmentConfig instanceof EquipmentConfig) {
                                    this.equipmentConfig.mechanics = mechanics;
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
        addDismountedProduct(product: string): void {
            if (this.equipmentConfig && this.equipmentConfig.dismountedProducts) {
                const productKey = this.equipmentConfig.dismountedProducts.get(product);
                if (productKey !== undefined) {
                    this.equipmentConfig.dismountedProducts.set(product, productKey + 1);
                } else {
                    this.equipmentConfig.dismountedProducts.set(product, 1);
                }
            }
        },
        removeDismountedProduct(product: string): void {
            if (this.equipmentConfig && this.equipmentConfig.dismountedProducts) {
                const productKey = this.equipmentConfig.dismountedProducts.get(product);
                if (productKey !== undefined) {
                    if (productKey > 1) {
                        this.equipmentConfig.dismountedProducts.set(product, productKey - 1);
                    } else {
                        this.equipmentConfig.dismountedProducts.delete(product);
                    }
                }
            }
        },
        selectNewActionConfig(selectedId: any) {
            ActionConfigService.loadActionConfig(selectedId).then((res) => {
                if (res && this.equipmentConfig && this.equipmentConfig.actions) {
                    this.equipmentConfig.actions.push(res);
                }
            });
        },
        removeActionConfig(child: any) {
            if (this.equipmentConfig && this.equipmentConfig.actions) {
                this.equipmentConfig.actions = removeItem(this.equipmentConfig.actions, child);
            }
        },
        selectNewInitStatuses(selectedId: any) {
            GameConfigService.loadStatusConfig(selectedId).then((res) => {
                if (res && this.equipmentConfig && this.equipmentConfig.initStatuses) {
                    this.equipmentConfig.initStatuses.push(res);
                }
            });
        },
        removeInitStatuses(child: any) {
            if (this.equipmentConfig && this.equipmentConfig.initStatuses) {
                this.equipmentConfig.initStatuses = removeItem(this.equipmentConfig.initStatuses, child);
            }
        },
        selectNewMechanics(selectedId: any) {
            GameConfigService.loadMechanics(selectedId).then((res) => {
                if (res && this.equipmentConfig && this.equipmentConfig.mechanics) {
                    this.equipmentConfig.mechanics.push(res);
                }
            });
        },
        removeMechanics(child: any) {
            if (this.equipmentConfig && this.equipmentConfig.mechanics) {
                this.equipmentConfig.mechanics = removeItem(this.equipmentConfig.mechanics, child);
            }
        },
        hasDismountedProducts(): boolean {
            if (this.equipmentConfig === null || this.equipmentConfig.dismountedProducts === null) {
                return false;
            }
            return this.equipmentConfig.dismountedProducts.size > 0;

        },
        addCollectScrapNumber(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.equipmentConfig && this.equipmentConfig.collectScrapNumber) {
                this.equipmentConfig.collectScrapNumber.set(index, value);
            }
        },
        removeCollectScrapNumber(index: number): void {
            if (this.equipmentConfig && this.equipmentConfig.collectScrapNumber) {
                this.equipmentConfig.collectScrapNumber.delete(index);
            }
        },
        addCollectScrapPatrolShipDamage(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.equipmentConfig && this.equipmentConfig.collectScrapPatrolShipDamage) {
                this.equipmentConfig.collectScrapPatrolShipDamage.set(index, value);
            }
        },
        removeCollectScrapPatrolShipDamage(index: number): void {
            if (this.equipmentConfig && this.equipmentConfig.collectScrapPatrolShipDamage) {
                this.equipmentConfig.collectScrapPatrolShipDamage.delete(index);
            }
        },
        addCollectScrapPlayerDamage(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.equipmentConfig && this.equipmentConfig.collectScrapPlayerDamage) {
                this.equipmentConfig.collectScrapPlayerDamage.set(index, value);
            }
        },
        removeCollectScrapPlayerDamage(index: number): void {
            if (this.equipmentConfig && this.equipmentConfig.collectScrapPlayerDamage) {
                this.equipmentConfig.collectScrapPlayerDamage.delete(index);
            }
        },
        addFailedManoeuvreDaedalusDamage(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.equipmentConfig && this.equipmentConfig.failedManoeuvreDaedalusDamage) {
                this.equipmentConfig.failedManoeuvreDaedalusDamage.set(index, value);
            }
        },
        removeFailedManoeuvreDaedalusDamage(index: number): void {
            if (this.equipmentConfig && this.equipmentConfig.failedManoeuvreDaedalusDamage) {
                this.equipmentConfig.failedManoeuvreDaedalusDamage.delete(index);
            }
        },
        addFailedManoeuvrePatrolShipDamage(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.equipmentConfig && this.equipmentConfig.failedManoeuvrePatrolShipDamage) {
                this.equipmentConfig.failedManoeuvrePatrolShipDamage.set(index, value);
            }
        },
        removeFailedManoeuvrePatrolShipDamage(index: number): void {
            if (this.equipmentConfig && this.equipmentConfig.failedManoeuvrePatrolShipDamage) {
                this.equipmentConfig.failedManoeuvrePatrolShipDamage.delete(index);
            }
        },
        addFailedManoeuvrePlayerDamage(tuple: number[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.equipmentConfig && this.equipmentConfig.failedManoeuvrePlayerDamage) {
                this.equipmentConfig.failedManoeuvrePlayerDamage.set(index, value);
            }
        },
        removeFailedManoeuvrePlayerDamage(index: number): void {
            if (this.equipmentConfig && this.equipmentConfig.failedManoeuvrePlayerDamage) {
                this.equipmentConfig.failedManoeuvrePlayerDamage.delete(index);
            }
        }
    },
    beforeMount() {
        const equipmentConfigId = String(this.$route.params.equipmentConfigId);
        GameConfigService.loadEquipmentConfig(Number(equipmentConfigId)).then((res: EquipmentConfig | null) => {
            this.equipmentConfig = res;
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+'equipment_configs', equipmentConfigId, 'action_configs'))
                .then((result) => {
                    const actions : ActionConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        const currentActionConfig = (new ActionConfig()).load(datum);
                        actions.push(currentActionConfig);
                    });
                    if (this.equipmentConfig instanceof EquipmentConfig) {
                        this.equipmentConfig.actions = actions;
                    }
                });
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+'equipment_configs', equipmentConfigId, 'init_statuses'))
                .then((result) => {
                    const initStatuses : StatusConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        const currentStatusConfig = (new StatusConfig()).load(datum);
                        initStatuses.push(currentStatusConfig);
                    });
                    if (this.equipmentConfig instanceof EquipmentConfig) {
                        this.equipmentConfig.initStatuses = initStatuses;
                    }
                });
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+'equipment_configs', equipmentConfigId, 'mechanics'))
                .then((result) => {
                    const mechanics : Mechanics[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        const currentMechanics = (new Mechanics()).load(datum);
                        mechanics.push(currentMechanics);
                    });
                    if (this.equipmentConfig instanceof EquipmentConfig) {
                        this.equipmentConfig.mechanics = mechanics;
                    }
                });
        });
    }
});
</script>

<style lang="scss" scoped>

.equipmentConfigCheckbox {
    margin-left: 10px;
    margin-right: 10px;
}

.select-container {
    width: 31%;
    min-width: 200px;
    align-self: flex-end;

    label {
        padding: 0 0.8em;
        transform: translateY(0.45em);
        word-break: break-word;
    }

    select {
        min-width: 5em;
        padding: 0.3em 0.6em;
        font-size: 1.3em;
        color: white;
        background: #222b6b;
        border: 1px solid transparentize(white, 0.8);
        border-radius: 1px;

        &:focus {
            outline: none;
            box-shadow: 0 0 0 3px transparentize(white, 0.85);
        }
    }
}

</style>
