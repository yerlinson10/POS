// vite.config.optimized.ts - Configuración optimizada de Vite
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
  plugins: [
    vue({
      template: {
        compilerOptions: {
          // Optimización del compilador Vue
          hoistStatic: true,
          cacheHandlers: true,
          prefixIdentifiers: true
        }
      }
    })
  ],

  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/js'),
      '~': resolve(__dirname, 'resources')
    }
  },

  build: {
    // Optimización del bundle
    rollupOptions: {
      output: {
        manualChunks: {
          // Separar vendors principales
          'vue-vendor': ['vue', 'vue-router', 'pinia'],
          'ui-vendor': ['lucide-vue-next', 'vue-sonner'],
          'chart-vendor': ['chart.js', 'vue-chartjs'],
          'utils': ['lodash-es', 'axios'],

          // Separar páginas principales
          'pos-pages': [
            'resources/js/pages/POS/Index.vue',
            'resources/js/pages/POS/components/ProductSelectionModal.vue',
            'resources/js/pages/POS/components/CustomerSelector.vue'
          ],

          // Separar widgets
          'dashboard-widgets': [
            'resources/js/pages/components/widgets/ChartWidget.vue',
            'resources/js/pages/components/widgets/TableWidget.vue',
            'resources/js/pages/components/widgets/StatsWidget.vue'
          ]
        }
      }
    },

    // Configuración de minificación
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true,
        drop_debugger: true,
        pure_funcs: ['console.log', 'console.warn']
      }
    },

    // Configuración de sourcemaps
    sourcemap: process.env.NODE_ENV === 'development',

    // Configuración de assets
    assetsDir: 'assets',
    chunkSizeWarningLimit: 1000
  },

  optimizeDeps: {
    include: [
      'vue',
      'vue-router',
      'pinia',
      'axios',
      'lodash-es',
      'chart.js',
      'vue-chartjs'
    ],
    exclude: [
      // Excluir componentes que se cargan lazy
      'resources/js/pages/components/widgets/ChartWidget.vue'
    ]
  },

  server: {
    hmr: {
      overlay: false
    }
  },

  // Configuración de CSS
  css: {
    devSourcemap: true,
    preprocessorOptions: {
      scss: {
        additionalData: `@import "resources/css/variables.scss";`
      }
    }
  }
})
