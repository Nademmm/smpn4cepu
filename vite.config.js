import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            // Halaman publik tidak memakai bundel JS sendiri (Livewire membawa Alpine),
            // jadi hanya CSS yang di-build agar tidak ada request JS kosong.
            input: ['resources/css/app.css'],
            refresh: true,
        }),
    ],
});
