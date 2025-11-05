let zonasSeleccionadas = [];

function toggleZona(button, zona) {
  // Si presiona "Todo el cuerpo"
  if (zona === 'Todo el cuerpo') {
    if (zonasSeleccionadas.includes('Todo el cuerpo')) {
      zonasSeleccionadas = []; // desmarca todo
      document.querySelectorAll('.btn-zona').forEach(btn => btn.classList.remove('selected'));
    } else {
      zonasSeleccionadas = ['Todo el cuerpo', 'Brazos', 'Pecho', 'Abdominales', 'Piernas'];
      document.querySelectorAll('.btn-zona').forEach(btn => btn.classList.add('selected'));
    }
  } else {
    // Elimina "Todo el cuerpo" si selecciona algo específico
    const index = zonasSeleccionadas.indexOf(zona);
    if (index === -1) {
      zonasSeleccionadas.push(zona);
      button.classList.add('selected');
    } else {
      zonasSeleccionadas.splice(index, 1);
      button.classList.remove('selected');
    }

    // Si deselecciona algo, también desmarca "Todo el cuerpo"
    const allBtn = document.querySelector('.btn-zona:first-child');
    allBtn.classList.remove('selected');
    zonasSeleccionadas = zonasSeleccionadas.filter(z => z !== 'Todo el cuerpo');
  }

  // Actualizar input oculto correctamente
  document.getElementById('zonasInput').value = zonasSeleccionadas.length > 0 ? zonasSeleccionadas.join(',') : '';
}

// Validación para que no deje continuar sin seleccionar nada
function validarZonas() {
    const input = document.getElementById('zonasInput');
    if (!input.value || input.value.trim() === '') {
        alert('⚠️ Por favor, selecciona al menos una zona antes de continuar.');
        return false; // bloquea el envío
    }
    return true; // permite el envío
}
