import { fileURLToPath, URL } from 'node:url';
import { reactRouter } from '@react-router/dev/vite';
import { defineConfig } from 'vite';

export default defineConfig({
  plugins: [reactRouter()],
  resolve: {
    alias: {
      '~': fileURLToPath(new URL('./app', import.meta.url)),
    },
  },
  server: {
    port: 5173,
  },
  css: {
    modules: {
      localsConvention: 'camelCaseOnly',
    },
  },
  ssr: {
    // Les paquets du monorepo sont publiés en sources (TypeScript, CSS) : Vite les compile.
    noExternal: ['@wariss/ui', '@wariss/i18n', '@wariss/api-client'],
  },
});
