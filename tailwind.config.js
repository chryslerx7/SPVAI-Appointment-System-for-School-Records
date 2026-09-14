/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.php",
    "./admin/**/*.php",
    "./layouts/**/*.php",
    "./student_area.php",
    "./request_document.php",
    "./my_requests.php",
    "./appointments.php",
    "./notifications.php",
    "./register.php",
    "./login.php",
    "./index.php",
    "./assets/js/**/*.js",
  ],
  theme: {
    extend: {
      boxShadow: {
        'brutal': '4px 4px 0px 0px rgba(0,0,0,1)',
        'brutal-lg': '8px 8px 0px 0px rgba(0,0,0,1)',
        'brutal-sm': '2px 2px 0px 0px rgba(0,0,0,1)',
      },
      colors: {
        'brutal-yellow': '#FACC15',
        'brutal-bg': '#F9F9F9',
      }
    },
  },
  plugins: [],
}
