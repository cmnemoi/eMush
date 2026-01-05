<template>
    <div class="achievement-wrapper">
        <div class="crown" />
        <div class="statistics-container">
            <!-- Header Section with different background -->
            <div class="header-section">
                <!-- Title Section - Full width header -->
                <div class="title-header">
                    <h2 class="title">{{ $t('achievements.title') }}</h2>
                    <!-- Gender Selection -->
                    <div class="gender-selection">
                        <Tippy>
                            <button
                                class="gender-button"
                                :class="{ active: selectedGender === 'male' }"
                                @click="updateGender('male')"
                            >
                                <img :src="getImgUrl('male.png')" alt="Male" />
                            </button>
                            <template #content>
                                {{ $t('achievements.gender.masculine') }}
                            </template>
                        </Tippy>
                        <Tippy>
                            <button
                                class="gender-button"
                                :class="{ active: selectedGender === 'female' }"
                                @click="updateGender('female')"
                            >
                                <img :src="getImgUrl('female.png')" alt="Female" />
                            </button>
                            <template #content>
                                {{ $t('achievements.gender.feminine') }}
                            </template>
                        </Tippy>
                    </div>
                </div>

                <!-- Points Section -->
                <div class="points-section">
                    <div class="points">{{ $t('achievements.points', { points: totalPoints }) }}</div>
                </div>

                <!-- Main Stats Cards -->
                <div class="stats-cards">
                    <Tippy
                        class="stat-card"
                        v-for="statistic in topThreeStatistics"
                        :key="statistic.name"
                        :class="{ active: activeCard === statistic.key, rare: statistic.isRare }"
                        @click="setActiveCard(statistic.key)"
                    >
                        <div class="card-value" :class="{ rare: statistic.isRare }">{{ statistic.count }}</div>
                        <div class="card-icon">
                            <img :src="StatisticRecords[statistic.key].icon" :alt="statistic.name" />
                        </div>
                        <div class="card-text" :class="{ rare: statistic.isRare }">{{ statistic.name }}</div>
                        <template #content>
                            <h1>{{ statistic.name }}</h1>
                            <p>{{ statistic.description }}</p>
                        </template>
                    </Tippy>
                </div>

                <!-- Navigation Tabs -->
                <div class="nav-tabs">
                    <button
                        class="tab-button"
                        :class="{ active: activeTab === 'stats' }"
                        @click="setActiveTab('stats')"
                    >
                        {{ $t('achievements.tabs.statistics') }}
                    </button>
                    <button
                        class="tab-button"
                        :class="{ active: activeTab === 'gains' }"
                        @click="setActiveTab('gains')"
                    >
                        {{ $t('achievements.tabs.gains', { count: achievementCount }) }}
                    </button>
                </div>
            </div>

            <!-- Statistics Grid -->
            <div class="stats-grid" v-if="activeTab === 'stats'">
                <Tippy
                    class="stat-grid-item"
                    v-for="statistic in statistics"
                    :key="statistic.name"
                    :class="{ rare: statistic.isRare }"
                >
                    <div class="grid-item-value" :class="{ rare: statistic.isRare }">{{ statistic.formattedCount }}</div>
                    <div class="grid-item-icon">
                        <img :src="StatisticRecords[statistic.key].icon" :alt="statistic.name" />
                    </div>
                    <template #content>
                        <h1>{{ statistic.name }}</h1>
                        <p>{{ statistic.description }}</p>
                    </template>
                </Tippy>
            </div>

            <!-- Gains List -->
            <div class="stats-list" v-if="activeTab === 'gains'">
                <Tippy class="stat-item" v-for="achievement in achievements" :key="achievement.name">
                    <div class="stat-icon">
                        <img :src="StatisticRecords[achievement.statisticKey].icon" :alt="achievement.name" />
                    </div>
                    <div class="stat-name">{{ achievement.name }}</div>
                    <div class="stat-value">{{ achievement.formattedPoints }}</div>
                    <template #content>
                        <h1>{{ achievement.statisticName }}</h1>
                        <p>{{ achievement.statisticDescription }}</p>
                    </template>
                </Tippy>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onBeforeMount, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { Tippy } from 'vue-tippy';
import { useStore } from 'vuex';
import { StatisticRecords } from './enum';
import { Achievement, Gender, Statistic } from './models';
import { getImgUrl } from '@/utils/getImgUrl';

