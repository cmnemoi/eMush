<template>
  <div class="char-panel">

    <div class="char-sheet">
      <img class="avatar" src="@/assets/images/char/stephen_seagull.jpg" alt="avatar">

      <ul class="statuses"></ul>

      <div class="health-points">
        <div class="life">
          <p><img src="@/assets/images/lp.png" alt="lp">12</p>
          <ul>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="empty"></li>
            <li class="empty"></li>
          </ul>
            
        </div>
        <div class="morale">
          <p><img src="@/assets/images/moral.png" alt="mp">4</p>
          <ul>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="full"></li>
            <li class="empty"></li>
            <li class="empty"></li>
            <li class="empty"></li>
            <li class="empty"></li>
            <li class="empty"></li>
            <li class="empty"></li>
            <li class="empty"></li>
            <li class="empty"></li>
            <li class="empty"></li>
            <li class="empty"></li>
          </ul>          
        </div>
      </div>
      <ul class="inventory">
        <li class="empty"></li>
        <li class="empty"></li>
        <li class="empty"></li>
      </ul>
      <div class="interactions">
        <p>Schrödinger :</p>
        <ul>
          <li><a href="#">Lâcher</a></li>
          <li><a href="#">Examiner</a></li>
          <li><a href="#">1 <img src="@/assets/images/pa.png" alt="ap"> Caresser</a></li>
          <li class="crossed"><a href="#">1 <img src="@/assets/images/pa.png" alt="ap"> Câlin infectieux</a></li>
        </ul>
      </div>
    </div>
    
    <div class="column">
      <ul class="skills">
        <li><img src="@/assets/images/skills/cook.png" alt="cook"></li>
        <li><img src="@/assets/images/skills/sturdy.png" alt="sturdy"></li>
        <li><img src="@/assets/images/skills/opportunist.png" alt="opportunist"></li>
        <li><img src="@/assets/images/skills/sturdy.png" alt="sturdy"></li>
      </ul>

      <div class="actions-sheet">
        <img src="@/assets/images/pam.png" alt="pam">
        <div class="action-points">
          <div class="actions">
            <ul>
              <li class="full"></li>
              <li class="full"></li>
              <li class="full"></li>
              <li class="full"></li>
              <li class="empty"></li>
              <li class="empty"></li>
              <li class="empty"></li>
              <li class="empty"></li>
              <li class="empty"></li>
              <li class="empty"></li>
              <li class="empty"></li>
              <li class="empty"></li>
            </ul>
          </div>
          <div class="movements">
            <ul>
              <li class="full"></li>
              <li class="full"></li>
              <li class="full"></li>
              <li class="full"></li>
              <li class="full"></li>
              <li class="full"></li>
              <li class="full"></li>
              <li class="full"></li>
              <li class="full"></li>
              <li class="empty"></li>
              <li class="empty"></li>
              <li class="empty"></li>
            </ul>
          </div>
        </div>
        <ul class="specials">
          <li><img src="@/assets/images/pa_cook.png">x6</li>
        </ul>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: "CharPanel",
  props: {
  }
}
</script>

<style lang="scss" scoped>



