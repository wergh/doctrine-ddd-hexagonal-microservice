<?php


use App\Application\DTO\DoctorDTO;
use App\Application\DTO\SlotDTO;
use App\Application\Provider\DoctorProvider;
use App\Application\Provider\SlotProvider;
use App\Application\Service\DoctorSlotSynchronizationService;
use App\Application\Service\SlotProcessor;
use App\Application\UseCase\MarkDoctorErrorUseCase;
use App\Application\UseCase\ProcessSlotsUseCase;
use App\Application\UseCase\SyncDoctorUseCase;
use App\Application\UseCase\SyncSlotUseCase;
use App\Domain\Entity\Doctor;
use App\Domain\Entity\Slot;
use App\Domain\Repository\DoctorRepository;
use App\Domain\Repository\SlotRepository;
use App\Infrastructure\Console\SyncDoctorsCommand;
use Doctrine\ORM\EntityManager;
use \Carbon\Carbon;

beforeEach(function () {

    //First, we mock the repositories and the providers.

    $this->entityManager = $this->createMock(EntityManager::class);
    $this->doctorRepository = $this->createMock(DoctorRepository::class);
    $this->slotRepository = $this->createMock(SlotRepository::class);

    $this->entityManager->method('getRepository')
        ->with(Doctor::class)
        ->willReturn($this->doctorRepository);

    $this->entityManager->method('getRepository')
        ->with(Slot::class)
        ->willReturn($this->slotRepository);

    $this->doctorProvider = $this->createMock(DoctorProvider::class);
    $this->slotProvider = $this->createMock(SlotProvider::class);

    //We create the use cases with our repository mocks.

    $this->syncDoctorUseCase = new SyncDoctorUseCase($this->doctorRepository);
    $this->processSlotsUseCase = new ProcessSlotsUseCase(new SlotProcessor());
    $this->syncSlotUseCase = new SyncSlotUseCase($this->slotRepository);
    $this->markDoctorErrorUseCase = new MarkDoctorErrorUseCase($this->doctorRepository);
    $this->slotProcessor =  new ProcessSlotsUseCase(new SlotProcessor());

    //We create a mock for the logger that should only be called once (since only one doctor will have an error when retrieving the slots).
    $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);


});


it('Testing error in getDoctors call', function () {


    Carbon::setTestNow(Carbon::createFromDate(2025, 2, 11));
    $this->doctorProvider->method('getDoctors')
        ->willReturnCallback(function() {
            throw new RuntimeException('Error 500 - Server Error');
        });
    $this->logger->expects($this->once())
        ->method('error')
        ->with($this->callback(function ($message){
            return str_contains($message, "Error getting Doctor from endpoint");
        }));

    $syncService = new DoctorSlotSynchronizationService(
        $this->syncDoctorUseCase,
        $this->syncSlotUseCase,
        $this->markDoctorErrorUseCase,
        $this->slotProcessor,
        $this->doctorProvider,
        $this->slotProvider,
        $this->logger
    );

    $syncCommand = new SyncDoctorsCommand($syncService);
    $syncCommand->run();

});

it('Testing doctors error clear', function () {

    $doctor = new Doctor(1, 'Carlos Lopez');
    $doctor->markError();
    $this->doctorRepository->expects($this->once())
        ->method('find')
        ->willReturnMap([
            [1, $doctor]
        ]);

    $doctorDTO = new DoctorDTO(1, 'Carlos Lopez');
    expect($doctor->hasError())->toBeTrue();
    $this->doctorRepository->expects($this->once())
        ->method('save')
        ->will($this->returnCallback(function ($doctor) {
            expect($doctor->hasError())->toBeFalse();
        }));
    $this->syncDoctorUseCase->execute($doctorDTO);

});



