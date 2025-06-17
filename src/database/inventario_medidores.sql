-- Elimina la base de datos si existe y créala de nuevo
DROP DATABASE IF EXISTS emar_db;
CREATE DATABASE emar_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE emar_db;

-- Tabla de roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT
) ENGINE=InnoDB;

INSERT INTO roles (id, nombre, descripcion) VALUES
(1, 'Administrador', 'Acceso completo al sistema'),
(2, 'Usuario', 'Acceso limitado para gestionar medidores y consumo'),
(3, 'Operario', 'Usuario con permisos de operario');

-- Tabla de usuarios (con campo foto)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    foto VARCHAR(255), -- Foto de perfil del usuario (opcional)
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla de medidores (con campo foto)
CREATE TABLE medidores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_serie VARCHAR(50) NOT NULL UNIQUE,
    tipo ENUM('agua') DEFAULT 'agua',
    ubicacion VARCHAR(255),
    estado ENUM('activo','inactivo','mantenimiento') DEFAULT 'activo',
    foto VARCHAR(255), -- Foto del medidor (opcional)
    usuario_id INT NOT NULL,
    fecha_instalacion DATE,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla de lecturas
CREATE TABLE lecturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medidor_id INT NOT NULL,
    fecha DATE NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    usuario_id INT NOT NULL, -- quién registró la lectura
    observaciones VARCHAR(255),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medidor_id) REFERENCES medidores(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_medidor_fecha (medidor_id, fecha)
) ENGINE=InnoDB;

-- Tabla de historial de lecturas
CREATE TABLE historial_lectura (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lectura_id INT NOT NULL,
    usuario_id INT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    accion VARCHAR(50),
    descripcion TEXT,
    FOREIGN KEY (lectura_id) REFERENCES lecturas(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla de consumos (opcional, para guardar consumos calculados)
CREATE TABLE consumos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medidor_id INT NOT NULL,
    lectura DECIMAL(10,2) NOT NULL,
    fecha_lectura DATE NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medidor_id) REFERENCES medidores(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla de logs de acciones generales
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(255) NOT NULL,
    fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla de notificaciones
CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    mensaje TEXT NOT NULL,
    leido TINYINT(1) DEFAULT 0,
    fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla de órdenes de servicio
CREATE TABLE ordenes_servicio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(30) NOT NULL DEFAULT 'Pendiente',
    observaciones TEXT,
    operario_id INT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (operario_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla de historial de órdenes de servicio
CREATE TABLE historial_orden (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_id INT,
    usuario_id INT,
    accion VARCHAR(50),
    descripcion TEXT,
    fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (orden_id) REFERENCES ordenes_servicio(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabla de historial de medidores
CREATE TABLE historial_medidor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medidor_id INT,
    usuario_id INT,
    accion VARCHAR(50),
    descripcion TEXT,
    fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medidor_id) REFERENCES medidores(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Índices adicionales para optimizar búsquedas frecuentes
CREATE INDEX idx_lecturas_medidor_fecha ON lecturas(medidor_id, fecha);
CREATE INDEX idx_historial_lectura_lectura ON historial_lectura(lectura_id);
CREATE INDEX idx_historial_orden_orden ON historial_orden(orden_id);
CREATE INDEX idx_historial_medidor_medidor ON historial_medidor(medidor_id);

-- Ejemplo de usuarios iniciales
INSERT INTO usuarios (nombre, email, password, rol_id, foto) VALUES
('Admin', 'admin@correo.com', '$2y$10$an7GUwgNnPdx1zo3zM0DtuvGxJQ397mzjqR/eAU0O03iUbif1PR9a', 1, NULL),
('Operario', 'operario@correo.com', '$2y$10$hashoperario', 3, NULL),
('Usuario', 'usuario@correo.com', '$2y$10$hashusuario', 2, NULL),
('Luna Ortiz', 'luna@correo.com', '$2y$10$hashusuario2', 2, NULL);

-- Ejemplo de medidor con foto
INSERT INTO medidores (numero_serie, usuario_id, fecha_instalacion, foto) VALUES
('MED10001', 3, '2025-06-11', 'medidor1.jpg');

-- Ejemplo de lectura inicial
INSERT INTO lecturas (medidor_id, fecha, valor, usuario_id, observaciones) VALUES
(1, '2024-05-01', 120.50, 3, 'Lectura inicial');

-- Ejemplo de historial de lectura
INSERT INTO historial_lectura (lectura_id, usuario_id, accion, descripcion) VALUES
(1, 1, 'Creación', 'Lectura registrada por el usuario');

-- Ejemplo de orden de servicio
INSERT INTO ordenes_servicio (usuario_id, tipo, descripcion) VALUES
(3, 'Reparación', 'Reparación de fuga');

-- Ejemplo de historial de orden
INSERT INTO historial_orden (orden_id, usuario_id, accion, descripcion) VALUES
(1, 1, 'Creación', 'Orden creada por el usuario');

-- Ejemplo de historial de medidor
INSERT INTO historial_medidor (medidor_id, usuario_id, accion, descripcion) VALUES
(1, 1, 'Instalación', 'Medidor instalado');

-- Ejemplo de notificación
INSERT INTO notificaciones (usuario_id, mensaje) VALUES
(3, 'Su medidor MED10001 ha sido actualizado.');

-- Ejemplo de log
INSERT INTO logs (usuario_id, accion) VALUES
(1, 'Inicio de sesión');