<?php

declare(strict_types=1);

namespace App\Application\Provider;

/**
 * Interface for retrieving slot data from external sources.
 */
interface SlotProviderInterface
{
    /**
     * Retrieves the available slots for a given doctor.
     *
     * @param int $doctorId The unique identifier of the doctor.
     * @return array An array of slots associated with the doctor.
     */
    public function getSlots(int $doctorId): array;
}
