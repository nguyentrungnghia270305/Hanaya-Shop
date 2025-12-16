<?php
$baseDir = __DIR__ . '/../resources/lang';
$locales = array_filter(scandir($baseDir), function($d) use ($baseDir) { return $d !== '.' && $d !== '..' && is_dir($baseDir.'/'.$d); });
$files = [];
foreach ($locales as $loc) {
    $dir = $baseDir . '/' . $loc;
    foreach (scandir($dir) as $file) {
        if (substr($file, -4) === '.php') {
            $files[$file][] = $loc;
        }
    }
}
ksort($files);
$results = [];
foreach ($files as $file => $presentLocales) {
    $allKeys = [];
    $arrays = [];
    foreach ($locales as $loc) {
        $path = "$baseDir/$loc/$file";
        if (file_exists($path)) {
            $arr = include $path;
            $arrays[$loc] = $arr;
            $allKeys = array_unique(array_merge($allKeys, array_keys($arr)));
        } else {
            $arrays[$loc] = null;
        }
    }
    sort($allKeys);
    foreach ($locales as $loc) {
        $missing = [];
        if (is_array($arrays[$loc])) {
            $missing = array_values(array_diff($allKeys, array_keys($arrays[$loc])));
        } else {
            $missing = $allKeys; // whole file missing
        }
        $results[$file][$loc] = $missing;
    }
}
foreach ($results as $file => $byLocale) {
    $hasMissing = false;
    foreach ($byLocale as $loc => $miss) {
        if (count($miss) > 0) { $hasMissing = true; break; }
    }
    if ($hasMissing) {
        echo "File: $file\n";
        foreach ($byLocale as $loc => $miss) {
            echo "  $loc: ".(count($miss) ? implode(', ', $miss) : 'OK')."\n";
        }
        echo "\n";
    }
}
if (empty($results)) echo "No lang files found\n";
