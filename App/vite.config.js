import { defineConfig } from 'vite'
import { fileURLToPath } from 'url';
import path from 'path';
import vue from '@vitejs/plugin-vue'

const filename = fileURLToPath(import.meta.url);
const pathSegments = path.dirname(filename);

console.log(pathSegments);
console.log(import.meta.env.VITE_URL);

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      "@": path.resolve(pathSegments, "/src"),
    },
    extensions: ['.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue']
  },
})