.char-panel {
  flex-direction: row;

  & .char-sheet {
    width: 176px;
    min-height: 459px;
    padding: 5px;
    border-top-left-radius: 4px;
    background: rgba(54,76,148,.35);

    & .avatar {
      width: 166px;
      height: auto;
    }

    & .health-points {
      flex-direction: row;
      margin: 3px 0;

      & .life, & .morale {
        flex-direction: row;
        align-items: center;
        margin-right: 3px;

        & p, & ul {
          display: flex;
          flex-direction: row;
          align-items: center;
          border: 1px solid #4077b5;
          border-radius: 3px;
          background: rgba(58,106,171,1);
          box-shadow: 0 0 4px 1px inset rgba(28,29,56,1);
        }

        & p {

          min-width: 24px;
          height: 15px;
          margin: 1px;
          padding-right: 1px;
          font-size: .5em;
          letter-spacing: .03em;
          border-right-width: 0;

          
          & img {
            width: 11px;
            height: 13px;
            margin-right: 1px;
          }
        }

        & ul {
          /*min-width: 59px;*/
          height: 11px;
          border-radius: 2px;
          margin-left: -2px;
          border-left-width: 0;

          & li {
            width: 3px;
            height: 5px;
            margin-right: 1px;
            background: rgba(138,170,44,1);
            box-shadow: 1px 1px 0 0 inset rgba(255,255,255,.7);

            &.empty {
              background: rgba(37,72,137,1);
              box-shadow: 1px 1px 0 0 inset rgba(78,154,255,.7);
            }
          }
        }
      }
    }

    & .inventory {
      display: flex;
      flex-direction: row;

      & li {
        width: 54px;
        height: 54px;
        margin-right: 1px;
        border: 1px solid #4077b5;
        border-radius: 3px;
        background: rgba(58,106,171,1);
        box-shadow: 0 0 8px 2px inset rgba(17,56,128,1);
      }
    }

    & .interactions {
      margin-top: 12px;

      & > p {
        margin: 0 0 4px 0;
        font-size: .83em;
        letter-spacing: .03em;
        font-variant: small-caps;
      }

      & ul {
        display: flex;
        flex-direction: column;

        & li a {
          display: flex;
          flex-direction: row;
          align-items: center;
          justify-content: center;
          min-height: 19px;
          width: auto;
          margin: 1px;
          padding-bottom: 2px;
          border-radius: 1px;
          color: white ;
          font-size: .83em;
          font-weight: 700;
          letter-spacing: .03em;
          text-decoration: none;
          font-variant: small-caps;
          text-align: center;
          text-shadow: 0 0 4px #15273C, 0 0 4px #15273C;
          box-shadow: 0 0 0 1px rgba(12,18,137,1);
          background: #003cc3;
          background: linear-gradient(0deg, #003cc3 2%, #0074df 6%, #0074df 46%, #008ee5 54%, #008ee5 94%, #499ce5 96%);

          & img {
            padding: 0 .3em;
          }

          &:hover{
            background: #003cc3;
            background: linear-gradient(0deg, #0061D5 2%, #00A0E9 6%, #00A0E9 46%, #00B3ED 54%, #00B3ED 94%, #40B5EC 96%);
          }
        }
        & li.crossed a {
          opacity: .5;
          text-decoration: line-through;
        }
      }
    }
  }

  & .column {
    justify-content: space-between;

    & .skills {
      display: flex;
      flex-direction: column;
      min-width: 32px;
      float: right;

      & li {

        display: flex;
        align-items: center;
        /* justify-content: center; */
        width: 30px;
        height: 34px;
        padding-right: 3px;
        margin-bottom: 7px;
        background: transparent url('~@/assets/images/skills/skillblock_gold.png') center left no-repeat;
        border-left: 1px solid #191A53;

        &:nth-child(1) {
          background: transparent url('~@/assets/images/skills/skillblock.png') center left no-repeat;
        }
        &:nth-child(2) {
          background: transparent url('~@/assets/images/skills/skillblock_once.png') center left no-repeat;
        }

      }
    }

    & .actions-sheet {
      align-items: center;
      justify-content: flex-start;
      width: 28px;
      min-height: 134px;
      padding: 5px 5px 5px 0;
      border-top-right-radius: 4px;
      background: rgba(54,76,148,.35);

      & > img { margin: 3px; }

      & .action-points {
        flex-direction: row;

        & > div {
          border-radius: 3px;
          background: rgb(17,18,48);
          background: linear-gradient(0deg, rgba(17,18,48,1) 0%, rgba(53,56,152,1) 100%);

          & ul {
            $border: 1px;
            display: block;
            flex-direction: column;
            align-items: center;
            margin: 1px;
            padding: 1px;
            border-radius: 2px;
            border: 1px solid;
            border-image-slice: 1;
            border-width: 1px;
            border-image-source: linear-gradient(0deg, rgba(13,44,115,1) 0%, rgba(89,164,216,1) 100%);
            background: rgb(17,18,48);
            background: linear-gradient(0deg, rgba(17,18,48,1) 0%, rgba(53,56,152,1) 100%);

            & li {
              width: 5px;
              height: 6px;
              border-bottom: 1px solid black;
              background: rgba(138,170,44,1);
              box-shadow: 0 -1px 0 0 inset rgba(0,0,0,.4);
            }
          }
        }

        & .movements ul li {
          background: rgb(0,255,228);
          background: linear-gradient(135deg, rgba(255,255,255,1) 5%, rgba(0,255,228,1) 20%);

          &.empty {
            background: rgb(14,62,56);
            background: linear-gradient(135deg, rgba(18,85,106,1) 5%, rgba(14,62,56,1) 20%);
          }
        }

        & .actions ul li {
          background: rgb(255,85,153);
          background: linear-gradient(135deg, rgba(255,255,255,1) 5%, rgba(255,85,153,1) 20%);

          &.empty {
            background: rgb(64,0,0);
            background: linear-gradient(135deg, rgba(77,17,32,1) 5%, rgba(64,0,0,1) 20%);
          }
        }
      }

      & .specials li {
        display: flex;
        flex-direction: row;
        align-items: baseline;
        margin: 2px 0;
        font-size: .65em;
        font-weight: 700;

        & img { margin-right: -3px; }
      }
    }
  }
}

</style>