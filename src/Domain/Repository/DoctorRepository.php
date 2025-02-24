<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Doctor;

/**
 * Interface for the Doctor repository.
 * Defines the contract for retrieving and persisting Doctor entities.
 */
interface DoctorRepository
{
    /**
     * Finds a doctor by their unique identifier.
     *
     * @param int $id The doctor's ID.
     * @return Doctor|null The found doctor or null if not found.
     */
    public function find(int $id): ?Doctor;

    /**
     * Saves a doctor entity to the repository.
     *
     * @param Doctor $doctor The doctor entity to save.
     */
    public function save(Doctor $doctor): void;
}
