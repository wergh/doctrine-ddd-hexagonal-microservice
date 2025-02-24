<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\DoctorSlotsSynchronizer;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\DBAL\DriverManager;

// Configuración de Doctrine ORM


$config = Setup::createAnnotationMetadataConfiguration(
    [realpath(__DIR__ . "/src/Domain/Entity")], // Ruta correcta a las entidades
    true, // Modo desarrollo
    null, // Directorio de caché (dejamos que Doctrine use la predeterminada)
    null, // Configuración de los proxies (dejar predeterminada)
    false  // Disable the second-level cache
);


// Configuración de la base de datos
$connection = [
    'dbname' => 'docplanner_exercise',
    'user' => 'root',
    'password' => '',
    'host' => '127.0.0.1',
    'driver' => 'pdo_mysql',
];

// 🔹 Aquí está la corrección: Usamos EntityManager::create() en lugar de new EntityManager()
$entityManager = EntityManager::create(
    DriverManager::getConnection($connection),
    $config
);

// Instanciar sincronizador y ejecutar
$synchronizer = new DoctorSlotsSynchronizer($entityManager);

//echo "Sincronizando doctores...\n";
$synchronizer->synchronizeDoctorSlots();
//echo "Sincronización completada.\n";
