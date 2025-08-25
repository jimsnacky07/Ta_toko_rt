//** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        tan: "#D2B48C",
      },
    },
  },
  // opsional: pastikan tidak kepurge
  safelist: ["text-tan"],
  plugins: [],
};

