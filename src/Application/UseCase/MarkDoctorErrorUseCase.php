<?php
declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Entity\Doctor;
use App\Domain\Repository\DoctorRepository;

/**
 * Use case for marking a doctor as having an error.
 */
final class MarkDoctorErrorUseCase
{
    private DoctorRepository $doctorRepository;

    /**
     * Constructor for MarkDoctorErrorUseCase.
     *
     * @param DoctorRepository $doctorRepository Repository for handling doctor persistence.
     */
    public function __construct(DoctorRepository $doctorRepository)
    {
        $this->doctorRepository = $doctorRepository;
    }

    /**
     * Marks the given doctor as having an error and persists the change.
     *
     * @param Doctor $doctor The doctor entity to update.
     */
    public function execute(Doctor $doctor): void
    {
        $doctor->markError();
        $this->doctorRepository->save($doctor);
    }
}
