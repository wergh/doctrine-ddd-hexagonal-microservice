<?php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$paths = [__DIR__ . "/src/Domain/Entity"];

$isDevMode = true;

$dbParams = [
    'driver' => 'pdo_mysql',
    'user' => 'root',
    'password' => '',
    'host' => '127.0.0.1',
    'dbname' => 'docplanner_exercise',
];

$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);

$entityManager = EntityManager::create($dbParams, $config);

function GetEntityManager(): EntityManager
{
    global $entityManager;
    return $entityManager;
}