it('Testing slots only update when they are stale', function () {

    // We will get two slots, one from 14:50 to 15:40
    // The other one is from 15:30 - 16:00
    // The timming is bad, i know, but for test propouses works


    $slotsDTO = [
        new SlotDTO(1, new DateTime("2020-02-01T14:50:00+00:00"), new DateTime("2020-02-01T15:40:00+00:00")),
        new SlotDTO(1, new DateTime("2020-02-01T15:30:00+00:00"), new DateTime("2020-02-01T16:00:00+00:00")),
    ];

    $doctor = new Doctor(1, 'Carlos Lopez');

    // Now we will create slots and asign this to the doctor.
    // The first one goes from 14:50 to 15:30, just created
    // The second one goes from 15:30 to 16:50, and created 6 minutes ago

    $existingSlotNoStale = new Slot($doctor, DateTime::createFromFormat("Y-m-d H:i:s","2020-02-01 14:50:00"), DateTime::createFromFormat("Y-m-d H:i:s","2020-02-01 15:30:00"));
    $existingSlotStale = new Slot($doctor, DateTime::createFromFormat("Y-m-d H:i:s","2020-02-01 15:30:00"), DateTime::createFromFormat("Y-m-d H:i:s","2020-02-01 16:50:00"));
    $existingSlotStale->setCreatedAt(new DateTime('6 minutes ago'));

    $callCountSlotFind = 0;
    $this->slotRepository->method('findByDoctorAndStart')
        ->will($this->returnCallback(function ($slot) use (&$callCountSlotFind, $existingSlotNoStale, $existingSlotStale) {
            $callCountSlotFind++;
            if ($callCountSlotFind === 1) {
                return $existingSlotNoStale;
            }
            if ($callCountSlotFind === 2) {
                return $existingSlotStale;
            }
        }));

    //The test will pass if the first slot endtime is NOT updated, becase the slot is not stale
    //And the second one is updated because the slot is stale

    $callCountSlotSave = 0;
    $this->slotRepository->expects($this->exactly(2))
        ->method('save')
        ->will($this->returnCallback(function ($slot) use (&$callCountSlotSave) {
            $callCountSlotSave++;
            if ($callCountSlotSave === 1) {
                expect($slot->getEnd() == DateTime::createFromFormat("Y-m-d H:i:s","2020-02-01 15:30:00"))->toBeTrue();
            }

            if ($callCountSlotSave === 2) {
                expect($slot->getEnd() == DateTime::createFromFormat("Y-m-d H:i:s","2020-02-01 16:50:00"))->toBeFalse();
                expect($slot->getEnd() == DateTime::createFromFormat("Y-m-d H:i:s","2020-02-01 16:00:00"))->toBeTrue();
            }
        }));

    foreach ($slotsDTO as $slotData) {
        $this->syncSlotUseCase->execute($doctor, $slotData);
    }
});


it('Testing doctor mark with error', function () {
  // If the slot repository return an error we need to mark with an error the doctor

    //Will be a week day no Sunday
    Carbon::setTestNow(Carbon::createFromDate(2025, 2, 11));

    $doctors = [
        ['id' => 1, 'name' => "With Error"],
    ];

    $this->doctorProvider->method('getDoctors')->willReturn($doctors);

    $this->slotProvider->method('getSlots')
        ->willReturnCallback(function() {
            throw new RuntimeException('Error 500 - Server Error');
        });

    $this->logger->expects($this->once())
        ->method('error')
        ->with($this->callback(function ($message){
            return str_contains($message, "With Error") &&
                str_contains($message, 'could not be retrieved');
        }));

    $existingDoctor = new Doctor(1, 'With Error');
    $this->doctorRepository->method('find')
        ->willReturnMap([
            [1, $existingDoctor],
        ]);

    $callCountDoctorSave = 0;
    $this->doctorRepository->expects($this->exactly(2))
        ->method('save')
        ->will($this->returnCallback(function ($doctor) use (&$callCountDoctorSave) {
            $callCountDoctorSave++;
            if ($callCountDoctorSave === 1) {
                expect($doctor->getName())->toBe('With Error');
                expect($doctor->hasError())->toBeFalse();
            }
            if ($callCountDoctorSave === 2) {
                expect($doctor->getName())->toBe('With Error');
                expect($doctor->hasError())->ToBeTrue();
            }
        }));

    $syncService = new DoctorSlotSynchronizationService(
        $this->syncDoctorUseCase,
        $this->syncSlotUseCase,
        $this->markDoctorErrorUseCase,
        $this->slotProcessor,
        $this->doctorProvider,
        $this->slotProvider,
        $this->logger
    );

    $syncCommand = new SyncDoctorsCommand($syncService);
    $syncCommand->run();

});

