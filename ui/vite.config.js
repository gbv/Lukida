import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [vue(), tailwindcss()],
  server: {
    port: 9051,
    host: '0.0.0.0',
    allowedHosts: ['esx-32.gbv.de'],
    proxy: {
      '/api': {
        target: 'http://localhost:9050',
        changeOrigin: true,
        rewrite: path => path.replace(/^\/api/, '')
      }
    }
  },
  build: { outDir: 'dist' }
})
