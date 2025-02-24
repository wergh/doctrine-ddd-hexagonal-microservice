<?php
declare(strict_types=1);

namespace App\Domain\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a Slot entity.
 *
 * @ORM\Entity
 * @ORM\Table(name="slots")
 * @ORM\HasLifecycleCallbacks()
 */
class Slot
{
    /**
     * The unique identifier of the slot.
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * The doctor associated with this slot.
     *
     * @ORM\ManyToOne(targetEntity="Doctor", inversedBy="slots")
     * @ORM\JoinColumn(name="doctor_id", referencedColumnName="id", nullable=false)
     */
    private Doctor $doctor;

    /**
     * The start time of the slot.
     *
     * @ORM\Column(type="datetime")
     */
    private DateTime $start;

    /**
     * The end time of the slot.
     *
     * @ORM\Column(type="datetime")
     */
    private DateTime $end;

    /**
     * The timestamp when the slot was created.
     *
     * @ORM\Column(type="datetime")
     */
    private DateTime $createdAt;

    /**
     * Slot constructor.
     *
     * @param Doctor $doctor The doctor associated with the slot.
     * @param DateTime $start The start time of the slot.
     * @param DateTime $end The end time of the slot.
     */
    public function __construct(Doctor $doctor, DateTime $start, DateTime $end)
    {
        $this->doctor = $doctor;
        $this->start = $start;
        $this->end = $end;
        $this->createdAt = new DateTime();
    }

    /**
     * Gets the unique identifier of the slot.
     *
     * @return int The slot ID.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Gets the doctor associated with the slot.
     *
     * @return Doctor The associated doctor.
     */
    public function getDoctor(): Doctor
    {
        return $this->doctor;
    }

    /**
     * Sets the doctor associated with the slot.
     *
     * @param Doctor $doctor The doctor to associate with the slot.
     */
    public function setDoctor(Doctor $doctor): void
    {
        $this->doctor = $doctor;
    }

    /**
     * Gets the start time of the slot.
     *
     * @return DateTime The slot's start time.
     */
    public function getStart(): DateTime
    {
        return $this->start;
    }

    /**
     * Gets the end time of the slot.
     *
     * @return DateTime The slot's end time.
     */
    public function getEnd(): DateTime
    {
        return $this->end;
    }

    /**
     * Sets the start time of the slot.
     *
     * @param DateTime $start The new start time.
     */
    public function setStart(DateTime $start): void
    {
        $this->start = $start;
    }

    /**
     * Sets the end time of the slot.
     *
     * @param DateTime $end The new end time.
     */
    public function setEnd(DateTime $end): void
    {
        $this->end = $end;
    }

    /**
     * Lifecycle callback that sets the creation timestamp before persisting.
     *
     * @ORM\PrePersist()
     */
    public function onPrePersist(): void
    {
        $this->createdAt = new DateTime();
    }

    /**
     * Gets the creation timestamp of the slot.
     *
     * @return DateTime The creation timestamp.
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set the creation timestamp of the slot.
     *
     * @param DateTime $createdAt The new createdAt time.
     */
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Determines if the slot is considered stale (created more than 5 minutes ago).
     *
     * @return bool True if the slot is stale, false otherwise.
     */
    public function isStale(): bool
    {
        return $this->createdAt < new DateTime('5 minutes ago');
    }
}
