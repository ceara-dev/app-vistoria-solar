/**
 * Paleta extraída do projeto antigo (Quasar).
 * A cor da marca é o teal #00796b (Quasar "teal-8"), usado em botões, links e destaques.
 *
 * Tailwind v4: este arquivo é carregado via `@config` em resources/css/app.css.
 * O tema do daisyUI (primary, secondary...) fica em app.css, com os mesmos valores.
 *
 * @type {import('tailwindcss').Config}
 */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                // Escala teal do Quasar (color="teal", "teal-10", "teal-8"...)
                brand: {
                    50: '#e0f2f1',
                    100: '#b2dfdb',
                    200: '#80cbc4',
                    300: '#4db6ac',
                    400: '#26a69a',
                    500: '#009688', // teal
                    600: '#00897b',
                    700: '#00796b', // cor principal da marca
                    800: '#00695c',
                    900: '#004d40', // teal-10 (notificações, hover)
                    950: '#004940',
                    DEFAULT: '#00796b',
                },
                // Verde do painel lateral do login (.color-fond)
                leaf: {
                    DEFAULT: '#55a38b',
                    light: '#80cbc4',
                },
                // Cinzas do layout antigo
                ink: {
                    DEFAULT: '#333333', // header / sidebar
                    soft: '#4d4d4c', // texto de eventos
                    muted: '#868e96',
                    subtle: '#999999',
                },
                surface: {
                    DEFAULT: '#f7f7f7', // fundo do formulário de login
                    alt: '#f0f3f3',
                    line: '#d9d9d9',
                },
                danger: {
                    DEFAULT: '#c80000',
                    alt: '#cb1111',
                },
                // Cores dos gráficos (apexcharts no quasar.config.js)
                chart: {
                    blue: '#008FFB',
                    green: '#00E396',
                    yellow: '#FEB019',
                    red: '#FF4560',
                    purple: '#775DD0',
                    teal: '#00796B',
                },
            },
            fontFamily: {
                sans: ['Montserrat', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                mono: ['"Oxygen Mono"', 'ui-monospace', 'monospace'],
            },
            backgroundImage: {
                'brand-gradient': 'linear-gradient(-45deg, #55a38b, #00796b, #80cbc4)',
            },
            keyframes: {
                gradient: {
                    '0%, 100%': { backgroundPosition: '0% 50%' },
                    '50%': { backgroundPosition: '100% 50%' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-10px)' },
                },
            },
            animation: {
                gradient: 'gradient 15s ease infinite',
                float: 'float 4s ease-in-out infinite',
            },
        },
    },
};
