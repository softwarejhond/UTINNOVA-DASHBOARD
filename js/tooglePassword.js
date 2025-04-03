function togglePassword() {
  const passwordInput = document.getElementById("passwordInput");
  const toggleIcon = document.querySelector(".toggle-password");

  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    toggleIcon.textContent = "🙈"; // Cambiar a ícono de ojos cerrados
  } else {
    passwordInput.type = "password";
    toggleIcon.textContent = "👁️"; // Cambiar a ícono de ojos abiertos
  }
}
