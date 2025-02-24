<?php

declare(strict_types=1);

use App\Application\Service\SlotProcessor;
use App\Application\UseCase\MarkDoctorErrorUseCase;
use App\Application\UseCase\ProcessSlotsUseCase;
use App\Infrastructure\Console\SyncDoctorsCommand;
use App\Infrastructure\Persistence\Doctrine\DoctrineDoctorRepository;
use App\Infrastructure\Persistence\Doctrine\DoctrineSlotRepository;
use App\Infrastructure\Provider\ApiDoctorProviderInterface;
use App\Application\Service\DoctorSlotSynchronizationService;
use App\Application\UseCase\SyncDoctorUseCase;
use App\Application\UseCase\SyncSlotUseCase;
use App\Infrastructure\Provider\ApiSlotProviderInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\DBAL\DriverManager;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();


$config = Setup::createAnnotationMetadataConfiguration(
    [realpath(__DIR__ . "/src/Domain/Entity")],
    true,
    null,
    null,
    false
);

$logger = new Logger('sync');
$logger->pushHandler(new StreamHandler(__DIR__ . '/sync.log', Logger::INFO));

$connection = [
    'dbname' => $_ENV['DB_NAME'] ?: 'default_bd',
    'user' => $_ENV['DB_USER'] ?: 'root',
    'password' => $_ENV['DB_PASSWORD'] ?: null,
    'host' => $_ENV['DB_HOST'] ?: '127.0.0.1',
    'driver' => 'pdo_mysql',
];

$entityManager = EntityManager::create(
    DriverManager::getConnection($connection),
    $config
);

$doctorRepository = new DoctrineDoctorRepository($entityManager);
$slotRepository = new DoctrineSlotRepository($entityManager);
$doctorProvider = new ApiDoctorProviderInterface();
$slotProvider = new ApiSlotProviderInterface();

$syncDoctorUseCase = new SyncDoctorUseCase($doctorRepository);
$syncSlotUseCase = new SyncSlotUseCase($slotRepository);
$markErrorUseCase = new MarkDoctorErrorUseCase($doctorRepository);
$slotProcessor =  new ProcessSlotsUseCase(new SlotProcessor());

$syncService = new DoctorSlotSynchronizationService(
    $syncDoctorUseCase,
    $syncSlotUseCase,
    $markErrorUseCase,
    $slotProcessor,
    $doctorProvider,
    $slotProvider,
    $logger
);

$syncCommand = new SyncDoctorsCommand($syncService);
$syncCommand->run();
