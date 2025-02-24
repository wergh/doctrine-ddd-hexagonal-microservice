<?php
declare(strict_types=1);

namespace App\Infrastructure\Provider;

use App\Application\Provider\SlotProvider;

/**
 * Implementation of SlotProvider that fetches data static.
 */
class StaticSlotProvider implements SlotProvider
{

    /**
     * Fetches the slots for a specific doctor by their ID. This is a placeholder method
     * and can be modified to fetch actual slot data.
     *
     * @param int $doctorId The ID of the doctor for whom to fetch the slots.
     * @return array An empty array or a list of slots for the doctor.
     */
    public function getSlots(int $doctorId): array
    {
        return [];
    }
}
