import typography from '@tailwindcss/typography'

/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        'brand-navy': '#172554',
        'brand-navy-dark': '#0f1a3d',
        'brand-gold': '#facc15',
      },
    },
  },
  plugins: [typography],
}
