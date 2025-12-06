/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  darkMode: 'class', // Enable class-based dark mode
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#083E40',
          dark: '#0f4c5c',
        },
        success: {
          DEFAULT: '#889717',
        },
        info: {
          DEFAULT: '#0a4f52',
        },
      },
    },
  },
  plugins: [],
}

