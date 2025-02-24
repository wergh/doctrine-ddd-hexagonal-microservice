<?php

// Simulación de la versión original con yield
function originalVersion($numberOfSlots) {
    $slots = array_fill(0, $numberOfSlots, ['start' => '2023-01-01 00:00:00', 'end' => '2023-01-01 01:00:00']);

    foreach (parseSlots($slots) as $slot) {
        // Simular el guardado del slot
        $slot['saved'] = true;
    }
    return $slots; // Retornar para evitar optimizaciones agresivas
}

function parseSlots($slots) {
    foreach ($slots as $slot) {
        yield $slot;
    }
}

// Simulación de la versión refactorizada
function refactoredVersion($numberOfSlots) {
    $slots = array_fill(0, $numberOfSlots, ['start' => '2023-01-01 00:00:00', 'end' => '2023-01-01 01:00:00']);

    foreach ($slots as &$slot) {
        // Simular el guardado del slot
        $slot['saved'] = true;
    }
    return $slots; // Retornar para evitar optimizaciones agresivas
}

// Función para medir el uso de memoria
function measureMemoryUsage($function, $numberOfSlots) {
    gc_collect_cycles();
    gc_disable();

    $startMemory = memory_get_peak_usage(true);
    $result = $function($numberOfSlots);
    $endMemory = memory_get_peak_usage(true);

    gc_enable();
    gc_collect_cycles();

    return [$endMemory - $startMemory, $result];
}

// Número de slots a probar
$numberOfSlots = 100000;

// Medir el uso de memoria para ambas versiones
[$originalMemoryUsage, $originalResult] = measureMemoryUsage('originalVersion', $numberOfSlots);
[$refactoredMemoryUsage, $refactoredResult] = measureMemoryUsage('refactoredVersion', $numberOfSlots);

echo "Uso de memoria (versión original): " . number_format($originalMemoryUsage) . " bytes\n";
echo "Uso de memoria (versión refactorizada): " . number_format($refactoredMemoryUsage) . " bytes\n";
echo "Diferencia: " . number_format($refactoredMemoryUsage - $originalMemoryUsage) . " bytes\n";

// Verificar que ambas versiones produjeron el mismo resultado
echo "Resultados iguales: " . (($originalResult === $refactoredResult) ? "Sí" : "No") . "\n";
