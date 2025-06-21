import { mergeConfig, defineConfig } from 'vitest/config'
import viteConfigFactory from './vite.config.ts'

const viteConfig = await viteConfigFactory({
  mode: 'development',
  command: 'serve',
})

export default mergeConfig(viteConfig, defineConfig({
  test: {
    environment: 'jsdom',
    exclude: ['node_modules', 'dist', 'e2e/**'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'html'],
      reportsDirectory: './coverage',
      include: ['src/**/*.{ts,vue}'],
      exclude: ['src/main.ts', 'src/components/index.ts', 'src/views/index.ts'],
    },
  },
}))
