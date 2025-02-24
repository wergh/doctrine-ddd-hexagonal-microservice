<?php

declare(strict_types=1);

namespace App\Infrastructure\Provider;

use App\Application\Provider\DoctorProviderInterface;
use RuntimeException;

/**
 * Implementation of DoctorProvider that fetches data from a local static JSON file.
 */
class StaticDoctorProviderInterface implements DoctorProviderInterface
{
    /**
     * Fetches the list of doctors from a local JSON file.
     *
     * @return array The list of doctors.
     * @throws RuntimeException If the file does not exist or the data cannot be read.
     */
    public function getDoctors(): array
    {
        $filePath = dirname(__DIR__, 3) . '/doctors.json';

        if (!file_exists($filePath)) {
            throw new RuntimeException('File doctors.json not found.');
        }

        $json = file_get_contents($filePath);
        if ($json === false) {
            throw new RuntimeException('Failed to read the doctors.json file.');
        }

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

}
