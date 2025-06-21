import {fileURLToPath, URL} from 'node:url'
import {defineConfig, loadEnv} from 'vite'
import vue from '@vitejs/plugin-vue'
import VueDevTools from 'vite-plugin-vue-devtools'


export default defineConfig(({mode}) => {
    const env = loadEnv(mode, process.cwd())
    console.log('Loaded VITE_API_URL:', env.VITE_API_URL)
    if (!env.VITE_API_URL) {
        throw new Error(`Missing VITE_API_URL in environment mode: ${mode}`)
    }
    
    return {
        plugins: [
            vue(),
            VueDevTools(),
        ],
        resolve: {
            alias: {
                '@': fileURLToPath(new URL('./src', import.meta.url)),
            },
        },
        server: {
            host: true,
            port: 8000,
            watch: {
                usePolling: true
            },
            proxy: {
                '/backend': {
                    target: env.VITE_API_URL,
                    changeOrigin: true,
                    rewrite: path => path.replace(/^\/backend/, ''),
                },
                cors: false
            }
        }
    }
})
