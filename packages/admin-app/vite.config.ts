import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
    plugins: [react()],
    base: '/admin/',
    server: {
        proxy: {
            '/api': {
                target: 'http://localhost:9090',
                changeOrigin: true,
                secure: false,
            },
        },
    },
    build: {
        outDir: '../../public/admin',
        emptyOutDir: true,
        assetsDir: 'assets',
    },
})
