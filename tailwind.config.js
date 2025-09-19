/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.jsx',
  ],
  theme: {
    extend: {
      screens: {
        'sm': '640px',
        'md': '768px',
        'lg': '1024px',
        'xl': '1280px',
        '2xl': '1536px',
      },
        colors: {
          // AmaKo Brand Colors
          'amk-brown-1': '#3d1f17',
          'amk-brown-2': '#855335',
          'amk-olive': '#2c311a',
          'amk-blush': '#d1ad97',
          'amk-amber': '#E38B2C',    // Legacy amber
          'amk-spice': '#E36414',    // New spicy orange - energetic, grilled/baked feeling
          'amk-sand': '#c6ae73',
          'amk-gold': '#eeaf00',
          'amk-nav-bottom': '#9a7a4a', // Brown-2 + Sand mix (80/20)
          
          // Clean Surface Colors
          'amk-cream': '#FFF8E6',    // Page background
          'amk-surface': '#FFFFFF',  // Cards/panels
          'amk-border': '#e9dfca',   // Soft sand line
          
          // Appetizing Color Add-ons
          'amk-green': '#4CAF50',    // Fresh accent - basil/herb green
          'amk-mint': '#9FD6A3',     // Pale mint for hover states
        // Legacy brand colors (for backward compatibility)
        brand: {
          DEFAULT: '#3d1f17', // Updated to amk-brown-1
          light: '#855335',   // Updated to amk-brown-2
        },
        highlight: '#eeaf00', // Updated to amk-gold
      },
      fontFamily: {
        // AmaKo Brand Fonts
        title: ['Tenor Sans', 'sans-serif'],
        subtitle: ['Playfair Display', 'serif'],
        subheading: ['Cormorant Garamond', 'serif'],
        section: ['Oswald', 'sans-serif'],
        body: ['Nunito', 'sans-serif'],
        caption: ['EB Garamond', 'serif'],
        quote: ['Prata', 'serif'],
        // Legacy fonts (for backward compatibility)
        sans: ['Nunito', 'sans-serif'],
        serif: ['Playfair Display', 'serif'],
        display: ['Tenor Sans', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
