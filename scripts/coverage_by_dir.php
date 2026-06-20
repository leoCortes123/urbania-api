<?php

declare(strict_types=1);

$xml = simplexml_load_file('build/coverage/clover.xml');
if ($xml === false) {
    fwrite(STDERR, "Unable to load clover.xml\n");
    exit(1);
}

$totals = [];

foreach ($xml->project->package as $package) {
    foreach ($package->file as $file) {
        $path = (string) $file['name'];
        $metrics = $file->metrics[count($file->metrics) - 1];
        $statements = (int) $metrics['statements'];
        $covered = (int) $metrics['coveredstatements'];

        // Normalize path separators and strip project root
        $root = str_replace('\\', '/', (string) getcwd());
        $path = str_replace('\\', '/', $path);
        if (str_starts_with($path, $root.'/')) {
            $path = substr($path, strlen($root) + 1);
        }

        // Group by top-level directory under src/ or app/
        $dir = 'other';
        if (str_starts_with($path, 'src/')) {
            $parts = explode('/', substr($path, 4));
            $dir = 'src/'.implode('/', array_slice($parts, 0, 2));
        } elseif (str_starts_with($path, 'app/')) {
            $dir = 'app';
        }

        $totals[$dir]['statements'] = ($totals[$dir]['statements'] ?? 0) + $statements;
        $totals[$dir]['covered'] = ($totals[$dir]['covered'] ?? 0) + $covered;
    }
}

// Print per-directory
printf("%-55s %10s %10s %8s\n", 'Directory', 'Total', 'Covered', 'Percent');
foreach ($totals as $dir => $data) {
    $pct = $data['statements'] > 0 ? ($data['covered'] / $data['statements']) * 100 : 0;
    printf("%-55s %10d %10d %7.2f%%\n", $dir, $data['statements'], $data['covered'], $pct);
}

// Layer aggregates
$layers = [
    'Domain' => ['src/Auth/Domain', 'src/Shared/Domain'],
    'Application' => ['src/Auth/Application', 'src/Shared/Application'],
    'Infrastructure' => ['src/Auth/Infrastructure'],
    'Presentation' => ['src/Auth/Presentation'],
];

echo "\nLayer aggregates:\n";
printf("%-20s %10s %10s %8s\n", 'Layer', 'Total', 'Covered', 'Percent');
foreach ($layers as $layer => $prefixes) {
    $total = 0;
    $covered = 0;
    foreach ($totals as $dir => $data) {
        foreach ($prefixes as $prefix) {
            if (str_starts_with($dir, $prefix)) {
                $total += $data['statements'];
                $covered += $data['covered'];
            }
        }
    }
    $pct = $total > 0 ? ($covered / $total) * 100 : 0;
    printf("%-20s %10d %10d %7.2f%%\n", $layer, $total, $covered, $pct);
}
