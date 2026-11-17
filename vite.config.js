import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import os from 'node:os';

// この PC の Wi-Fi などのアドレス（例: 10.20.60.189）を探す
const findLanAddress = () => Object.values(os.networkInterfaces())
    .flat()
    .find((network) => network
        && network.family === 'IPv4'
        && ! network.internal
        && ! network.address.startsWith('169.254.'))
    ?.address;

// 同じ Wi-Fi にいる端末（localhost と LAN 内のアドレス）から開いたページだけ、開発サーバーの読み込みを許可する
const lanOrigin = /^https?:\/\/(localhost|127\.0\.0\.1|\[::1\]|10\.\d+\.\d+\.\d+|192\.168\.\d+\.\d+|172\.(1[6-9]|2\d|3[01])\.\d+\.\d+)(:\d+)?$/;

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    // .env に VITE_DEV_SERVER_LAN=true を書いたときだけ、スマホなど同じ Wi-Fi の端末からも
    // npm run dev の開発サーバーを使えるようにする（アドレスは自動で探す。VITE_DEV_SERVER_HOST で指定も可）
    const lanHost = env.VITE_DEV_SERVER_LAN === 'true'
        ? (env.VITE_DEV_SERVER_HOST || findLanAddress())
        : null;

    return {
        plugins: [
            tailwindcss(),
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/sass/app.scss',
                    'resources/js/app.js',
                ],
                refresh: true,
            }),
        ],
        server: lanHost
            ? {
                host: '0.0.0.0',
                port: 5173,
                strictPort: true,
                origin: `http://${lanHost}:5173`,
                hmr: { host: lanHost },
                cors: { origin: lanOrigin },
            }
            : undefined,
    };
});
