import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// Vite : bundling, tree-shaking, minification, code-splitting et Fast Refresh
// sont fournis nativement. Le plugin React active le Hot Reload / Fast Refresh.
export default defineConfig({
  plugins: [react()],
  build: {
    target: 'es2019',
    cssCodeSplit: true,
    rollupOptions: {
      output: {
        manualChunks: {
          // Découpage du bundle : le moteur d'animation dans son propre chunk
          motion: ['framer-motion'],
          router: ['react-router-dom'],
        },
      },
    },
  },
})
