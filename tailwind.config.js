/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Filament/**/*.php',
        './app/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                vrazio: {
                    forest: '#0E261C',
                    'forest-deep': '#0A1C15',
                    gold: '#D4AF37',
                    'gold-light': '#F3E5AB',
                    'gold-dark': '#C5A028',
                    'gold-tint': '#E5C158',
                    ruby: '#9B1B30',
                    ivory: '#FAF8F5',
                    midnight: '#0B0E0D',
                },
            },
            fontFamily: {
                display: ['Playfair Display', 'Georgia', 'serif'],
                body: ['Inter', 'system-ui', 'sans-serif'],
            },
            backgroundImage: {
                'gold-gradient': 'linear-gradient(135deg, #D4AF37 0%, #F3E5AB 50%, #D4AF37 100%)',
                'forest-gradient': 'linear-gradient(180deg, #0E261C 0%, #0A1C15 100%)',
            },
            boxShadow: {
                'luxury': '0 4px 30px rgba(212, 175, 55, 0.15)',
                'luxury-lg': '0 10px 60px rgba(212, 175, 55, 0.2)',
            },
            animation: {
                'shimmer': 'shimmer 2.5s ease-in-out infinite',
                'fade-in': 'fadeIn 0.6s ease-out',
                'slide-up': 'slideUp 0.5s ease-out',
                'ticker': 'ticker 30s linear infinite',
            },
            keyframes: {
                shimmer: {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.7' },
                },
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(20px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                ticker: {
                    '0%': { transform: 'translateX(100%)' },
                    '100%': { transform: 'translateX(-100%)' },
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
