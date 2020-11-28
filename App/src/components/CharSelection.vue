<template>
  <div class="main">
    <h1>Select a character</h1>
    <ul class="char-selection">
      <li v-for="(character, key) in characters" v-bind:key="key">
        <a href="/#" @click="selectCharacter(character)">
          <div><img :src=characterBody(character)></div>
          <span>{{ character.name }}</span>
        </a>
      </li>
    </ul>
  </div>
</template>

<script>
import ApiService from "@/services/api.service";
import {characterEnum} from "@/enums/character";
import PlayerService from "@/services/player.service";

export default {
  name: 'CharSelection',
  props: {
  },
  data: () => {
    return {
      loading: false,
      daedalusId: null,
      characters: []
    }
  },
  methods: {
    characterBody: function(character) {
      return characterEnum[character.key] ? characterEnum[character.key].body : require('@/assets/images/items/todo.jpg');
    },
    selectCharacter: function(character) {
      PlayerService.selectCharacter(this.daedalusId, character.key)
          .then(() => {
            this.loading = false
          })
          .catch((error) => {
            console.error(error);
            this.loading = false;
          })
    },
  },
  beforeMount() {
    this.loading = true;
    ApiService.get('daedalus/available-characters')
        .then((response) => {
          this.daedalusId = response.data.daedalus;
          this.characters = response.data.characters;
          this.loading = false;
        })
  }
}
</script>

<style lang="scss" scoped>

h1 {
  margin: 15px;
  font-size: 150%;
  font-variant: small-caps;
}

.char-selection {
  flex-direction: row;
  flex-wrap: wrap;
  justify-content: space-evenly;

  & li a {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    min-width: 160px;
    padding: 12px;
    margin: 4px;
    color: white;
    font-size: .91em;
    font-weight: 700;
    letter-spacing: .03em;
    text-transform: uppercase;
    text-decoration: none;
    background: rgba(54, 76, 148, 0.35);
    @include corner-bezel(12px, 0px);

    & > div {

      position: relative;
      align-items: center;
      margin-right: 16px;
      & img { z-index: 2; }

      &::after {
        content: "";
        position: relative;
        left: 1px;
        bottom: 8px;
        width: 39px;
        height: 21px;
        background: url("~@/assets/images/figure.png") center no-repeat;
      }
    }

    &:hover, &:active { background: rgba(54, 76, 148, 0.65); }

  }
}

</style>



