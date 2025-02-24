<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Entity\Doctor;
use App\Domain\Entity\Slot;
use App\Domain\Repository\SlotRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

/**
 * Implementation of SlotRepository using Doctrine ORM for data persistence.
 */
final class DoctrineSlotRepository implements SlotRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * Constructor for DoctrineSlotRepository.
     *
     * @param EntityManagerInterface $entityManager The Doctrine entity manager used for persisting entities.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Finds a slot by the doctor and start time.
     *
     * @param Doctor $doctor The doctor entity associated with the slot.
     * @param DateTime $start The start time of the slot.
     * @return Slot|null The slot entity or null if not found.
     */
    public function findByDoctorAndStart(Doctor $doctor, DateTime $start): ?Slot
    {
        return $this->entityManager->getRepository(Slot::class)->findOneBy([
            'doctor' => $doctor,
            'start' => $start
        ]);
    }

    /**
     * Saves a slot entity to the database.
     *
     * @param Slot $slot The slot entity to persist.
     */
    public function save(Slot $slot): void
    {
        $this->entityManager->persist($slot);
        $this->entityManager->flush();
    }
}
