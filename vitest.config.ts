import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vitest/config';

// Separado do vite.config.js para os testes não carregarem os plugins do Laravel e das fontes.
export default defineConfig({
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    test: {
        include: ['tests/js/**/*.spec.ts'],
        environment: 'node',
    },
});
