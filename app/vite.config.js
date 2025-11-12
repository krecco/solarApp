import { fileURLToPath, URL } from 'node:url'
import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import compression from 'vite-plugin-compression'

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  
  return {
    plugins: [
      vue(),
      vueJsx(),
      compression({
        algorithm: 'gzip',
        ext: '.gz',
        threshold: 10240, // Only compress files larger than 10kb
      })
    ],
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url)),
        '@components': fileURLToPath(new URL('./src/components', import.meta.url)),
        '@views': fileURLToPath(new URL('./src/views', import.meta.url)),
        '@stores': fileURLToPath(new URL('./src/stores', import.meta.url)),
        '@composables': fileURLToPath(new URL('./src/composables', import.meta.url)),
        '@utils': fileURLToPath(new URL('./src/utils', import.meta.url)),
        '@api': fileURLToPath(new URL('./src/api', import.meta.url)),
        '@layouts': fileURLToPath(new URL('./src/layouts', import.meta.url)),
        '@assets': fileURLToPath(new URL('./src/assets', import.meta.url)),
        '@styles': fileURLToPath(new URL('./src/styles', import.meta.url)),
        '@plugins': fileURLToPath(new URL('./src/plugins', import.meta.url)),
        '@router': fileURLToPath(new URL('./src/router', import.meta.url)),
        '@middleware': fileURLToPath(new URL('./src/middleware', import.meta.url)),
        '@types': fileURLToPath(new URL('./src/types', import.meta.url)),
        '@widgets': fileURLToPath(new URL('./src/components/widgets', import.meta.url))
      }
    },
    css: {
      preprocessorOptions: {
        scss: {
          // Use the modern Sass API
          api: 'modern',
          // Automatically inject the abstracts into every component
          additionalData: `@use "@styles/abstracts" as *;`,
          // Suppress deprecation warnings during migration
          silenceDeprecations: ['import'],
        }
      }
    },
    server: {
      port: 3000,
      host: true,
      open: true,
      cors: true,
      proxy: {
        '/api': {
          target: env.VITE_API_BASE_URL || 'http://localhost:8080',
          changeOrigin: true,
          secure: false,
          rewrite: (path) => path.replace(/^\/api/, '')
        }
      }
    },
    build: {
      target: 'esnext',
      minify: 'terser',
      terserOptions: {
        compress: {
          drop_console: true,
          drop_debugger: true
        }
      },
      rollupOptions: {
        output: {
          manualChunks: {
            'vue-vendor': ['vue', 'vue-router', 'pinia'],
            'primevue-vendor': ['primevue'],
            'chart-vendor': ['chart.js', 'apexcharts', 'vue3-apexcharts'],
            'utils-vendor': ['axios', 'lodash-es', 'dayjs'],
            'ui-vendor': ['@formkit/auto-animate']
          }
        }
      },
      chunkSizeWarningLimit: 1000,
      sourcemap: mode === 'development',
      reportCompressedSize: false
    },
    optimizeDeps: {
      include: [
        'vue',
        'vue-router',
        'pinia',
        'axios',
        'primevue/config',
        'primevue/button',
        'primevue/datatable',
        'primevue/dialog',
        'primevue/toast',
        'chart.js',
        '@vueuse/core'
      ]
    },
    define: {
      __APP_VERSION__: JSON.stringify(process.env.npm_package_version),
      __BUILD_TIME__: JSON.stringify(new Date().toISOString())
    }
  }
})
