/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{js,jsx,ts,tsx}",
    "./public/index.html",
  ],
  theme: {
    extend: {
      colors: {
        'dark-green': '#3E5F44',
        'medium-green': '#5E936C',
        'light-green': '#93DA97',
        'pale-green': '#E8FFD7',
      },
      fontFamily: {
        'jakarta': ['"Plus Jakarta Sans"', 'sans-serif'],
      },
      boxShadow: {
        'card': '0 4px 6px rgba(62, 95, 68, 0.1)',
        'card-hover': '0 8px 15px rgba(62, 95, 68, 0.15)',
        'input-focus': '0 0 0 2px rgba(94, 147, 108, 0.2)',
      },
      transitionProperty: {
        'height': 'height',
        'transform': 'transform',
      },
      spacing: {
        '4': '16px',
        '8': '32px',
      },
      borderRadius: {
        'card': '8px',
        'button': '5px',
      },
    },
  },
  plugins: [],
  variants: {
    extend: {
      boxShadow: ['hover', 'focus'],
      transform: ['hover'],
      backgroundColor: ['hover', 'focus'],
      textColor: ['hover', 'focus'],
    },
  },
}