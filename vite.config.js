import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

const dockerVite = process.env.DOCKER_VITE === '1';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    // Docker Compose `vite` profile: set DOCKER_VITE=1 so dev server binds 0.0.0.0 and polls volumes
    server: dockerVite
        ? {
              host: '0.0.0.0',
              port: 5173,
              strictPort: true,
              watch: { usePolling: true },
          }
        : undefined,
});
