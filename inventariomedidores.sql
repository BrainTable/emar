-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS InventarioMedidores;

USE InventarioMedidores;

-- Crear la tabla medidores
CREATE TABLE medidores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_serie VARCHAR(50) NOT NULL UNIQUE,
    tipo VARCHAR(50) NOT NULL,
    ubicacion VARCHAR(100),
    estado ENUM('activo', 'mantenimiento', 'inactivo') NOT NULL,
    fecha_instalacion DATE,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar datos de ejemplo
INSERT INTO medidores (numero_serie, tipo, ubicacion, estado, fecha_instalacion) VALUES
('MED-001', 'Residencial', 'Zona Norte',  'activo',        '2023-01-15'),
('MED-002', 'Industrial',  'Zona Sur',    'mantenimiento', '2023-02-10'),
('MED-003', 'Comercial',   'Zona Centro', 'activo',        '2023-03-05'),
('MED-004', 'Residencial', 'Zona Norte',  'activo',        '2023-04-16'),
('MED-005', 'Industrial',  'Zona Sur',    'mantenimiento', '2023-05-19'),
('MED-006', 'Comercial',   'Zona Centro', 'activo',        '2023-06-04'),
('MED-007', 'Residencial', 'Zona Norte',  'activo',        '2023-07-17'),
('MED-008', 'Industrial',  'Zona Sur',    'mantenimiento', '2023-08-11'),
('MED-009', 'Comercial',   'Zona Centro', 'activo',        '2023-09-03'),
('MED-010', 'Residencial', 'Zona Norte',  'activo',        '2023-10-18'),
('MED-011', 'Industrial',  'Zona Sur',    'mantenimiento', '2023-11-12'),
('MED-012', 'Comercial',   'Zona Centro', 'activo',        '2023-12-02'),
('MED-013', 'Residencial', 'Zona Norte',  'activo',        '2024-01-19'),
('MED-014', 'Industrial',  'Zona Sur',    'mantenimiento', '2024-02-13'),
('MED-015', 'Comercial',   'Zona Centro', 'activo',        '2024-03-01'),
('MED-016', 'Residencial', 'Zona Norte',  'activo',        '2024-04-20'),
('MED-017', 'Industrial',  'Zona Sur',    'mantenimiento', '2024-05-14'),
('MED-018', 'Comercial',   'Zona Centro', 'activo',        '2024-06-28'),
('MED-019', 'Residencial', 'Zona Norte',  'activo',        '2024-07-21'),
('MED-020', 'Industrial',  'Zona Sur',    'mantenimiento', '2024-08-15'),
('MED-021', 'Comercial',   'Zona Centro', 'activo',        '2024-09-27'),
('MED-022', 'Residencial', 'Zona Norte',  'activo',        '2024-10-22'),
('MED-023', 'Industrial',  'Zona Sur',    'mantenimiento', '2024-11-16'),
('MED-024', 'Comercial',   'Zona Centro', 'activo', 	   '2024-12-26'),
('MED-025','Residencial',  'Zona Norte',  'activo',        '2025-01-23'),
('MED-026','Industrial',    'Zona Sur',    'mantenimiento','2025-02-17'),
('MED-027','Comercial',     'Zona Centro', 'activo',       '2025-03-25'),
('MED0-28','Residencial',   'Zona Norte',  'activo',       '2025-04-22'),
('MED-029','Industrial',    'Zona Sur',    'mantenimiento','2022-05-18'),
('MED-030', 'Residencial', 'Zona Norte',  'activo',        '2022-06-15'),
('MED-031', 'Industrial',  'Zona Sur',    'mantenimiento', '2022-07-10'),
('MED-032', 'Comercial',   'Zona Centro', 'activo',        '2022-08-05'),
('MED-033', 'Residencial', 'Zona Norte',  'activo',        '2022-09-15'),
('MED-034', 'Industrial',  'Zona Sur',    'mantenimiento', '2022-10-10'),
('MED-035', 'Comercial',   'Zona Centro', 'activo',        '2022-11-05'),
('MED-036', 'Residencial', 'Zona Norte',  'activo',        '2022-12-15'),
('MED-037', 'Industrial',  'Zona Sur',    'mantenimiento', '2021-01-10'),
('MED-038', 'Comercial',   'Zona Centro', 'activo',        '2021-02-05'),
('MED-039', 'Residencial', 'Zona Norte',  'activo',        '2021-03-15'),
('MED-040', 'Industrial',  'Zona Sur',    'mantenimiento', '2021-04-10'),
('MED-041', 'Comercial',   'Zona Centro', 'activo',        '2021-05-05'),
('MED-042', 'Residencial', 'Zona Norte',  'activo',        '2021-06-15'),
('MED-043', 'Industrial',  'Zona Sur',    'mantenimiento', '2021-07-10'),
('MED-044', 'Comercial',   'Zona Centro', 'activo',        '2021-08-05'),
('MED-045', 'Residencial', 'Zona Norte',  'activo',        '2021-09-15'),
('MED-046', 'Industrial',  'Zona Sur',    'mantenimiento', '2021-10-10'),
('MED-047', 'Comercial',   'Zona Centro', 'activo',        '2021-11-05'),
('MED-048', 'Residencial', 'Zona Norte',  'activo',        '2021-12-15'),
('MED-049', 'Industrial',  'Zona Sur',    'mantenimiento', '2020-01-10'),
('MED-050', 'Comercial',   'Zona Centro', 'activo',        '2020-02-05'),
('MED-051', 'Residencial', 'Zona Norte',  'activo',        '2020-03-15'),
('MED-052', 'Industrial',  'Zona Sur',    'mantenimiento', '2020-04-10'),
('MED-053', 'Comercial',   'Zona Centro', 'activo',        '2020-05-05'),
('MED-054', 'Residencial', 'Zona Norte',  'activo',        '2020-06-15'),
('MED-055', 'Industrial',  'Zona Sur',    'mantenimiento', '2020-07-10'),
('MED-056', 'Comercial',   'Zona Centro', 'activo',        '2020-08-05'),
('MED-057', 'Residencial', 'Zona Norte',  'activo',        '2020-09-15'),
('MED-058', 'Industrial',  'Zona Sur',    'mantenimiento', '2020-10-10'),
('MED-059', 'Comercial',   'Zona Centro', 'activo',        '2020-11-05'),
('MED-060', 'Residencial', 'Zona Norte',  'activo',        '2020-12-15'),
('MED-061', 'Industrial',  'Zona Sur',    'mantenimiento', '2020-01-10'),
('MED-062', 'Comercial',   'Zona Centro', 'activo',        '2020-02-05');<?php
<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "InventarioMedidores";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>