/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.jsx',
        './resources/**/*.vue',
        './resources/**/*.ts',
        './resources/**/*.tsx',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [],
    safelist: [
        'px-4',
        'py-2',
        'bg-blue-500',
        'text-white',
        'rounded',
        'hover:bg-blue-600',
        'transition-colors',
        'bg-white',
        'rounded-lg',
        'shadow-md',
        'overflow-hidden',
    ],
}; 