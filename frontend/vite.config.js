import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// Deploy under http://localhost/mero-kam/app/ — API at /mero-kam/backend/api/
export default defineConfig({
  base: '/mero-kam/app/',
  plugins: [react()],
  server: {
    port: 5173,
    proxy: {
      '/mero-kam/backend': {
        target: 'http://127.0.0.1',
        changeOrigin: true,
      },
    },
  },
})
