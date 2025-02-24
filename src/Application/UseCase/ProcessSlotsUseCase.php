<?php

namespace App\Application\UseCase;

use App\Domain\Entity\Doctor;
use App\Application\Service\SlotProcessorInterface;
use Generator;

/**
 * Use case responsible for processing slots for a given doctor.
 */
final class ProcessSlotsUseCase
{
    private SlotProcessorInterface $slotProcessor;

    /**
     * @param SlotProcessorInterface $slotProcessor Service for processing slot data.
     */
    public function __construct(SlotProcessorInterface $slotProcessor)
    {
        $this->slotProcessor = $slotProcessor;
    }

    /**
     * Processes an array of slot data for a given doctor.
     *
     * @param Doctor $doctor The doctor whose slots are being processed.
     * @param array $slotsData The raw slot data.
     * @return Generator Yields SlotDTO instances.
     */
    public function execute(Doctor $doctor, array $slotsData): Generator
    {
        return $this->slotProcessor->process($doctor, $slotsData);
    }
}
