<?php
declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Entity\Doctor;
use App\Domain\Entity\Slot;
use App\Domain\Repository\SlotRepository;
use App\Application\DTO\SlotDTO;
use Psr\Log\LoggerInterface;

/**
 * Use case for synchronizing slot data for a doctor.
 */
final class SyncSlotUseCase
{
    private SlotRepository $slotRepository;

    /**
     * Constructor for SyncSlotUseCase.
     *
     * @param SlotRepository $slotRepository Repository for handling slot persistence.
     * @param LoggerInterface $logger Logger service for logging slot synchronization details.
     */
    public function __construct(SlotRepository $slotRepository)
    {
        $this->slotRepository = $slotRepository;
    }

    /**
     * Synchronizes a slot for the given doctor and slot data.
     * If the slot already exists for the doctor and time, updates it, otherwise creates a new slot.
     *
     * @param Doctor $doctor The doctor entity for whom the slot is being synchronized.
     * @param SlotDTO $slotDTO The data transfer object containing slot start and end times.
     */
    public function execute(Doctor $doctor, SlotDTO $slotDTO): void
    {
        $slot = $this->slotRepository->findByDoctorAndStart($doctor, $slotDTO->start)
            ?? new Slot($doctor, $slotDTO->start, $slotDTO->end);
        if ($slot->isStale()) {
            $slot->setEnd($slotDTO->end);
        }

        $this->slotRepository->save($slot);

    }
}