const route = useRoute();
const store = useStore();

// Store getters
const language = computed<string>(() => store.getters['locale/currentLocale']);
const statistics = computed<Statistic[]>(() => store.getters['achievements/statistics']);
const topThreeStatistics = computed<Statistic[]>(() => store.getters['achievements/topNStatistics'](3));
const totalPoints = computed<integer>(() => store.getters['achievements/points']);
const achievements = computed<Achievement[]>(() => store.getters['achievements/achievements']);
const achievementCount = computed<integer>(() => store.getters['achievements/achievements'].length);
const userId = computed<string>(() => route.params.userId as string);
const selectedGender = computed<Gender>(() => store.getters['achievements/selectedGender']);

// Store actions
async function fetchStatistics(payload: { userId: string; language: string; gender: Gender }) {
    await store.dispatch('achievements/fetchStatistics', payload);
}
async function fetchAchievements(payload: { userId: string; language: string; gender: Gender }) {
    await store.dispatch('achievements/fetchAchievements', payload);
}
const updateGender = async (gender: Gender) => store.dispatch('achievements/updateGender', gender);

const setActiveCard = (card: string) => activeCard.value = card;
const setActiveTab = (tab: string) => activeTab.value = tab;

// Reacting to component lifecycle
onBeforeMount(async () => {
    await Promise.allSettled([
        fetchStatistics({ userId: userId.value, language: language.value, gender: selectedGender.value }),
        fetchAchievements({ userId: userId.value, language: language.value, gender: selectedGender.value })
    ]);
});

watch([language, selectedGender, route], async () => {
    await Promise.allSettled([
        fetchStatistics({ userId: userId.value, language: language.value, gender: selectedGender.value }),
        fetchAchievements({ userId: userId.value, language: language.value, gender: selectedGender.value })
    ]);
});

// Component local data
const activeCard   = ref<string>('');
const activeTab    = ref<'stats' | string>('stats');
</script>

<style scoped lang="scss">
@use "sass:color";

.achievement-wrapper {
  position: relative;
  padding-top: 15px;
  margin: 0 auto;
  max-width: 400px;
}

.crown {
  background-image: url('/src/assets/images/ugoals_crown.webp');
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  width: 125px;
  height: 16px;
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  z-index: 10;
}

.statistics-container {
  width: 100%;
  background-color: #292C69;
  font-family: Arial, sans-serif;
  color: #ffffff;
  padding: 0;
  border: 2px solid #feb500;
  box-shadow: 0 0 8px rgba(243, 156, 18, 0.3);
}

.header-section {
  background-color: #292C69;
  padding: 20px;
  padding-bottom: 0;
  margin-bottom: 0;
  border-bottom: 2px solid #33388A;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.title-header {
  margin: -20px -20px 8px -20px;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  position: relative;
  background-color: #20224D;
  background-image: radial-gradient(circle at 1px 1px, #232344 1px, transparent 0);
  background-size: 8px 8px;
  padding: 8px 16px;
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.05),
    inset 0 -1px 0 rgba(0, 0, 0, 0.1),
    0 2px 4px rgba(0, 0, 0, 0.1);
  border-top: 1px solid rgba(255, 255, 255, 0.05);
  border-bottom: 1px solid rgba(0, 0, 0, 0.2);

  .title {
    margin: 0;
    font-size: 18px;
    color: #ffffff;
    text-align: center;
    padding-right: 50px;
    padding-left: 50px;
  }

  .gender-selection {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    flex-direction: row;
    gap: 2px;

    .gender-button {
      width: 16px;
      height: 16px;
      padding: 0;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0.5;

      &:hover {
        opacity: 0.8;
      }

      &.active {
        opacity: 1;
      }

      img {
        width: 100%;
        height: 100%;
        object-fit: contain;
      }
    }
  }
}

.points-section {
  text-align: center;
  margin-bottom: 20px;

  .points {
    font-size: 24px;
    font-weight: bold;
    color: #ffffff;
  }
}

