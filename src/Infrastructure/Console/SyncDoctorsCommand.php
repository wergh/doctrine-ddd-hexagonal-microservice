<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Application\Service\DoctorSlotSynchronizationService;

/**
 * Command for triggering the synchronization of doctors and their slots.
 */
class SyncDoctorsCommand
{
    private DoctorSlotSynchronizationService $syncService;

    /**
     * Constructor for SyncDoctorsCommand.
     *
     * @param DoctorSlotSynchronizationService $syncService The service responsible for syncing doctors and slots.
     */
    public function __construct(DoctorSlotSynchronizationService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Runs the synchronization process for doctors and their slots.
     * Outputs messages to the console to indicate the status of the synchronization.
     */
    public function run(): void
    {
        echo "Starting doctor synchronization...\n";
        $this->syncService->synchronize();
        echo "Synchronization completed.\n";
    }
}
