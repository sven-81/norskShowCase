import { fileURLToPath, URL } from 'node:url'
import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd())

    if (!env.VITE_API_URL) {
        throw new Error(`Missing VITE_API_URL in environment mode: ${mode}`)
    }

    return {
        plugins: [vue()],
        resolve: {
            alias: {
                '@': fileURLToPath(new URL('./src', import.meta.url))
            }
        },
        server: {
            host: true,
            port: 8000,
            cors: false,
            watch: {
                usePolling: true
            },
            proxy: {
                '/backend': {
                    target: env.VITE_API_URL,
                    changeOrigin: true,
                    rewrite: (path) => path.replace(/^\/backend/, '')
                }
            }
        }
    }
})
