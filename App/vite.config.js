/// <reference types="vitest" />
import { defineConfig } from 'vite';
import { fileURLToPath } from 'url';    
import typescript from '@rollup/plugin-typescript';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from "vite-plugin-pwa";
import path from 'path';

const filename = fileURLToPath(import.meta.url);
const pathSegments = path.dirname(filename);
const STATIC_DIR = 'public';

export default defineConfig({
  plugins: [
    typescript(),
    vue(),
    VitePWA({
      registerType: "autoUpdate",
      includeAssets: ["favicon.ico", "apple-touch-icon.png", "masked-icon.svg"],
      manifest: {
        name: "eMush - Jeu multijoueur de coopération : trahisons et survie dans l'espace",
        short_name: "eMush",
        description: "Vous allez vous réveiller sur le vaisseau spatial Daedalus avec 15 autres joueurs. Seul problème : deux d'entre eux sont infectés par un parasite, le Mush ! 🍄 Luttez pour votre survie dans la plus grande épopée de space opera de l'Humanité, depuis tous vos appareils !",
        theme_color: "#ffffff",
        icons: [
          {
            "src": "pwa-64x64.png",
            "sizes": "64x64",
            "type": "image/png"
          },
          {
            "src": "pwa-192x192.png",
            "sizes": "192x192",
            "type": "image/png"
          },
          {
            "src": "pwa-512x512.png",
            "sizes": "512x512",
            "type": "image/png"
          },
          {
            "src": "maskable-icon-512x512.png",
            "sizes": "512x512",
            "type": "image/png",
            "purpose": "maskable"
          }
        ]
      },
      workbox: {
        cleanupOutdatedCaches: false,
        maximumFileSizeToCacheInBytes: 3000000
      },
      devOptions: {
        enabled: true
      }
    })
  ],
  define: {
    global: 'globalThis'
  },
  publicDir: STATIC_DIR,
  resolve: {
    alias: {
      '@': path.resolve(pathSegments, './src'),
    },
    extensions: ['.js', '.ts', '.json', '.vue'],
  },
  css: {
    preprocessorOptions: {
      scss: { additionalData: `@import "./src/assets/scss/_mixins.scss";` },
    },
  },
  build: {
    assetsInlineLimit: 0,
  },
  test: {
  },
})