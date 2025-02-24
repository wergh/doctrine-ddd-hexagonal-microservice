<?php
declare(strict_types=1);

namespace App\Infrastructure\Provider;

use App\Application\Provider\SlotProviderInterface;
use RuntimeException;

/**
 * Implementation of SlotProvider that fetches data from an external API.
 */
class ApiSlotProviderInterface extends ApiProvider implements SlotProviderInterface
{

    /**
     * Fetches the slots for a specific doctor by their ID from the external API.
     *
     * @param int $doctorId The ID of the doctor for whom to fetch the slots.
     * @return array The list of slots for the doctor.
     * @throws RuntimeException If an error occurs during the data fetch.
     */
    public function getSlots(int $doctorId): array
    {
        $response = $this->fetchData($this->endpoint . '/' . $doctorId . '/slots');

        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }

}
