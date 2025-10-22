/** @type {import('tailwindcss').Config} */
export default {
  content: [ 
    "./**/**/*.blade.php",
    "./**/**/*.js",
    "./**/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        // Oxford book band colours
        "level-1": "#FF69B4",  // Pink
        "level-2": "#FF0000",  // Red
        "level-3": "#FFFF00",  // Yellow
        "level-4": "#67C5F4",  // Light Blue
        "level-5": "#00FA36",  // Green
        "level-6": "#FF892E",  // Orange
        "level-7": "#40e0d0",  // Turquoise
        "level-8": "#6A1B9A",  // Purple
        "level-9": "#D4AF37",  // Gold
        "level-10": "#FFFFFF", // White
        "level-11": "#bfff00", // Lime
        "level-12": "#AED581", // Lime+
        "level-13": "#9E9E9E", // Grey
        "level-14": "#9E9E9E", // Grey
        "level-15": "#0D47A1", // Dark Blue
        "level-16": "#0D47A1", // Dark Blue
        "level-17": "#B71C1C", // Dark Red  
        "level-18": "#B71C1C", // Dark Red
        "level-19": "#B71C1C", // Dark Red
        "level-20": "#B71C1C", // Dark Red
      },
      textColor: theme => ({
        "level-1": "#ffffff",
        "level-1p": "#ffffff",
        "level-2": "#ffffff",
        "level-3": "#000000",   // Black text
        "level-4": "#ffffff",
        "level-5": "#ffffff",
        "level-6": "#ffffff",
        "level-7": "#ffffff",
        "level-8": "#ffffff",
        "level-9": "#ffffff",
        "level-10": "#000000",  // Black text
        "level-11": "#000000",  // Black text
        "level-12": "#000000",  // Black text
        "level-13": "#ffffff",
        "level-14": "#ffffff",
        "level-15": "#ffffff",
        "level-16": "#ffffff",
        "level-17": "#ffffff",
        "level-18": "#ffffff",
        "level-19": "#ffffff",
        "level-20": "#ffffff",
      }),
    },
  },
  plugins: [],
};
