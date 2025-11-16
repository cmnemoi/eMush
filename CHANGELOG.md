# [0.17.0](https://gitlab.com/eternaltwin/mush/mush/compare/0.16.2...0.17.0) (2025-11-16)


### Bug Fixes

* **action:** prevent takeoff when patrol ship is broken ([4136b6d](https://gitlab.com/eternaltwin/mush/mush/commit/4136b6d54668602c70686e96f4c4e98eb5beaed0))
* Add missing :female_admin:, :male_admin: and :neron: emotes ([f13c0d2](https://gitlab.com/eternaltwin/mush/mush/commit/f13c0d26dbd5c652e1f3e8dfcf0a58b0e044e85b))
* Add missing emote aliases and order aliases by their values ([bf6f20c](https://gitlab.com/eternaltwin/mush/mush/commit/bf6f20c933b75bfdd5e4d4474b6f1613f483c4cf))
* Bump Neron failure threads when reporting equipment issues ([ad91813](https://gitlab.com/eternaltwin/mush/mush/commit/ad918139bcf53f391978715eef6b124df4f18838))
* Character cycle stats are correctly linked to the user, to take account of transfers ([18e316e](https://gitlab.com/eternaltwin/mush/mush/commit/18e316e22910c3286bf11002d621166be7cf3174))
* chat performance ([509e8f7](https://gitlab.com/eternaltwin/mush/mush/commit/509e8f7294622e5279579a594ee2b9174ae1d30e))
* **communication:** close invitation panel when changing channel ([3a7185c](https://gitlab.com/eternaltwin/mush/mush/commit/3a7185c34bb0aa02392baaf275a5bb95e8018789))
* **communication:** update current channel reference to refresh UI ([27db64a](https://gitlab.com/eternaltwin/mush/mush/commit/27db64a91cba1a44582bdc737d01f5f0f37bf85b))
* Completing Mushicide soap with the soap in the inventory will properly give a Super soap ([6df980b](https://gitlab.com/eternaltwin/mush/mush/commit/6df980b33110f1c14bf5b203dacf9cc37b9e876d))
* Daedalus does not start with a jumpkin during Halloween ([d134fd9](https://gitlab.com/eternaltwin/mush/mush/commit/d134fd91774f1ac2160adfd46d01521e0a5eb301))
* Display Terrence cycles stat ([9f19120](https://gitlab.com/eternaltwin/mush/mush/commit/9f19120b81743afe2ce682d150732ea43d424dc5))
* Do not clear message after multiple line breaks when caret go out of screen ([da7952f](https://gitlab.com/eternaltwin/mush/mush/commit/da7952fbe4ffa5f04969e60a58a4650a05f6fa9c))
* Do not display JS skin when player is laid down in a room with two sofas ([37fa626](https://gitlab.com/eternaltwin/mush/mush/commit/37fa6266ad0c08f6a71ca5471ed08fb2c035ac61))
* Do not print enter/exit logs for planet or patrolships ([4acaf78](https://gitlab.com/eternaltwin/mush/mush/commit/4acaf78e2cb9a854e9204829af798613995c8fc4))
* Do not tell everyone is dead when exploration ends on happy end ([ccf64be](https://gitlab.com/eternaltwin/mush/mush/commit/ccf64befb9c7b84a3975e3eb526fde28e44877b7))
* doesn't show the disable log when a terrence move from the room while anonymous ([1b8e754](https://gitlab.com/eternaltwin/mush/mush/commit/1b8e7548fca21cefc84c2cf5882411c1cf5d67d7))
* Fix 403 error on login ([aa4d3eb](https://gitlab.com/eternaltwin/mush/mush/commit/aa4d3eb7fbb60525592834d6867e459d58c358a3))
* Fix crash when displaying deactivated artefact specialist stat ([f94f241](https://gitlab.com/eternaltwin/mush/mush/commit/f94f2418711e4432364763dd29fb06e1bf95f60d))
* fix terminal tips on other panels ([599d553](https://gitlab.com/eternaltwin/mush/mush/commit/599d553227b0357fc8a0336ae1e7d468a2f9fb2c))
* freeze when reloading the room ([3341548](https://gitlab.com/eternaltwin/mush/mush/commit/3341548e21a19d903288d099caf79922a94f02df))
* Graft fruit in player's inventory instead of room ([cc84233](https://gitlab.com/eternaltwin/mush/mush/commit/cc8423344b92fc873fc4e556156764dddef71cd2))
* Improve version mismatch detection to tell people to clear cache ([36d0bc9](https://gitlab.com/eternaltwin/mush/mush/commit/36d0bc939f6893d44867fd95010d038ca382c4f4))
* Instead of scrolling, let's fit content of tables tight! ([f95d353](https://gitlab.com/eternaltwin/mush/mush/commit/f95d353dd65db9576db96c47f41784e0e1290f45))
* Losing a heavy item in expedition will properly remove Burdened status ([bf157be](https://gitlab.com/eternaltwin/mush/mush/commit/bf157be34f63a0849e8fa1375833864ebd01d84b))
* More protections on private channel number ([1773aa5](https://gitlab.com/eternaltwin/mush/mush/commit/1773aa5dbfe67846f19b6edabb5e8244923c7097))
* NERON announcement announcing new merchant will be sync with cycle change ([5de99f7](https://gitlab.com/eternaltwin/mush/mush/commit/5de99f797df35d901e0fe58bb4214a6c8cbcf1e0))
* new Crowdin translations ([a8c71e9](https://gitlab.com/eternaltwin/mush/mush/commit/a8c71e933c9e330bdf1e3b0a840f454b8acec741))
* new Crowdin translations ([7759504](https://gitlab.com/eternaltwin/mush/mush/commit/77595043a15ccbf337df4212d733cc873628eab0))
* new Crowdin translations ([ed89a35](https://gitlab.com/eternaltwin/mush/mush/commit/ed89a35cdfb3b6e69ef3f0a55deafed6b226c2b7))
* Remove 'twinoid_' prefix from EmoteTwinoidEnum ([c7e90a1](https://gitlab.com/eternaltwin/mush/mush/commit/c7e90a10d1fcc9e2af5b324f7609b7848ede9b72))
* remove action parameter placeholder from action history logs (confident, premonition, torture) ([289813c](https://gitlab.com/eternaltwin/mush/mush/commit/289813c6aa072c1efddc2c73a673969e9a7d3fa2))
* Remove spoiler vector of attacks for moderators ([40994a9](https://gitlab.com/eternaltwin/mush/mush/commit/40994a9570cd97619ee8e17cc94181f33d39250f))
* Rich text editor now correctly display line break ([9091c30](https://gitlab.com/eternaltwin/mush/mush/commit/9091c30fa855af181eac8f7cb43b4fe034c6e706))
* scan goes to 99% after one fail while planet scanner equipment is functional ([d879f56](https://gitlab.com/eternaltwin/mush/mush/commit/d879f56d32ce7a0e36c66ce965c6b538e0cc37da))
* Sync food destruction log time with cycle change ([dfbd021](https://gitlab.com/eternaltwin/mush/mush/commit/dfbd021441d092d7eef81f0b0fb605563a73e552))
* take_cat not recorded to action history ([8f4fd6f](https://gitlab.com/eternaltwin/mush/mush/commit/8f4fd6f987ccff734f245b16c1d0a038e62380f8))
* You cannot move with right click ([e2a0762](https://gitlab.com/eternaltwin/mush/mush/commit/e2a0762222a8fef4b392d579ca420f0455aa63a7))
* You cannot participate in an unproposed project ([c0b0ec9](https://gitlab.com/eternaltwin/mush/mush/commit/c0b0ec9f384c4898e18c6b6b149127803f0c0169))


### Features

* Add Aeon as a contributor ([1c4a3fa](https://gitlab.com/eternaltwin/mush/mush/commit/1c4a3faaa0b6f58abd08880c5559dca9553d56f7))
* Add Muxxu and Twinoid emotes. Allow defining each emote size. ([3892704](https://gitlab.com/eternaltwin/mush/mush/commit/3892704851ed0e78329e1514408ec708ec26dacc))
* **communication:** isolate typedMessage per channel ([8a95df9](https://gitlab.com/eternaltwin/mush/mush/commit/8a95df98c0a4a7823305287b99552bbfb40d4920))
* extend terminal tips by default if the player is a beginner ([df48c85](https://gitlab.com/eternaltwin/mush/mush/commit/df48c85e72a0d0a8f153137a92a5a03fa8d21fbf))
* new admin action force cycle change ([e0c863c](https://gitlab.com/eternaltwin/mush/mush/commit/e0c863c7532f37b42306c79fcfb72b42af9e099b))
* new admin action force exploration step ([c1e6b5d](https://gitlab.com/eternaltwin/mush/mush/commit/c1e6b5d1a841f9af8fc52063f133e0d4f696e0d4))
* Replace old french Twinpedia link to eMushpedia wiki in tips channel ([3c8acca](https://gitlab.com/eternaltwin/mush/mush/commit/3c8acca59b67638cec1a0da56c52a336d6e50737))
* Rework the rich text editor layout and add multiple emotes ([02b2da2](https://gitlab.com/eternaltwin/mush/mush/commit/02b2da23cd5cb84dc0fd2d626814985875c95e0f))
* splashproof makes showers cheaper (does not stack with soap) ([450ea08](https://gitlab.com/eternaltwin/mush/mush/commit/450ea0856c1c9890e36755bcdf9984f8f15b1c40))
* spread fire trigger next cycle (42) ([e660ef7](https://gitlab.com/eternaltwin/mush/mush/commit/e660ef7f7050de93bbe19755179524de31f22628))
* **stat:** Butcher ([f31eb1d](https://gitlab.com/eternaltwin/mush/mush/commit/f31eb1d27d5527a0962597c8af93ae75b6fcfba4))
* **stat:** Communications Expert ([67571a1](https://gitlab.com/eternaltwin/mush/mush/commit/67571a1e01e710f4fefbbe69e98281664a5ba480))
* **stat:** Day max ([6878f0e](https://gitlab.com/eternaltwin/mush/mush/commit/6878f0e128be92879f1d98352dde76a5c1205bcb))
* **stat:** Day X reached stats ([245f3de](https://gitlab.com/eternaltwin/mush/mush/commit/245f3deecfb371bd504b5c0fc9dfb916f03eadcd))
* **stat:** Drugs taken ([76e6dc0](https://gitlab.com/eternaltwin/mush/mush/commit/76e6dc0fd50e2eb01da314e36d660b6d30ae663c))
* **stat:** Hunter down ([d27a9f8](https://gitlab.com/eternaltwin/mush/mush/commit/d27a9f80b434b80fdd3de76ed5c1358e37baf534))
* **stat:** Kivanc Terzi contacted ([8fd9647](https://gitlab.com/eternaltwin/mush/mush/commit/8fd964788f8bb0e669dd040581873dbf87eed5c9))
* **stat:** Likes ([d1b8d95](https://gitlab.com/eternaltwin/mush/mush/commit/d1b8d953b707798f67925ee9b416a891e8251aaf))
* **stat:** Mush Genome ([07e4850](https://gitlab.com/eternaltwin/mush/mush/commit/07e485022be17d7224d259ca69b8fca4b80af5bd))
* **stat:** PILGRED is back! ([75366b0](https://gitlab.com/eternaltwin/mush/mush/commit/75366b0ef51f9883365dae68e231bb5c8a8ceea0))
* **stat:** Politician ([dde120f](https://gitlab.com/eternaltwin/mush/mush/commit/dde120fd7af4f5528e75966ed2f78dc81f862ac8))
* **stat:** Rebels ([1c1319d](https://gitlab.com/eternaltwin/mush/mush/commit/1c1319d8227aeb3171a27fbdc0e504957ecabbf1))
* **stat:** Surgeon ([8825da0](https://gitlab.com/eternaltwin/mush/mush/commit/8825da011ccdb9918261b5cbd7ae92ed61b8380b))
* Update rules for beta test phase ([44631cd](https://gitlab.com/eternaltwin/mush/mush/commit/44631cd43814d32a3cc59011dc3768a676e34e03))

## [0.16.2](https://gitlab.com/eternaltwin/mush/mush/compare/0.16.1...0.16.2) (2025-11-05)


### Bug Fixes

* disable anti-spam ([b2a2b53](https://gitlab.com/eternaltwin/mush/mush/commit/b2a2b530d4453c3eaa6ba19dde476752031439a6))
* room traps can now be triggered by repairing doors ([e66829b](https://gitlab.com/eternaltwin/mush/mush/commit/e66829b802433fe2c0b73df3815fd7807f92b823))

## [0.16.1](https://gitlab.com/eternaltwin/mush/mush/compare/0.16.0...0.16.1) (2025-11-05)


### Bug Fixes

* revert chat live preview (dbd7eb1) ([b6a5a3b](https://gitlab.com/eternaltwin/mush/mush/commit/b6a5a3babeaa2b616b407ad70f3b8cb44b963497))

# [0.16.0](https://gitlab.com/eternaltwin/mush/mush/compare/0.15.3...0.16.0) (2025-11-04)


### Bug Fixes

* Give mission stat is not incremented twice ([55a9e60](https://gitlab.com/eternaltwin/mush/mush/commit/55a9e6058c0a34d5825d73bb6cf0831490cbf7da))


### Features

* chat live preview ([dbd7eb1](https://gitlab.com/eternaltwin/mush/mush/commit/dbd7eb1a340b7a0e4f14bb9ab7ac4e720c51b6a1))

## [0.15.3](https://gitlab.com/eternaltwin/mush/mush/compare/0.15.2...0.15.3) (2025-11-04)


### Bug Fixes

* new Crowdin translations ([5c1c8b3](https://gitlab.com/eternaltwin/mush/mush/commit/5c1c8b304d362e1096210ba5fa0799f0cbaca4ae))