it('Check if log is not registered on Sundays', function () {


    Carbon::setTestNow(Carbon::createFromDate(2025, 2, 9));

    $doctors = [
        ['id' => 1, 'name' => "With Error"],
    ];

    $this->doctorProvider->method('getDoctors')->willReturn($doctors);

    $this->slotProvider->method('getSlots')
        ->willReturnCallback(function() {
            throw new RuntimeException('Error 500 - Server Error');
        });


    $existingDoctor = new Doctor(1, 'With Error');
    $this->doctorRepository->method('find')
        ->willReturnMap([
            [1, $existingDoctor],
        ]);

    $callCountDoctorSave = 0;
    $this->doctorRepository->expects($this->exactly(2))
        ->method('save')
        ->will($this->returnCallback(function ($doctor) use (&$callCountDoctorSave) {
            $callCountDoctorSave++;
            if ($callCountDoctorSave === 1) {
                expect($doctor->getName())->toBe('With Error');
                expect($doctor->hasError())->toBeFalse();
            }
            if ($callCountDoctorSave === 2) {
                expect($doctor->getName())->toBe('With Error');
                expect($doctor->hasError())->ToBeTrue();
            }
        }));

    $syncService = new DoctorSlotSynchronizationService(
        $this->syncDoctorUseCase,
        $this->syncSlotUseCase,
        $this->markDoctorErrorUseCase,
        $this->slotProcessor,
        $this->doctorProvider,
        $this->slotProvider,
        $this->logger
    );

    $syncCommand = new SyncDoctorsCommand($syncService);
    $syncCommand->run();

});


