/// <reference types="vitest" />
import typescript from '@rollup/plugin-typescript';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import { fileURLToPath } from 'url';
import { defineConfig } from 'vite';
import { VitePWA } from "vite-plugin-pwa";

const filename = fileURLToPath(import.meta.url);
const pathSegments = path.dirname(filename);
const STATIC_DIR = 'public';

export default defineConfig({
  plugins: [
    typescript(),
    vue(),
    VitePWA({
      registerType: "autoUpdate",
      includeAssets: ["favicon.ico", "apple-touch-icon-180x180.png"],
      manifest: {
        "id": "/",
        "name": "eMush",
        "short_name": "eMush",
        "description": "eMush: explorations, exploits, and betrayals in space! Dive into a thrilling space opera that combines survival, social deduction, and role-playing in a unique multiplayer experience, with games lasting a few days, from all your devices!",
        "start_url": "/",
        "display": "standalone",
        "display_override": ["standalone", "window-control-overlay"],
        "background_color": "#0f0f43",
        "theme_color": "#111317",
        "orientation": "portrait",
        "dir": "ltr",
        "categories": ["games", "entertainment", "social"],
        "shortcuts": [
          {
            "name": "Daedalus",
            "short_name": "Daedalus",
            "url": "/game",
            "icons": [
              {
                "src": "pwa-192x192.png",
                "sizes": "192x192",
                "type": "image/png"
              },
            ]
          },
          {
            "name": "My account",
            "short_name": "My account",
            "url": "/me",
            "icons": [
              {
                "src": "pwa-192x192.png",
                "sizes": "192x192",
                "type": "image/png"
              },
            ]
          },
          {
            "name": "News",
            "short_name": "News",
            "url": "/news",
            "icons": [
              {
                "src": "pwa-192x192.png",
                "sizes": "192x192",
                "type": "image/png"
              },
            ]
          },
        ],
        "icons": [
          {
            "src": "pwa-64x64.png",
            "sizes": "64x64",
            "type": "image/png"
          },
          {
            "src": "pwa-192x192.png",
            "sizes": "192x192",
            "type": "image/png",
            "purpose": "any"
          },
          {
            "src": "pwa-512x512.png",
            "sizes": "512x512",
            "type": "image/png"
          },
          {
            "src": "pwa-maskable-192x192.png",
            "sizes": "192x192",
            "type": "image/png",
            "purpose": "maskable"
          },
          {
            "src": "pwa-maskable-512x512.png",
            "sizes": "512x512",
            "type": "image/png",
            "purpose": "maskable",
          }
        ],
        "screenshots": [
          {
            "src": "screenshots/screenshot-1.png",
            "sizes": "794x871",
            "type": "image/png"
          },
          {
            "src": "screenshots/screenshot-2.png",
            "sizes": "794x871",
            "type": "image/png"
          },
          {
            "src": "screenshots/screenshot-3.png",
            "sizes": "794x871",
            "type": "image/png"
          },
          {
            "src": "screenshots/screenshot-4.png",
            "sizes": "794x874",
            "type": "image/png",
            "form_factor": "wide"
          }
        ],
      },
      strategies: 'injectManifest',
      srcDir: 'src',
      filename: 'sw.js',
      injectManifest: {
        injectionPoint: undefined,
        maximumFileSizeToCacheInBytes: 3_000_000
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
      scss: { 
        additionalData: `@use "@/assets/scss/mixins" as *;`,
        api: 'modern',
      },
    },
  },
  build: {
    assetsInlineLimit: 0,
  },
  test: {},
  assetsInclude: ['**/*.swf'],
})