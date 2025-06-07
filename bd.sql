-- Base de datos normalizada hasta 3FN
CREATE DATABASE sistema_trabajadores;
USE sistema_trabajadores;

-- Tabla principal: Persona (1FN)
CREATE TABLE persona (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(15) NOT NULL UNIQUE,
    tipo_cedula ENUM('N', 'E') NOT NULL,
    primer_nombre VARCHAR(50) NOT NULL,
    segundo_nombre VARCHAR(50),
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla Universidad (3FN)
CREATE TABLE universidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    pais VARCHAR(50) DEFAULT 'Panamá'
);

-- Tabla Profesión (2FN y 3FN)
CREATE TABLE profesion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT NOT NULL,
    id_universidad INT NOT NULL,
    titulo_universitario VARCHAR(100) NOT NULL,
    FOREIGN KEY (id_persona) REFERENCES persona(id),
    FOREIGN KEY (id_universidad) REFERENCES universidad(id)
);

-- Tabla Trabajo (2FN)
CREATE TABLE trabajo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT NOT NULL,
    codigo_trabajador VARCHAR(20) NOT NULL UNIQUE,
    cargo VARCHAR(100) NOT NULL,
    empresa VARCHAR(100) NOT NULL,
    salario_bruto DECIMAL(10,2) NOT NULL,
    estatus ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_persona) REFERENCES persona(id)
);

-- Tabla Urbanización (3FN)
CREATE TABLE urbanizacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ciudad VARCHAR(50) NOT NULL
);

-- Tabla Residencia (2FN y 3FN)
CREATE TABLE residencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT NOT NULL,
    id_urbanizacion INT NOT NULL,
    numero_casa VARCHAR(10) NOT NULL,
    FOREIGN KEY (id_persona) REFERENCES persona(id),
    FOREIGN KEY (id_urbanizacion) REFERENCES urbanizacion(id)
);