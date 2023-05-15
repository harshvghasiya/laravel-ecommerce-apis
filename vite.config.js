import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'   //  Imported 'vue'

export default defineConfig({
    plugins: [
    	vue(),
        laravel({
            input: [
            'resources/css/app.css',

            	/**
                 * =======================
                 *      Assets Files
                 * =======================
                 */

             'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
