<?php

declare(strict_types=1);

namespace App\Infrastructure\Provider;

use App\Application\Provider\DoctorProviderInterface;
use RuntimeException;

/**
 * Implementation of DoctorProvider that fetches data from an external API.
 */
class ApiDoctorProviderInterface extends ApiProvider implements DoctorProviderInterface
{

    /**
     * Fetches the list of doctors from the external API.
     *
     * @return array The list of doctors.
     * @throws RuntimeException If an error occurs during the data fetch.
     */
    public function getDoctors(): array
    {
        $response = $this->fetchData($this->endpoint);

        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }

}
