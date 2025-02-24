<?php

use App\Domain\Entity\Doctor;
use App\Domain\Repository\DoctorRepository;
use Doctrine\ORM\EntityManager;

beforeEach(function () {

    //First, we mock the repositories and the providers.

    $this->entityManager = $this->createMock(EntityManager::class);
    $this->doctorRepository = $this->createMock(DoctorRepository::class);

    $this->entityManager->method('getRepository')
        ->with(Doctor::class)
        ->willReturn($this->doctorRepository);


});

it('Testing doctors name format', function() {

    $doctor = new Doctor('1', 'name lowercase');
    $this->doctorRepository->save($doctor);
    expect($doctor->getName() === "Name Lowercase")->toBeTrue();
    $doctor = new Doctor('2', 'name Uppercase');
    $this->doctorRepository->save($doctor);
    expect($doctor->getName() === "Name Uppercase")->toBeTrue();
    $doctor = new Doctor('3', "name o'cornell");
    $this->doctorRepository->save($doctor);
    expect($doctor->getName() === "Name O'Cornell")->toBeTrue();
    $doctor = new Doctor('4', 'JOHN SMITH');
    $this->doctorRepository->save($doctor);
    expect($doctor->getName() === "John Smith")->toBeTrue();
});
