import { defineConfig } from 'vite';
import { fileURLToPath } from 'url';    
import typescript from '@rollup/plugin-typescript';
import vue from '@vitejs/plugin-vue';
import path from 'path';

const filename = fileURLToPath(import.meta.url);
const pathSegments = path.dirname(filename);
const STATIC_DIR = 'public';

export default defineConfig({
  plugins: [
    typescript(),
    vue()
  ],
  define: {
    global: 'globalThis'
  },
  publicDir: STATIC_DIR,
  resolve: {
    alias: {
      '@': path.resolve(pathSegments, './src'),
    },
    extensions: ['.mjs', '.js', '.ts', '.json', '.vue'],
  },
  css: {
    preprocessorOptions: {
      scss: { additionalData: `@import "./src/assets/scss/_mixins.scss";` },
    },
  },
})