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
        'brand-cream': '#FFFDD0',
      },
      fontFamily: {
        // One modern family for everything (variable: weights 200-800 from a single small file).
        sans: ['"Plus Jakarta Sans Variable"', '"Plus Jakarta Sans"', 'system-ui', '-apple-system', '"Segoe UI"', 'Roboto', '"Helvetica Neue"', 'Arial', 'sans-serif'],
        display: ['"Plus Jakarta Sans Variable"', '"Plus Jakarta Sans"', 'system-ui', '-apple-system', '"Segoe UI"', 'Roboto', 'Arial', 'sans-serif'],
      },
      // Text scale ~6% larger than Tailwind's defaults (about +1px at body sizes) so everything on the
      // site reads more comfortably. Only text grows - spacing and layout are unchanged. 6xl and up are left as is.
      fontSize: {
        xs: ['0.8125rem', '1.125rem'],
        sm: ['0.9375rem', '1.375rem'],
        base: ['1.0625rem', '1.65rem'],
        lg: ['1.1875rem', '1.8125rem'],
        xl: ['1.3125rem', '1.875rem'],
        '2xl': ['1.5625rem', '2.125rem'],
        '3xl': ['1.9375rem', '2.375rem'],
        '4xl': ['2.3125rem', '2.625rem'],
        '5xl': ['3.0625rem', '1'],
      },
      boxShadow: {
        card: '0 1px 2px 0 rgb(0 0 0 / 0.04), 0 8px 24px -8px rgb(23 37 84 / 0.10)',
        'card-hover': '0 4px 12px 0 rgb(0 0 0 / 0.06), 0 16px 32px -8px rgb(23 37 84 / 0.16)',
      },
    },
  },
  plugins: [typography],
}
