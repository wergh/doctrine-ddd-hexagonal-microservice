<?php
declare(strict_types=1);

namespace App\Application\DTO;

/**
 * Data Transfer Object (DTO) for a Doctor.
 * This class is used to transfer doctor data between application layers.
 */
class DoctorDTO
{
    /**
     * The unique identifier of the doctor.
     *
     * @var int
     */
    public int $id;

    /**
     * The name of the doctor.
     *
     * @var string
     */
    public string $name;

    /**
     * DoctorDTO constructor.
     *
     * @param int $id The unique identifier of the doctor.
     * @param string $name The name of the doctor.
     */
    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}
