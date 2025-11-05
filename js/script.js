document.addEventListener("DOMContentLoaded", () => {
  const forms = document.querySelectorAll("form");
  forms.forEach(form => {
    form.addEventListener("submit", e => {
      const inputs = form.querySelectorAll("input[required]");
      let valido = true;

      inputs.forEach(input => {
        if (input.value.trim() === "") valido = false;
      });

      if (!valido) {
        e.preventDefault();
        alert("⚠️ Completa todos los campos");
      }
    });
  });
});
