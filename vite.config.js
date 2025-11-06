import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    server: {
        host: "localhost", // Dùng domain giống Laravel
        port: 5174,
        strictPort: true,
        https: false,
        hmr: {
            host: "localhost",
            protocol: "ws", // hoặc 'wss' nếu bạn dùng HTTPS
        },
        cors: true, // <-- Bắt buộc thêm dòng này
    },

    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
});
