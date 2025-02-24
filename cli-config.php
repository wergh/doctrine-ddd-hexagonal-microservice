<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once 'bootstrap.php';

$entityManager = GetEntityManager();

return ConsoleRunner::createHelperSet($entityManager);