it('Testing Full Sync Service Process', function () {

    Carbon::setTestNow(Carbon::createFromDate(2025, 2, 11));

    //We mock the doctor API responses with the cases we want to verify:
    //A normal name
    //A name in lowercase
    //A name with an apostrophe


    $doctors = [
        ['id' => 1, 'name' => "Without Error"],
        ['id' => 2, 'name' => "with error"],
        ['id' => 3, 'name' => "name o'donel"],
    ];

    //Next, we mock the API responses for the slots. Only two of them will have available slots, and the second doctor will return a 500 error for the call. The first slot has an end time of 15:40.
    $slotsDoctor1 = [
        ["start" => "2020-02-01T14:50:00+00:00","end" => "2020-02-01T15:40:00+00:00"],
        ["start" => "2020-02-01T15:30:00+00:00","end" => "2020-02-01T16:00:00+00:00"],
    ];

    $slotsDoctor3 = [
        ["start" => "2020-02-01T14:50:00+00:00","end" => "2020-02-01T15:30:00+00:00"],
        ["start" => "2020-02-01T15:30:00+00:00","end" => "2020-02-01T16:00:00+00:00"],
    ];


    $this->doctorProvider->method('getDoctors')->willReturn($doctors);

    $callCount = 0;
    $this->slotProvider->method('getSlots')
        ->willReturnCallback(function() use(&$callCount, $slotsDoctor1, $slotsDoctor3) {
            $callCount++;
            if ($callCount === 1) {
                return $slotsDoctor1;
            } elseif ($callCount === 2) {
                throw new RuntimeException('Error 500 - Server Error');
            } elseif ($callCount === 3) {
                return $slotsDoctor3;
            }
        });

    $this->logger->expects($this->once())
        ->method('error')
        ->with($this->callback(function ($message){
            return str_contains($message, "with error") &&
                str_contains($message, 'could not be retrieved');
        }));

    $existingDoctor = new Doctor(1, 'Without Error');

    //For the doctors, we mock the responses of the find method. The first will return an already existing doctor, and the rest will be new doctors.

    $this->doctorRepository->method('find')
        ->willReturnMap([
            [1, $existingDoctor],
            [2, null],
            [3, null],
        ]);

    //For the slots, the slots for the first doctor will already be created. The first one will have an end time
    // different from the one registered in the database but will be recently created, so it should not be updated with
    // the API response. The second, however, will have been created more than 5 minutes ago,
    // and the end time should be updated.

    $existingSlotNoStale = new Slot($existingDoctor, DateTime::createFromFormat("Y-m-d H:i:s","2020-02-01 14:50:00"), DateTime::createFromFormat("Y-m-d H:i:s","2020-02-01 15:30:00"));
    $existingSlotStale = new Slot($existingDoctor, DateTime::createFromFormat("Y-m-d H:i:s","2020-02-01 15:30:00"), DateTime::createFromFormat("Y-m-d H:i:s","2020-02-01 16:50:00"));
    $existingSlotStale->setCreatedAt(new DateTime('6 minutes ago'));

    //We mock the responses for the findByDoctorAndStart call so that the first two return the slots we created,
    // and the other two will be new.
    $callCountSlotFind = 0;
    $this->slotRepository->method('findByDoctorAndStart')
        ->will($this->returnCallback(function ($slot) use (&$callCountSlotFind, $existingSlotNoStale, $existingSlotStale) {
            $callCountSlotFind++;
            if ($callCountSlotFind === 1) {
                return $existingSlotNoStale;
            }
            if ($callCountSlotFind === 2) {
                return $existingSlotStale;
            }
        }));

    // Now, we mock the save responses for the doctors. Although there are 3 doctors,
    // there should be 4 calls, since the second doctor will be called twice—once when created and again when we
    // receive the error for fetching the slots, marking it as an error.
    // When they are created, the error is always set to false, so the first time we save the second doctor,
    // the error is false. The second time we save this doctor, the error will be true.
    $callCountDoctorSave = 0;
    $this->doctorRepository->expects($this->exactly(4))
        ->method('save')
        ->will($this->returnCallback(function ($doctor) use (&$callCountDoctorSave) {
            $callCountDoctorSave++;
            if ($callCountDoctorSave === 1) {
                expect($doctor->getName())->toBe('Without Error');
                expect($doctor->hasError())->toBeFalse();
            }

            if ($callCountDoctorSave === 2) {
                expect($doctor->getName())->toBe('With Error');
                expect($doctor->hasError())->toBeFalse();
            }

            if ($callCountDoctorSave === 3) {
                expect($doctor->getName())->toBe('With Error');
                expect($doctor->hasError())->ToBeTrue();
            }

            if ($callCountDoctorSave === 4) {
                expect($doctor->getName())->toBe("Name O'Donel");
                expect($doctor->hasError())->toBeFalse();
            }

        }));

    //For the slots that were already created, the first one should remain as it is since it’s recently created,
    // and the second should have its end time updated.
    $callCountSlotSave = 0;
    $this->slotRepository->expects($this->exactly(4))
        ->method('save')
        ->will($this->returnCallback(function ($slot) use (&$callCountSlotSave) {
            $callCountSlotSave++;
            if ($callCountSlotSave === 1) {
                expect($slot->getEnd() == DateTime::createFromFormat("Y-m-d H:i:s","2020-02-01 15:30:00"))->toBeTrue();
            }

            if ($callCountSlotSave === 2) {
                expect($slot->getEnd() == DateTime::createFromFormat("Y-m-d H:i:s","2020-02-01 16:50:00"))->toBeFalse();
                expect($slot->getEnd() == DateTime::createFromFormat("Y-m-d H:i:s","2020-02-01 16:00:00"))->toBeTrue();
            }

        }));


    $syncService = new DoctorSlotSynchronizationService(
        $this->syncDoctorUseCase,
        $this->syncSlotUseCase,
        $this->markDoctorErrorUseCase,
        $this->slotProcessor,
        $this->doctorProvider,
        $this->slotProvider,
        $this->logger
    );

    $syncCommand = new SyncDoctorsCommand($syncService);
    $syncCommand->run();


    //Assertions are implicit in the mocks

});



