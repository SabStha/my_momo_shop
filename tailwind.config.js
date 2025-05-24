/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['"Instrument Sans"', 'ui-sans-serif', 'system-ui'],
      },
      colors: {
        khaja: {
          light: '#FFF8F0',
          accent: '#F6C177',
          dark: '#7A3E1D',
          hover: '#A44B29',
        }
      }
    },
  },
  plugins: [],
}
