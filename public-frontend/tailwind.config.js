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
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        display: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      boxShadow: {
        card: '0 1px 2px 0 rgb(0 0 0 / 0.04), 0 8px 24px -8px rgb(23 37 84 / 0.10)',
        'card-hover': '0 4px 12px 0 rgb(0 0 0 / 0.06), 0 16px 32px -8px rgb(23 37 84 / 0.16)',
      },
    },
  },
  plugins: [typography],
}
