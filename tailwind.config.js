/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./views/**/*.php', './views/**/*.html'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
      colors: {
        forest: {
          950: '#052e16',
          900: '#14532d',
          800: '#166534',
          700: '#15803d',
          600: '#16a34a',
          500: '#22c55e',
          200: '#bbf7d0',
          50:  '#f0fdf4',
        },
      },
    },
  },
  plugins: [],
};
