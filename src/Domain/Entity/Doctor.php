<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a Doctor entity.
 *
 * @ORM\Entity
 * @ORM\Table(name="doctors")
 */
class Doctor
{
    /**
     * The unique identifier of the doctor.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * The name of the doctor.
     *
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * Indicates if the doctor has an error.
     *
     * @ORM\Column(type="boolean")
     */
    private bool $error = false;

    /**
     * The slots associated with the doctor.
     *
     * @ORM\OneToMany(targetEntity="Slot", mappedBy="doctor", cascade={"persist", "remove"})
     */
    private $slots;

    /**
     * Doctor constructor.
     *
     * @param int $id The unique identifier of the doctor.
     * @param string $name The name of the doctor.
     */
    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $this->normalizeName($name);
    }

    /**
     * Normalizes the full name of the doctor.
     *
     * @param string $fullName The full name of the doctor.
     * @return string The normalized name.
     */
    private function normalizeName(string $fullName): string
    {
        [, $surname] = explode(' ', $fullName);

        /** @see https://www.youtube.com/watch?v=PUhU3qCf0Nk */
        if (0 === stripos($surname, "o'")) {
            return ucwords(strtolower($fullName), ' \'');
        }

        return ucwords(strtolower($fullName));
    }

    /**
     * Gets the unique identifier of the doctor.
     *
     * @return int The doctor's ID.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Gets the name of the doctor.
     *
     * @return string The doctor's name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the name of the doctor.
     *
     * @param string $name The new name of the doctor.
     */
    public function setName(string $name): void
    {
        $this->name = $this->normalizeName($name);
    }

    /**
     * Marks the doctor as having an error.
     */
    public function markError(): void
    {
        $this->error = true;
    }

    /**
     * Clears the error flag for the doctor.
     */
    public function clearError(): void
    {
        $this->error = false;
    }

    /**
     * Checks if the doctor has an error.
     *
     * @return bool True if the doctor has an error, false otherwise.
     */
    public function hasError(): bool
    {
        return $this->error;
    }

    /**
     * Gets the slots associated with the doctor.
     *
     * @return mixed The slots associated with the doctor.
     */
    public function getSlots()
    {
        return $this->slots;
    }
}
