import { defineConfig } from 'vite'
import { fileURLToPath } from 'url';
import typescript from '@rollup/plugin-typescript';
import path from 'path';
import vue from '@vitejs/plugin-vue'

const filename = fileURLToPath(import.meta.url);
const pathSegments = path.dirname(filename);

const STATIC_DIR = 'public';

console.log(pathSegments);

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    typescript(),
    vue()
  ],
  publicDir: STATIC_DIR,
  resolve: {
    alias: {
      "@": path.resolve(pathSegments, "/src"),
    },
    extensions: ['.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue']
  },
  css: {
    preprocessorOptions: {
      scss: { additionalData: `@import "./src/assets/scss/_mixins.scss";` },
    },
  },
})