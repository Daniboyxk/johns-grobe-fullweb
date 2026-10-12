/**
 * Lógica del Front-End para Grobe Club (Carta)
 */

// --- 1. LÓGICA DEL CARRITO INTERACTIVO ---
const barra = document.getElementById('barra-carrito');
let carrito = []; 

if (barra) {
    // Añadir producto desde la carta
    window.agregarAlCarrito = function(nombre, precio) {
        let itemExistente = carrito.find(item => item.nombre === nombre);
        if (itemExistente) {
            itemExistente.cantidad++; 
        } else {
            carrito.push({ nombre: nombre, precio: precio, cantidad: 1 }); 
        }
        actualizarCarrito();
    };

    // Sumar 1 cantidad dentro del carrito
    window.sumarAlCarrito = function(index) {
        carrito[index].cantidad++;
        actualizarCarrito();
    };

    // Restar 1 cantidad dentro del carrito
    window.restarDelCarrito = function(index) {
        if (carrito[index].cantidad > 1) {
            carrito[index].cantidad--;
        } else {
            carrito.splice(index, 1); // Borrar si llega a 0
        }
        actualizarCarrito();
    };

    // Renderizar la lista del carrito en pantalla
    function actualizarCarrito() {
        const displayTotal = document.getElementById('total-precio'); 
        const inputTotal = document.getElementById('input-total');
        const lista = document.getElementById('lista-carrito');
        
        lista.innerHTML = ''; 
        let total = 0;
        
        carrito.forEach((item, index) => {
            let precioFila = item.precio * item.cantidad;
            total += precioFila;
            
            lista.innerHTML += `
                <li class="flex justify-between items-center bg-gray-100 p-2 md:p-3 rounded-xl border border-gray-200">
                    <span class="font-bold truncate pr-2 text-gray-800 flex-1">${item.nombre}</span>
                    
                    <div class="flex items-center gap-4 shrink-0">
                        <span class="font-black text-[#D21F3C] w-16 text-right">${precioFila.toFixed(2)}€</span>
                        
                        <div class="flex items-center bg-white rounded-lg border border-gray-300 overflow-hidden shadow-sm">
                            <button type="button" onclick="restarDelCarrito(${index})" class="px-3 py-1 text-gray-600 hover:bg-gray-200 hover:text-red-500 transition-colors font-black text-lg leading-none" title="Quitar uno">-</button>
                            <span class="px-2 font-black text-sm w-6 text-center select-none">${item.cantidad}</span>
                            <button type="button" onclick="sumarAlCarrito(${index})" class="px-3 py-1 text-gray-600 hover:bg-gray-200 hover:text-green-600 transition-colors font-black text-lg leading-none" title="Añadir otro">+</button>
                        </div>
                    </div>
                </li>
            `;
        });
        
        displayTotal.innerText = total.toFixed(2) + '€'; 
        inputTotal.value = total.toFixed(2);
        
        // Recargar iconos si es necesario
        if (typeof lucide !== 'undefined') lucide.createIcons(); 
        
        // Mostrar u ocultar la barra inferior
        if (total > 0) {
            barra.classList.remove('translate-y-full');
        } else {
            barra.classList.add('translate-y-full');
        }
    }
}


// --- 2. LÓGICA DEL PANEL DE ADMINISTRADOR ---
function prepararEdicion(id, nombre, descripcion, precio, idCat, puntosCoste) {
    document.getElementById('panel-formulario').classList.replace('border-[#D21F3C]/50', 'border-blue-500');
    document.getElementById('texto-formulario').innerText = "Editando Plato...";
    document.getElementById('icono-formulario').setAttribute('data-lucide', 'pencil');
    document.getElementById('icono-formulario').classList.replace('text-[#D21F3C]', 'text-blue-500');
    document.getElementById('btn-submit').className = "btn-secundario";
    document.getElementById('btn-submit').innerText = "Actualizar Plato";
    document.getElementById('btn-cancelar-edicion').classList.remove('hidden');
    
    document.getElementById('input-accion').name = "editar_producto";
    document.getElementById('input-id-editar').value = id;
    document.getElementById('input-nombre').value = nombre;
    document.getElementById('input-descripcion').value = descripcion;
    document.getElementById('input-precio').value = precio;
    document.getElementById('input-categoria').value = idCat;
    document.getElementById('input-puntos-coste').value = puntosCoste;
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function cancelarEdicion() {
    document.getElementById('panel-formulario').classList.replace('border-blue-500', 'border-[#D21F3C]/50');
    document.getElementById('texto-formulario').innerText = "Añadir Nuevo Plato";
    document.getElementById('icono-formulario').setAttribute('data-lucide', 'plus-circle');
    document.getElementById('icono-formulario').classList.replace('text-blue-500', 'text-[#D21F3C]');
    document.getElementById('btn-submit').className = "btn-primario";
    document.getElementById('btn-submit').innerText = "Guardar Plato";
    document.getElementById('btn-cancelar-edicion').classList.add('hidden');
    
    document.getElementById('input-accion').name = "agregar_producto";
    document.getElementById('input-id-editar').value = "";
    document.getElementById('form-producto').reset();
    if (typeof lucide !== 'undefined') lucide.createIcons();
}


// --- 3. MODALES ---
function abrirModalQR() {
    const modal = document.getElementById('modal-qr');
    const content = document.getElementById('modal-qr-content');
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }, 10);
}

function cerrarModalQR() {
    const modal = document.getElementById('modal-qr');
    const content = document.getElementById('modal-qr-content');
    modal.classList.add('opacity-0');
    content.classList.remove('scale-100');
    content.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }, 300);
}

// Inicializar iconos al cargar la página
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}