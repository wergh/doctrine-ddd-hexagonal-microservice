<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Doctor;
use App\Domain\Entity\Slot;
use DateTime;

/**
 * Interface for the Slot repository.
 * Defines the contract for retrieving and persisting Slot entities.
 */
interface SlotRepository
{
    /**
     * Finds a slot by doctor and start time.
     *
     * @param Doctor $doctor The doctor associated with the slot.
     * @param DateTime $start The start time of the slot.
     * @return Slot|null The found slot or null if not found.
     */
    public function findByDoctorAndStart(Doctor $doctor, DateTime $start): ?Slot;

    /**
     * Saves a slot entity to the repository.
     *
     * @param Slot $slot The slot entity to save.
     */
    public function save(Slot $slot): void;
}
