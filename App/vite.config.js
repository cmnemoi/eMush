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
      includeAssets: ["favicon.ico", "apple-touch-icon-180x180.png"],
      manifest: {
        "name": "eMush",
        "short_name": "eMush",
        "icons": [
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
        "start_url": "/",
        "display": "standalone",
        "background_color": "#FFFFFF",
        "theme_color": "#FFFFFF",
        "description": "eMush: explorations, exploits, and betrayals in space!  Dive into a thrilling space opera that combines survival, social deduction, and role-playing in a unique multiplayer experience, with games lasting a few days, from all your devices!",
        "orientation": "portrait",
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
  test: {
  },
})