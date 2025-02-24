<?php 

error_reporting(E_ALL);

if (!isset($_SERVER['argv']) && !isset($argv)) {
    echo "Please enable the register_argc_argv directive in your php.ini\n";
    exit(1);
} elseif (!isset($argv)) {
    $argv = $_SERVER['argv'];
}

$buildDir = getcwd() . '/build';
$srcDir   = getcwd() . '/src';
$moduleData = [];

// Get all subdirectories in src
$directories = array_filter(glob($srcDir . '/*'), 'is_dir');
foreach ($directories as $dir) {
    $target = basename($dir);
    echo "======== Update Package: {$target} ========\n";
    
    $archiveFile    = "{$buildDir}/{$target}.tar.gz";
    $moduleInfoFile = "{$srcDir}/{$target}/module.info";
    
    // Verify module.info exists
    if (!file_exists($moduleInfoFile)) {
        echo "module.info not found for {$target}\n";
        continue;
    }
    
    // Verify archive file exists
    if (!file_exists($archiveFile)) {
        echo "Archive not found for {$target}\n";
        continue;
    }
    
    $infoData = json_decode(file_get_contents($moduleInfoFile));
    
    // Build module array
    $module = [
        'name'        => $target,
        'title'       => $infoData->title,
        'version'     => $infoData->version,
        'description' => $infoData->description,
        'author'      => $infoData->author,
        'size'        => filesize($archiveFile),
        'checksum'    => hash_file('sha256', $archiveFile),
        'num_downloads' => '0',
    ];
    
    if (isset($infoData->system)) {
        $module['type'] = 'System';
    } elseif (isset($infoData->cliOnly)) {
        $module['type'] = 'CLI';
    } else {
        $module['type'] = 'GUI';
    }
    
    $moduleData[$target] = $module;
}

// Sort modules and update JSON file
asort($moduleData);
@unlink("{$buildDir}/modules.json");
file_put_contents("{$buildDir}/modules.json", json_encode($moduleData));

echo "\nComplete!\n";
