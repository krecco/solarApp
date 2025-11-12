import { fileURLToPath } from 'node:url'
import { mergeConfig, defineConfig, configDefaults } from 'vitest/config'
import viteConfig from './vite.config'

export default mergeConfig(
  viteConfig,
  defineConfig({
    test: {
      globals: true,
      environment: 'jsdom',
      exclude: [...configDefaults.exclude, 'e2e/*'],
      root: fileURLToPath(new URL('./', import.meta.url)),
      coverage: {
        provider: 'v8',
        reporter: ['text', 'json', 'html'],
        exclude: [
          'node_modules/',
          'dist/',
          '*.config.js',
          '*.config.ts',
          'src/**/*.spec.ts',
          'src/**/*.test.ts',
          'src/types/**',
        ],
        thresholds: {
          statements: 80,
          branches: 80,
          functions: 80,
          lines: 80
        }
      },
      setupFiles: ['./src/tests/setup.ts'],
      mockReset: true,
      restoreMocks: true
    }
  })
)