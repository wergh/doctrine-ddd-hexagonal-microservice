<?php
declare(strict_types=1);

namespace App\Application\DTO;

use DateTime;

/**
 * Data Transfer Object (DTO) for a Slot.
 * This class is used to transfer slot data between application layers.
 */
class SlotDTO
{
    /**
     * The unique identifier of the doctor associated with the slot.
     *
     * @var int
     */
    public int $doctorId;

    /**
     * The start time of the slot.
     *
     * @var DateTime
     */
    public DateTime $start;

    /**
     * The end time of the slot.
     *
     * @var DateTime
     */
    public DateTime $end;

    /**
     * SlotDTO constructor.
     *
     * @param int $doctorId The unique identifier of the doctor.
     * @param DateTime $start The start time of the slot.
     * @param DateTime $end The end time of the slot.
     */
    public function __construct(int $doctorId, DateTime $start, DateTime $end)
    {
        $this->doctorId = $doctorId;
        $this->start = $start;
        $this->end = $end;
    }
}
