<?php

namespace App\Application\Service;

use App\Domain\Entity\Doctor;
use App\Application\DTO\SlotDTO;
use DateTime;
use Generator;

/**
 * Processes slot data and converts it into SlotDTO objects using a generator.
 */
class SlotProcessor implements SlotProcessorInterface
{
    /**
     * Processes an array of slot data for a given doctor.
     *
     * @param Doctor $doctor The doctor for whom slots are being processed.
     * @param array $slotsData The raw slot data.
     * @return Generator Yields SlotDTO instances for each slot.
     */
    public function process(Doctor $doctor, array $slotsData): Generator
    {
        foreach ($slotsData as $slotData) {
            yield new SlotDTO(
                $doctor->getId(),
                new DateTime($slotData['start']),
                new DateTime($slotData['end'])
            );
        }
    }
}
