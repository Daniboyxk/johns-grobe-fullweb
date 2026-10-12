
PROYECTO: GROBE CLUB - SISTEMA DE FIDELIZACIÓN DIGITAL
AUTOR: JUAN DANIEL ZAMBRANO VÉLEZ

1. REQUISITOS DEL ENTORNO
-------------------------
- Servidor Local: XAMPP / WAMP / Laragon.
- Versión de PHP: 7.4 o superior (recomendado 8.x).
- Puerto configurado en Apache: 8081.

2. INSTRUCCIONES DE INSTALACIÓN
-------------------------------
1. Copiar la carpeta 'grobe-club' en el directorio 'htdocs' de su servidor local.
2. Acceder a phpMyAdmin y crear una base de datos llamada: grobe_club
3. Importar el archivo 'grobe_club.sql' (incluido en este ZIP) para cargar tablas y datos.

3. ACCESO A LA APLICACIÓN
-------------------------
URL Local: http://localhost:8081/grobe-club/index.php

CREDENCIALES DE PRUEBA:

A) PERFIL ADMINISTRADOR (TPV):
   - Email: admin@grobe.com
   - Contraseña: 1234
   - Funciones: Escaneo de QR, gestión de puntos, edición de clientes.

B) PERFIL CLIENTE:
   - Email: jose@grobe.com
   - Contraseña: 1234
   - Funciones: Ver código QR personal, consultar puntos y catálogo.

4. NOTA SOBRE EL HARDWARE (CÁMARA/QR)
--------------------------------------
Para probar el escáner desde un dispositivo móvil real, se requiere una conexión HTTPS. 
El proyecto ha sido testeado satisfactoriamente utilizando 'Ngrok' para crear un túnel seguro hacia el puerto 8081.

============================================================