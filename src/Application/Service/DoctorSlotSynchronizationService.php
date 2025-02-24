<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\DoctorDTO;
use App\Application\UseCase\MarkDoctorErrorUseCase;
use App\Application\UseCase\ProcessSlotsUseCase;
use App\Application\UseCase\SyncDoctorUseCase;
use App\Application\UseCase\SyncSlotUseCase;
use App\Application\Provider\DoctorProviderInterface;
use App\Application\Provider\SlotProviderInterface;
use Carbon\Carbon;
use DateTime;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Service responsible for synchronizing doctors and their slots.
 */
class DoctorSlotSynchronizationService
{
    private SyncDoctorUseCase $syncDoctorUseCase;
    private SyncSlotUseCase $syncSlotUseCase;
    private MarkDoctorErrorUseCase $markDoctorErrorUseCase;
    private ProcessSlotsUseCase $processSlotsUseCase;
    private DoctorProviderInterface $doctorProvider;
    private SlotProviderInterface $slotProvider;
    private LoggerInterface $logger;

    /**
     * Constructor for the synchronization service.
     *
     * @param SyncDoctorUseCase $syncDoctorUseCase Use case for synchronizing doctors.
     * @param SyncSlotUseCase $syncSlotUseCase Use case for synchronizing slots.
     * @param MarkDoctorErrorUseCase $markDoctorErrorUseCase Use case for marking a doctor with an error.
     * @param DoctorProviderInterface $doctorProvider External provider for doctor data.
     * @param SlotProviderInterface $slotProvider External provider for slot data.
     * @param LoggerInterface $logger Logger instance for logging information.
     */
    public function __construct(
        SyncDoctorUseCase       $syncDoctorUseCase,
        SyncSlotUseCase         $syncSlotUseCase,
        MarkDoctorErrorUseCase  $markDoctorErrorUseCase,
        ProcessSlotsUseCase     $processSlotsUseCase,
        DoctorProviderInterface $doctorProvider,
        SlotProviderInterface   $slotProvider,
        LoggerInterface         $logger
    )
    {
        $this->syncDoctorUseCase = $syncDoctorUseCase;
        $this->syncSlotUseCase = $syncSlotUseCase;
        $this->markDoctorErrorUseCase = $markDoctorErrorUseCase;
        $this->processSlotsUseCase = $processSlotsUseCase;
        $this->doctorProvider = $doctorProvider;
        $this->slotProvider = $slotProvider;
        $this->logger = $logger;
    }

    /**
     * Synchronizes doctors and their slots from an external provider.
     *
     * Fetch doctors and their respective slots, updating them in the system.
     * If an error occurs while retrieving slots, the doctor is marked as having an error.
     */
    public function synchronize(): void
    {

        try {
            $doctors = $this->doctorProvider->getDoctors();
        } catch (RuntimeException $e) {
            if ($this->shouldReportErrors()) {
                $this->logger->error("Error getting Doctor from endpoint : " . $e->getMessage());
            }
            echo "Finished with error.\n";
            return;
        }

        foreach ($doctors as $doctorData) {
            $doctorDTO = new DoctorDTO($doctorData['id'], $doctorData['name']);
            $doctor = $this->syncDoctorUseCase->execute($doctorDTO);

            try {
                $slots = $this->slotProvider->getSlots($doctorDTO->id);

                foreach ($this->processSlotsUseCase->execute($doctor, $slots) as $slotData) {
                    $this->syncSlotUseCase->execute($doctor, $slotData);
                }
            } catch (RuntimeException $e) {
                if ($this->shouldReportErrors()) {
                    $this->logger->error("The slots for " . $doctorDTO->name . " could not be retrieved : " . $e->getMessage());
                }
                $this->markDoctorErrorUseCase->execute($doctor);
                continue;
            }
        }
    }

    /**
     * Determines whether errors should be reported based on the current day.
     *
     * @return bool True if errors should be reported, false otherwise.
     */
    protected function shouldReportErrors(): bool
    {
        return Carbon::now()->format('D') !== 'Sun';
    }
}