.stats-cards {
  display: flex;
  flex-direction: row;
  gap: 8px;
  margin-bottom: 20px;

  .stat-card {
    flex: 1;
    background-color: #353A8E;
    border: 2px solid;
    border-radius: 4px;
    padding: 20px 8px 10px 8px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s ease;
    position: relative;
    box-shadow:
      0 0 8px rgba(243, 156, 18, 0.3),
      inset 0 1px 0 rgba(255, 255, 255, 0.05);

    &.rare {
      border: 2px solid #F0B449
    }

    .card-value {
      position: absolute;
      top: -12px;
      left: 50%;
      transform: translateX(-50%);
      background-color: #292C69;
      font-size: 14px;
      font-weight: bold;
      padding: 2px 8px;
      border: 1px solid;
      border-radius: 2px;
      min-width: 20px;

      &.rare {
        color: #F0B449;
      }
    }

    .card-icon {
      font-size: 16px;
      margin-bottom: 4px;
      display: flex;
      justify-content: center;
      align-items: center;

      img {
        width: 16px;
        height: 16px;
        object-fit: contain;
      }
    }

    .card-text {
      font-size: 10px;
      font-weight: bold;
      color: white;
      line-height: 1.2;

      &.rare {
        color: #F0B449;
      }
    }
  }
}

.nav-tabs {
  display: flex;
  flex-direction: row;
  margin-bottom: 15px;
  border-bottom: 1px solid #7f8c8d;

  .tab-button {
    background: none;
    border: none;
    color: #95a5a6;
    font-size: 11px;
    font-weight: bold;
    padding: 8px 12px;
    cursor: pointer;
    transition: color 0.2s ease;

    &.active {
      color: #F0B449;
      border-bottom: 2px solid #F0B449;
    }

    &:hover:not(.active) {
      color: #ffffff;
    }
  }
}

.stats-grid {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  flex-direction: row;
  gap: 8px;
  max-height: 300px;
  overflow-y: auto;
  padding: 12px;
  background-color: #353A8E;

  .stat-grid-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: #292C69;
    border: 1px solid #3f5a70;
    border-radius: 4px;
    padding: 8px 4px;
    cursor: pointer;
    position: relative;
    transition: border-color 0.2s ease;
    width: 40px;
    min-height: 40px;

    &:hover {
      border-color: white;
    }

    &.rare {
      border-color: #F0B449;

      &:hover {
        border-color: white;
      }
    }

    .grid-item-value {
      position: absolute;
      top: -8px;
      left: 50%;
      transform: translateX(-50%);
      background-color: #353A8E;
      font-size: 10px;
      font-weight: bold;
      padding: 1px 6px;
      border: 1px solid #3f5a70;
      border-radius: 2px;
      min-width: 16px;
      text-align: center;

      &.rare {
        color: #F0B449;
        border-color: #F0B449;
      }
    }

    .grid-item-icon {
      display: flex;
      justify-content: center;
      align-items: center;

      img {
        width: 20px;
        height: 20px;
        object-fit: contain;
      }
    }
  }
}

.stats-list {
  max-height: 300px;
  overflow-y: auto;
  padding: 20px;
  padding-top: 0;
  background-color: #353A8E;

  .stat-item {
    display: flex;
    flex-direction: row;
    align-items: center;
    padding: 6px 0;
    border-bottom: 1px solid #3f5a70;
    cursor: pointer;

    &:last-child {
      border-bottom: none;
    }

    .stat-icon {
      width: 20px;
      text-align: center;
      margin-right: 8px;
      font-size: 12px;
    }

    .stat-name {
      flex: 1;
      font-size: 11px;
      line-height: 1.3;
      font-weight: bold;

      &.rare {
        color: #F0B449;
      }
    }

    .stat-value {
      font-size: 11px;
      font-weight: bold;
      margin-left: 8px;

      &.rare {
        color: #F0B449;
      }
    }
  }
}

// Scrollbar styling for webkit browsers
.stats-grid::-webkit-scrollbar,
.stats-list::-webkit-scrollbar {
  width: 6px;
}

.stats-grid::-webkit-scrollbar-track,
.stats-list::-webkit-scrollbar-track {
  background: #34495e;
}

.stats-grid::-webkit-scrollbar-thumb,
.stats-list::-webkit-scrollbar-thumb {
  background: #7f8c8d;
  border-radius: 3px;
}

.stats-grid::-webkit-scrollbar-thumb:hover,
.stats-list::-webkit-scrollbar-thumb:hover {
  background: #95a5a6;
}

// Responsive design
@media (max-width: 480px) {
  .nav-tabs {
    .tab-button {
      padding: 6px 8px;
      font-size: 10px;
    }
  }
}
</style>


