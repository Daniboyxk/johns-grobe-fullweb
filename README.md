# John's Grobe — Web Oficial & Grobe Club

Proyecto web integral para **John's Grobe** (Marina Port Premià):
1. **Portal Web Oficial:** Landing interactiva con animaciones de scroll de alto rendimiento, historia y carta gastronómica.
2. **Grobe Club (Fidelización Digital):** Sistema completo para clientes y empleados con autenticación, generación de código QR personal, acumulación y canje de puntos por platos, y panel TPV para escanear y gestionar clientes.

---

## 📁 Estructura del Proyecto

`	ext
johns-grobe-fullweb/
│
├── index.html              # Portada oficial de John's Grobe
├── carta.html              # Carta gastronómica detallada
├── historia.html           # Historia y tradición artesanal
├── assets/                 # Estilos, scripts e imágenes de la web
├── scrollanimation/        # Secuencia de fotogramas WebP para scroll
│
├── club/                   # MÓDULO GROBE CLUB (PHP + MySQL)
│   ├── index.php           # Pantalla de acceso / login
│   ├── carta.php           # Catálogo de fidelización y QR del cliente
│   ├── login_proceso.php   # Control de sesión y roles
│   ├── logout.php          # Cierre de sesión
│   ├── admin_panel.php     # Panel TPV con escáner de cámara QR
│   ├── admin_canjear.php   # Interfaz de canje y suma de puntos
│   ├── admin_usuarios.php  # Gestión de clientes y saldos
│   ├── admin_historial.php # Historial de movimientos
│   └── conexion.php        # Conexión PDO a MySQL
│
├── database/
│   └── grobe_club.sql      # Script de base de datos MySQL completo
│
├── server.js               # Servidor local Node.js
└── package.json
`

---

## 🚀 Puesta en Marcha

### 1. Base de Datos (MySQL)
1. Abre **phpMyAdmin** (o tu gestor MySQL favorito).
2. Crea la base de datos grobe_club o importa directamente:
   `	ext
   database/grobe_club.sql
   `

### 2. Credenciales por Defecto
* **Administrador (TPV):**
  * Email: dmin@grobe.com
  * Contraseña: 1234
* **Cliente de Prueba:**
  * Email: jose@grobe.com
  * Contraseña: 1234 (Inicia con 150 puntos)

---

## 👨‍💻 Autor
Desarrollado para John's Grobe — Salsitxers & Graella des de 2006.
