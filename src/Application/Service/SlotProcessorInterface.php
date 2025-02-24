<?php

namespace App\Application\Service;

use App\Domain\Entity\Doctor;
use App\Application\DTO\SlotDTO;
use Generator;

/**
 * Defines a contract for processing slot data and converting it into SlotDTO objects.
 */
interface SlotProcessorInterface
{
    /**
     * Processes an array of slot data for a given doctor.
     *
     * @param Doctor $doctor The doctor whose slots are being processed.
     * @param array $slotsData The raw slot data.
     * @return Generator<SlotDTO> A generator yielding SlotDTO instances.
     */
    public function process(Doctor $doctor, array $slotsData): Generator;
}
