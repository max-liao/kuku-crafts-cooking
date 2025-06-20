import react from '@vitejs/plugin-react'
import { defineConfig } from 'vite'

export default defineConfig({
  plugins: [react()],
  build: {
    outDir: 'build',
    manifest: 'manifest.json', 
    rollupOptions: {
      input: '/src/main.tsx'
    }
  }
})
