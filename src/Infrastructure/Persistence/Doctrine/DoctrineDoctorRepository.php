<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Entity\Doctor;
use App\Domain\Repository\DoctorRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Implementation of DoctorRepository using Doctrine ORM for data persistence.
 */
final class DoctrineDoctorRepository implements DoctorRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * Constructor for DoctrineDoctorRepository.
     *
     * @param EntityManagerInterface $entityManager The Doctrine entity manager used for persisting entities.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Finds a doctor by their unique identifier.
     *
     * @param int $id The unique identifier of the doctor.
     * @return Doctor|null The doctor entity or null if not found.
     */
    public function find(int $id): ?Doctor
    {
        return $this->entityManager->find(Doctor::class, $id);
    }

    /**
     * Saves a doctor entity to the database.
     *
     * @param Doctor $doctor The doctor entity to persist.
     */
    public function save(Doctor $doctor): void
    {
        $this->entityManager->persist($doctor);
        $this->entityManager->flush();
    }
}
