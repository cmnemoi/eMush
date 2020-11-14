<template>
<div class="comms-panel">
  <div class="tabs-container">
    <ul class="tabs">
      <li>
        <img src="@/assets/images/comms/tip.png">
        <span class="title">Tips</span>
      </li>
      <li>
        <img src="@/assets/images/comms/local.png">
        <span class="title">Room events</span>
      </li>
      <li class="active">
        <img src="@/assets/images/comms/wall.png">
        <span class="notifier">192</span>
        <span class="title">Discussion</span>
      </li>
      <li>
        <img src="@/assets/images/comms/fav.png">
        <span class="notifier">14</span>
        <span class="title">Favorites</span>
      </li>
      <li>
        <img src="@/assets/images/comms/mush.png">
        <span class="notifier">1</span>
        <span class="title">Mush discussion</span>
      </li>
      <li>
        <img src="@/assets/images/comms/private.png">
        <span class="title">Private #1</span>
      </li>
      <li>
        <img src="@/assets/images/comms/newtab.png">
      </li>
    </ul>
  </div>
  <div class="chatbox-container">
    <textarea class="chat-input" placeholder="Type your message here!"></textarea>
    <div class="chatbox">
      <div class="unit">
        <div class="main-message">
          <img src="@/assets/images/char/body/jin_su.png">
          <p><span class="author">Jin Su :</span>It would be nice to set up BIOS. Any IT Expert up to it?</p>
          <ul class="reacts">
            <li><a href="#">Reply</a></li>
            <li><a href="#">Favorite</a></li>
            <li><a href="#">!!</a></li>
            <li class="timestamp">moments ago</li>
          </ul>
          
        </div>
        <div>
          <p><img src="@/assets/images/char/head/jin_su.png"><span class="author">Jin Su :</span><strong><em>Share BIOS parameters</em></strong><br />
            <strong>Foodstuff Destruction</strong> : No<br />
            <strong>CPU Priority</strong> : Astronavigation<br />
            <strong>Crew lock</strong> : Piloting<br />
            <strong>Vocoder Announcements</strong> : Unauthorized<br />
            <strong>Report Deaths</strong> : Yes<br />
            <strong>Inhibit DMZ-CorePeace</strong> : Yes<br />
            <em>Find us an O2 planet!</em>
          </p>
          <ul class="reacts">
            <li><a href="#">Reply</a></li>
            <li><a href="#">Favorite</a></li>
            <li><a href="#">!!</a></li>
            <li class="timestamp">moments ago</li>
          </ul>
          
        </div>
      </div>

      <div class="unit">
        <div class="main-message neron new">
          <img src="@/assets/images/comms/neron_chat.png">
          <p><span class="author">NERON :</span><strong>Gravity Simulator</strong> engaged, nice. <em>Move Humans more fastly</em>. You not get dirt everywhere! [Hax!]</p>
          <ul class="reacts">
            <li><a href="#">Reply</a></li>
            <li><a href="#">Favorite</a></li>
            <li><a href="#">!!</a></li>
            <li class="timestamp">25min</li>
          </ul>
        
        </div>
        <div class="new">
          <p><img src="@/assets/images/char/head/hua.png"><span class="author">Hua :</span>Dirty dismantling the shower.</p>
          <ul class="reacts">
            <li><a href="#">Reply</a></li>
            <li><a href="#">Favorite</a></li>
            <li><a href="#">!!</a></li>
            <li class="timestamp">~1h</li>
          </ul>
        </div>
        <div class="new">
          <p><img src="@/assets/images/char/head/paola.png"><span class="author">Paola :</span>Thanks Hua, got my sofa ready on the bridge now :)</p>
          <ul class="reacts">
            <li><a href="#">Reply</a></li>
            <li><a href="#">Favorite</a></li>
            <li><a href="#">!!</a></li>
            <li class="timestamp">~1h</li>
          </ul>        </div>
      </div>

      <div class="unit">
        <div class="main-message new">
          <img src="@/assets/images/char/body/ian.png">
          <p><span class="author">Ian :</span><strong><em>Piloting</em></strong></p>
          <ul class="reacts">
            <li><a href="#">Reply</a></li>
            <li><a href="#">Favorite</a></li>
            <li><a href="#">!!</a></li>
            <li class="timestamp">~5d</li>
          </ul>
        </div>
        <div>
          <p><img src="@/assets/images/char/head/jin_su.png"><span class="author">Jin Su :</span>So far eight hunters shot total (3 + 5), no scrap collected yet.</p>
          <ul class="reacts">
            <li><a href="#">Reply</a></li>
            <li><a href="#">Favorite</a></li>
            <li><a href="#">!!</a></li>
            <li class="timestamp">~3d</li>
          </ul>
        </div>
        <div>
          <p><img src="@/assets/images/char/head/ian.png"><span class="author">Ian :</span>Excellent sir, I can see why they have you training the new pilots :P</p>
          <ul class="reacts">
            <li><a href="#">Reply</a></li>
            <li><a href="#">Favorite</a></li>
            <li><a href="#">!!</a></li>
            <li class="timestamp">~3d</li>
          </ul>
        </div>
        <div>
          <p><img src="@/assets/images/char/head/jin_su.png"><span class="author">Jin Su :</span>Kind of you to say so, yet I sadly can't agree. In fact I find our hull's exellency wanting. It shall be restored once we collected scrap and built the oscilloscope.</p>
          <ul class="reacts">
            <li><a href="#">Reply</a></li>
            <li><a href="#">Favorite</a></li>
            <li><a href="#">!!</a></li>
            <li class="timestamp">~1d</li>
          </ul>
        </div>
        <div>
          <p><img src="@/assets/images/char/head/jin_su.png"><span class="author">Jin Su :</span>Scrap collected, still a bit left.</p>
          <ul class="reacts">
            <li><a href="#">Reply</a></li>
            <li><a href="#">Favorite</a></li>
            <li><a href="#">!!</a></li>
            <li class="timestamp">~1d</li>
          </ul>
        </div>
        <div>
          <p><img src="@/assets/images/char/head/paola.png"><span class="author">Paola :</span>This topic is discriminating, I vote for a new one simply labeled "hunters" or "attackers"</p>
          <ul class="reacts">
            <li><a href="#">Reply</a></li>
            <li><a href="#">Favorite</a></li>
            <li><a href="#">!!</a></li>
            <li class="timestamp">~1d</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script>
