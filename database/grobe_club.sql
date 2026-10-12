-- ============================================================
-- BASE DE DATOS: grobe_club
-- SISTEMA DE FIDELIZACIÓN DIGITAL & CARTA - JOHN'S GROBE
-- ============================================================

CREATE DATABASE IF NOT EXISTS grobe_club DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE grobe_club;

-- 1. TABLA: usuarios
CREATE TABLE IF NOT EXISTS usuarios (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  
ombre VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  ol ENUM('admin', 'cliente') NOT NULL DEFAULT 'cliente',
  puntos_acumulados INT NOT NULL DEFAULT 0,
  echa_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. TABLA: categorias
CREATE TABLE IF NOT EXISTS categorias (
  id_categoria INT AUTO_INCREMENT PRIMARY KEY,
  
ombre_categoria VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. TABLA: productos
CREATE TABLE IF NOT EXISTS productos (
  id_producto INT AUTO_INCREMENT PRIMARY KEY,
  id_categoria INT NULL,
  
ombre_producto VARCHAR(150) NOT NULL,
  descripcion TEXT NULL,
  precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  puntos_coste INT NOT NULL DEFAULT 0,
  imagen VARCHAR(255) NULL,
  disponible TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. TABLA: historial_puntos
CREATE TABLE IF NOT EXISTS historial_puntos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  puntos INT NOT NULL,
  id_premio INT NULL,
  echa TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DATOS INICIALES / SEED
-- ============================================================

-- Contraseña por defecto: 1234
INSERT INTO usuarios (
ombre, email, password, ol, puntos_acumulados) VALUES
('Admin Grobe', 'admin@grobe.com', '.0328.vNllp7Xb4u05gwl9ba6CagtP.Ui1nlu', 'admin', 0),
('Jose Pérez', 'jose@grobe.com', '.0328.vNllp7Xb4u05gwl9ba6CagtP.Ui1nlu', 'cliente', 150)
ON DUPLICATE KEY UPDATE 
ombre = VALUES(
ombre);

INSERT INTO categorias (
ombre_categoria) VALUES
('Hamburgueses'),
('Frankfurts & Bratwurst'),
('Tapes & Braves'),
('Begudes')
ON DUPLICATE KEY UPDATE 
ombre_categoria = VALUES(
ombre_categoria);

INSERT INTO productos (id_categoria, 
ombre_producto, descripcion, precio, puntos_coste, imagen, disponible) VALUES
(1, 'Grobe Burger Clàssica', 'Carn 100% vedella de Girona, formatge cheddar fos, ceba caramel·litzada i salsa Grobe.', 12.50, 120, 'assets/img/dishes/burger-gourmet.webp', 1),
(1, 'Cheff Premium Burger', 'Doble smash de Girona amb bacó cruixent i brioche artesanal.', 14.50, 140, 'assets/img/dishes/cheff-premium.webp', 1),
(2, 'Frankfurt Tradicional', 'Salsitxa artesanal al vapor i toc de graella amb pa cruixent.', 6.50, 60, 'assets/img/dishes/frankfurt-traditional.webp', 1),
(2, 'Currywurst Berlín', 'Frankfurt fumat amb salsa curry artesanal i patates fregides.', 8.90, 85, 'assets/img/dishes/currywurst-berlin.webp', 1),
(3, 'Patates Braves Grobe', 'Patates de graella amb el nostre alioli fumat i oli picant secret.', 5.90, 50, 'assets/img/dishes/patates-braves.webp', 1),
(3, 'Assortiment de Tapes', 'Pernil ibèric, croquetes casolanes i formatges catalans.', 13.90, 130, 'assets/img/dishes/tapas-assortiment.webp', 1)
ON DUPLICATE KEY UPDATE 
ombre_producto = VALUES(
ombre_producto);
