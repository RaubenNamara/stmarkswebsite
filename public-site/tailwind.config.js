/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./templates/**/*.php'],
  theme: {
    extend: {
      colors: {
        'brand-navy': '#172554',
        'brand-navy-dark': '#0f1a3d',
        'brand-gold': '#facc15',
      },
      fontFamily: {
        sans: ['-apple-system', 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', 'sans-serif'],
      },
    },
  },
  plugins: [require('@tailwindcss/typography')],
};
