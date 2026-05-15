<?php
// generate_files.php
// Escanea el plugin y genera .agents/skills/project-skill/files.json

$pluginRoot = dirname(__DIR__, 3); // plugin root (../../.. from project-skill)
$skillDir = __DIR__;
$outFile = $skillDir . DIRECTORY_SEPARATOR . 'files.json';
$backupFile = $skillDir . DIRECTORY_SEPARATOR . 'files.json.bak.' . date('YmdHis');

$targets = [
    ['path' => 'bendidoai-mockup-engine.php', 'desc' => 'Archivo principal del plugin (bootstrap)'],
    ['path' => 'assets/js', 'desc' => 'Frontend JS (módulos: mockup, modelos, core, enhance, remove-bg)'],
    ['path' => 'includes', 'desc' => 'Includes PHP: módulos, shortcodes y lógica del servidor'],
    ['path' => 'assets/images/rasgosAvatar', 'desc' => 'Thumbs usados por el wizard (rasgosAvatar)'],
    ['path' => 'services', 'desc' => 'Servicios externos (ej.: gemini)'],
    ['path' => 'assets/vendor', 'desc' => 'Librerías de terceros (choices.js, etc.)'],
];

$allowedExtensions = ['php','js','css','md','json','py','png','jpg','jpeg','svg'];
$entries = [];

foreach ($targets as $t) {
    $abs = $pluginRoot . DIRECTORY_SEPARATOR . $t['path'];
    if (is_file($abs)) {
        $entries[] = ['path' => str_replace('\\', '/', $t['path']), 'desc' => $t['desc']];
        continue;
    }
    if (is_dir($abs)) {
        // add directory entry
        $entries[] = ['path' => str_replace('\\', '/', rtrim($t['path'], '/\\') . '/'), 'desc' => $t['desc']];

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($abs));
        foreach ($iterator as $file) {
            if (!$file->isFile()) continue;
            $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions)) continue;
            $rel = substr($file->getPathname(), strlen($pluginRoot) + 1);
            $rel = str_replace('\\', '/', $rel);
            $entries[] = ['path' => $rel, 'desc' => ''];
        }
    }
}

// Deduplicate by path keeping first description
$seen = [];
$final = [];
foreach ($entries as $e) {
    if (isset($seen[$e['path']])) continue;
    $seen[$e['path']] = true;
    $final[] = $e;
}

// Backup old file if exists
if (file_exists($outFile)) {
    @copy($outFile, $backupFile);
}

file_put_contents($outFile, json_encode($final, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "Wrote " . $outFile . " (" . count($final) . " entries)\n";
if (file_exists($backupFile)) echo "Backup saved to: " . $backupFile . "\n";
echo "Run: php " . str_replace('\\', '/', __FILE__) . "\n";
