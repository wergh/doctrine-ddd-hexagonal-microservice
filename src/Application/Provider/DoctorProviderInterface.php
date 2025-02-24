<?php

declare(strict_types=1);

namespace App\Application\Provider;

/**
 * Interface for retrieving doctor and slot data from external sources.
 */
interface DoctorProviderInterface
{
    /**
     * Retrieves a list of doctors.
     *
     * @return array An array of doctors.
     */
    public function getDoctors(): array;

}
