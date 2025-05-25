module.exports = {
    content: [
        // ... existing content ...
    ],
    theme: {
        extend: {
            // ... existing extensions ...
            keyframes: {
                'fade-in-down': {
                    '0%': {
                        opacity: '0',
                        transform: 'translateY(-1rem)'
                    },
                    '100%': {
                        opacity: '1',
                        transform: 'translateY(0)'
                    },
                }
            },
            animation: {
                'fade-in-down': 'fade-in-down 0.3s ease-out'
            }
        },
    },
    plugins: [
        // ... existing plugins ...
    ],
} 