export default {
  name: "CommsPanel",
  props: {
  }
}
</script>

<style lang="scss" scoped>

.comms-panel {
  flex-direction: row;
  position: relative;
  width: 404px;
  height: 460px;

  & .tabs-container {
    position: relative;
    align-items: flex-start;
    justify-content: space-between;
    min-width: 34px;

    & .tabs {
      position: absolute;
      display: flex;
      flex-direction: column;
      width: 34px;
      min-height: 100%;
      padding: 2px;
      background: #74CBF3;
      z-index: 2;

      transition: width .2s 0s;

      &:hover { width: 210px; transition: width .2s .6s; }
      &:hover .notifier:before { opacity: 0; transition: opacity .2s .6s; }

      & li {
        position: relative;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: flex-start;
        cursor: pointer;
        min-width: 28px;
        min-height: 28px;
        margin: 1px;
        padding-right: 8px;
        border-radius: 2px;
        background: rgba(54, 76, 148, .5);
        @include corner-bezel(6.5px, 0px);

        & img { margin: 6px; }

        & .title {
          font-size: .85em;
          font-weight: 700;
          font-variant: small-caps;
          letter-spacing: .025em;
          white-space: nowrap;
          text-shadow: 0 0 3px  rgba(54, 76, 148, 1), 0 0 3px  rgba(54, 76, 148, 1);
          margin: 3px;
        }

        & .notifier {
          text-align: center;
          font-size: .65em;
          padding: 2px 4px;
          margin: 0 3px;
          border-radius: 12px;
          font-weight: 700;
          background: #cf1830;
          border: 2px solid rgba(255, 255, 255, .3);

          &:before {
            content:"";
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 10px;
            height: 10px;
            border-radius: 12px;
            background: #cf1830;
            border: 2px solid rgba(255, 255, 255, .4);
            transition: opacity .2s 0s;
          }
        }

        &:hover, &:active {
          background: rgba(54, 76, 148, 1);
        }

        &.active { background: rgba(54, 76, 148, 1); }
        &:last-child { opacity: .3; }
      }
    }
  }

  & .cycle-time {
    flex-direction: row;
    align-items: center;
    font-size: .7em;
    font-variant: small-caps;
    margin: 0 12px;

    & img { margin-right: 3px; }
  }
  
  & .chatbox-container {
    position: relative;
    height: 460px;
    font-size: .8em;
    line-height: initial;
    background: rgba(194, 243, 252, 1);
    @include corner-bezel(0px, 6.5px, 0px);

    & textarea {
      position: sticky;
      resize: vertical;
      min-height: 28px;
      max-height: 80%;
      margin: 6px 6px 4px 6px;
      padding: 5px;
      opacity: .85;

      box-shadow: 0px 1px 0px white;
      border: 1px solid #aad4e5;
      border-radius: 3px;

      &:active, &:focus {
        opacity: 1;
      }
    }

    & .chatbox {
      overflow: auto;
      padding: 7px;
      color: #090a61;
      border-top: 1px solid #aad4e5;

      & .unit {
        display: block;
        align-items: flex-end;
        padding: 5px 0;
        border-bottom: 1px solid rgb(170, 212, 229);

        & div {
          $react-transition-time: .4s;
          $react-transition-delay: .8s;

          position: relative;
          display: block;
          clear: both;

          & p:not(.timestamp) {
            position:relative;
            margin: 3px 0;
            padding: 4px 6px;
            border-radius: 3px;
            background: white;

            & .author {
              color: #2081e2;
              font-weight: 700;
              font-variant: small-caps;
              padding-right: .25em; 
            }

            & em {color: #cf1830;}
          }

          & .reacts {
            position: absolute;
            z-index: 2;
            right: 0;
            bottom: 0;
            border-radius: 2px;
            font-size: .85em;
            font-style: italic;
            opacity: .5;
            float: right;
            background: transparentize(white, 1);
            transition: all ($react-transition-time / 2);

            & li {
              padding: 1px 3px;
              transition: all ($react-transition-time / 2);
              &:not(.timestamp) { visibility: none; opacity: 0; }
            }

            & a {
              color: inherit;
              text-decoration: none;
              font-weight: 700;
              &:hover, &:focus { text-decoration: underline; }
            }
          }

          &:hover .reacts, &:focus .reacts {
            opacity: 1;
            bottom: -8px;
            background: white;
            transition: all $react-transition-time $react-transition-delay;

            & li {
              visibility: visible;
              opacity: 1;
              transition:
                visibility 0s $react-transition-delay, opacity $react-transition-time $react-transition-delay;
            }
          }

          &.new:not(.neron) p, &.new.neron {
            border-left: 2px solid #EA9104;

            &::after {
              content:"";
              position: absolute;
              top: 0;
              bottom: 0;  
              left: -6px;
              width: 11px;
              background: transparent url('~@/assets/images/comms/thinklinked.png') center no-repeat;
            }
          }

          &.main-message {
            & img {margin: 2px; float: left;}

            & p { margin-left: 36px; min-height: 52px;}

            & p::before { /* bubble triangle*/
              $size: 8px;
              content:"";
              position: absolute;
              top: 4px;
              left: -$size;
              width: 0px;
              height: 0px;
              border-top: $size solid transparent;
              border-bottom: $size solid transparent;
              border-right: $size solid white;
            }

            &.new p {
              &::before { border-right-color: #EA9104 }
            }
          }

          &.neron {
            padding: 2px 4px;
            border-radius: 4px;
            background: #74CBF3;

            & p {
              min-height: 36px;
              padding: 0 0 0 8px;
              font-variant: small-caps;
              color: inherit;
              background: transparent;
              & .author { color: inherit; }
              &::before { content: none; }
            }
          }

          &:not(.main-message) {
            margin-left: 50px;
            & img { margin-right: 3px; }
            & p {margin-top: 12px;}

            & p::before { /* bubble triangle*/
              $size: 8px;
              content:"";
              position: absolute;
              top: -$size;
              left: 4px ;
              width: 0px;
              height: 0px;
              border-left: $size solid transparent;
              border-right: $size solid transparent;
              border-bottom: $size solid white;
            }

/* MESSAGES LINKTREE */

            &::before {
              --border-radius: 5px;
              content: "";
              position: absolute;
              top: calc( -12px - var(--border-radius) );
              left: -36px;
              width: calc( 28px + var(--border-radius) );
              height: calc( 26px + var(--border-radius) );
              border-left: 1px solid #aad4e5;
              border-bottom: 1px solid #aad4e5;
              border-radius: var(--border-radius);

              clip-path: polygon(
                0 var(--border-radius),
                calc(100% - var(--border-radius)) var(--border-radius),
                calc(100% - var(--border-radius)) 100%,
                0 100%
              );
            }

            &:not(:last-child)::after {
              --border-radius: 5px;
              content: "";
              position: absolute;
              top: 13px;
              left: -36px;
              width: calc( 28px + var(--border-radius) );
              bottom: calc( -4px - var(--border-radius) );
              border-left: 1px solid #aad4e5;
              border-top: 1px solid #aad4e5;
              border-radius: var(--border-radius);

              clip-path: polygon(
                0 0,
                calc(100% - var(--border-radius)) 0,
                calc(100% - var(--border-radius)) calc(100% - var(--border-radius)),
                0 calc(100% - var(--border-radius))
              );
            }
          }
        }
      }
    }
  }

/* SCROLLBAR STYLING */

  & .chatbox, {
    --scrollbarBG: white;
    --thumbBG: #090a61;

    scrollbar-width: thin;
    scrollbar-color: var(--thumbBG) var(--scrollbarBG);
    &::-webkit-scrollbar { width: 6px; }
    &::-webkit-scrollbar-track { background: var(--scrollbarBG); }
    &::-webkit-scrollbar-thumb { background-color: var(--thumbBG); }
  }
}

</style>