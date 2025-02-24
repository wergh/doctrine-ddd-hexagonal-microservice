<?php
declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Entity\Doctor;
use App\Domain\Repository\DoctorRepository;
use App\Application\DTO\DoctorDTO;

/**
 * Use case for synchronizing doctor data.
 */
final class SyncDoctorUseCase
{
    private DoctorRepository $doctorRepository;

    /**
     * Constructor for SyncDoctorUseCase.
     *
     * @param DoctorRepository $doctorRepository Repository for handling doctor persistence.
     */
    public function __construct(DoctorRepository $doctorRepository)
    {
        $this->doctorRepository = $doctorRepository;
    }

    /**
     * Synchronizes a doctor entity with the given DTO data.
     * If the doctor exists, updates their information; otherwise, creates a new doctor.
     *
     * @param DoctorDTO $doctorDTO The data transfer object containing doctor information.
     * @return Doctor The synchronized doctor entity.
     */
    public function execute(DoctorDTO $doctorDTO): Doctor
    {
        $doctor = $this->doctorRepository->find($doctorDTO->id)
            ?? new Doctor($doctorDTO->id, $doctorDTO->name);

        $doctor->setName($doctorDTO->name);
        $doctor->clearError();
        $this->doctorRepository->save($doctor);

        return $doctor;
    }
}
