// Listar medidores y mostrarlos en una tabla
function listarMedidores() {
    fetch('/Proyectos/Emar/src/controllers/MedidorController.php')
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('tabla-medidores-body');
            tbody.innerHTML = '';
            data.forEach(medidor => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${medidor.id}</td>
                    <td>${medidor.numero_serie}</td>
                    <td>${medidor.marca}</td>
                    <td>${medidor.modelo}</td>
                    <td>${medidor.ubicacion}</td>
                    <td>${medidor.estado}</td>
                    <td>${medidor.foto ? `<img src="${medidor.foto}" width="50">` : ''}</td>
                    <td>
                        <button onclick="cargarMedidor(${medidor.id})">Editar</button>
                        <button onclick="eliminarMedidor(${medidor.id})">Eliminar</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        });
}

function guardarMedidor(e) {
    e.preventDefault();
    const id = document.getElementById('id').value;
    const medidor = {
        id: id,
        numero_serie: document.getElementById('numero_serie').value,
        marca: document.getElementById('marca').value,
        modelo: document.getElementById('modelo').value,
        ubicacion: document.getElementById('ubicacion').value,
        estado: document.getElementById('estado').value,
        foto: document.getElementById('foto_url').value
    };

    const metodo = id ? 'PUT' : 'POST';

    fetch('/Proyectos/Emar/src/controllers/MedidorController.php', {
        method: metodo,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(medidor)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(id ? 'Medidor actualizado' : 'Medidor creado');
            document.getElementById('formMedidor').reset();
            listarMedidores();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function subirImagen(input) {
    const file = input.files[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('foto', file);

    fetch('/Proyectos/Emar/src/controllers/MedidorController.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.ruta) {
            document.getElementById('foto_url').value = data.ruta;
            alert('Imagen subida correctamente');
        } else {
            alert('Error al subir la imagen: ' + (data.error || ''));
        }
    });
}

function cargarMedidor(id) {
    fetch('/Proyectos/Emar/src/controllers/MedidorController.php')
        .then(res => res.json())
        .then(data => {
            const medidor = data.find(m => m.id == id);
            if (medidor) {
                document.getElementById('id').value = medidor.id;
                document.getElementById('numero_serie').value = medidor.numero_serie;
                document.getElementById('marca').value = medidor.marca;
                document.getElementById('modelo').value = medidor.modelo;
                document.getElementById('ubicacion').value = medidor.ubicacion;
                document.getElementById('estado').value = medidor.estado;
                document.getElementById('foto_url').value = medidor.foto;
            }
        });
}

function eliminarMedidor(id) {
    if (!confirm('¿Seguro que deseas eliminar este medidor?')) return;
    fetch('/Proyectos/Emar/src/controllers/MedidorController.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Medidor eliminado');
            listarMedidores();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

window.onload = function() {
    listarMedidores();
    document.getElementById('formMedidor').addEventListener('submit', guardarMedidor);
    document.getElementById('foto').addEventListener('change', function() {
        subirImagen(this);
    });
};