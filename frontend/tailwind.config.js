/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}'
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50:  '#eef2ff',
          100: '#e0e7ff',
          200: '#c7d2fe',
          300: '#a5b4fc',
          400: '#818cf8',
          500: '#6366f1',
          600: '#4f46e5',
          700: '#4338ca',
          800: '#3730a3',
          900: '#312e81'
        },
        surface: {
          900: '#111827',
          800: '#1f2937',
          700: '#374151',
          600: '#4b5563'
        },
        badge: {
          lost:        '#ef4444',
          lostLight:   '#fef2f2',
          found:       '#22c55e',
          foundLight:  '#f0fdf4',
          open:        '#3b82f6',
          openLight:   '#eff6ff',
          closed:      '#6b7280',
          closedLight: '#f9fafb'
        }
      },
      fontFamily: {
        sans: ['system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif']
      },
      animation: {
        'slide-in': 'slideIn 0.3s ease-out',
        'fade-in':  'fadeIn 0.2s ease-out'
      },
      keyframes: {
        slideIn: {
          '0%':   { transform: 'translateX(100%)', opacity: '0' },
          '100%': { transform: 'translateX(0)',    opacity: '1' }
        },
        fadeIn: {
          '0%':   { opacity: '0' },
          '100%': { opacity: '1' }
        }
      }
    }
  },
  plugins: []
}
