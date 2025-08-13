CREATE DATABASE IF NOT EXISTS parqueadero;
USE parqueadero;

CREATE TABLE IF NOT EXISTS vehiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(10) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    documento VARCHAR(20) NOT NULL,
    tipo VARCHAR(20) NOT NULL
);

CREATE TABLE IF NOT EXISTS tarifas (
    id INT PRIMARY KEY,
    bicicleta INT,
    bus INT,
    carro INT,
    moto INT
);

INSERT INTO tarifas (id, bicicleta, bus, carro, moto) VALUES (1, 0, 0, 0, 0)
ON DUPLICATE KEY UPDATE id=1;
