<?php
$rootDir = str_replace('\\', '/', __DIR__);

function scanDirRecursive($dir, $extensions) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isDir()) continue;
        $ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), $extensions)) {
            $files[] = $file->getPathname();
        }
    }
    return $files;
}

$frontendFiles = scanDirRecursive($rootDir . '/frontend', ['html', 'php']);
$backendFiles = scanDirRecursive($rootDir . '/backend', ['php']);
$allFiles = array_merge($frontendFiles, $backendFiles);

$brokenLinks = [];

// Get all valid backend routes by parsing Router.php (simplified, or just extracting all public methods from controllers)
$controllersDir = $rootDir . '/backend/controllers';
$validRoutes = [];
foreach (glob($controllersDir . '/*.php') as $controllerFile) {
    $controllerName = basename($controllerFile, '.php');
    // very basic route construction based on Router.php fallback
    $prefix = strtolower(str_replace('Controller', '', $controllerName));
    if ($prefix === 'home') $prefix = '/';
    
    // We will just do a loose check: if a route is like ?route=prefix/method or has a valid controller
    $validRoutes[] = $prefix; 
}
// Manually add known router map prefixes
$validRoutes = array_merge($validRoutes, ['admin', 'blog', 'api', 'auth', 'hospital_admin', 'doctor', 'patient', 'receptionist']);

echo "Scanning " . count($allFiles) . " files...\n\n";

foreach ($allFiles as $file) {
    $file = str_replace('\\', '/', $file);
    $content = file_get_contents($file);
    $relPath = str_replace($rootDir, '', $file);
    
    // Extract hrefs
    preg_match_all('/href=["\']([^"\']+)["\']/i', $content, $hrefMatch);
    // Extract srcs
    preg_match_all('/src=["\']([^"\']+)["\']/i', $content, $srcMatch);
    // Extract actions
    preg_match_all('/action=["\']([^"\']+)["\']/i', $content, $actionMatch);
    
    $links = array_merge($hrefMatch[1], $srcMatch[1], $actionMatch[1]);
    
    foreach ($links as $link) {
        // Ignore empty, anchors, js, tel, mailto, and external links
        if (empty($link) || strpos($link, '#') === 0 || strpos($link, 'javascript:') === 0 || strpos($link, 'tel:') === 0 || strpos($link, 'mailto:') === 0 || preg_match('/^https?:\/\//i', $link) || strpos($link, '<?php') !== false || strpos($link, '<?=') !== false || strpos($link, '$') !== false) {
            continue;
        }

        $isBroken = false;
        $reason = "";

        // Route checking
        if (strpos($link, '?route=') !== false) {
            preg_match('/route=([^\/&]+)/', $link, $routeMatch);
            if (!empty($routeMatch[1])) {
                $routePrefix = $routeMatch[1];
                if (!in_array($routePrefix, $validRoutes)) {
                    $isBroken = true;
                    $reason = "Unknown route prefix '$routePrefix'";
                }
            }
        } 
        // Local Asset/File checking
        else {
            // Strip query params and anchor hashes for file checking
            $filePath = explode('?', $link)[0];
            $filePath = explode('#', $filePath)[0];
            
            // Resolve path relative to the current file's directory
            $baseDir = dirname($file);
            $targetPath = realpath($baseDir . '/' . $filePath);
            
            // If it's a backend view template, resolve relative to the backend directory
            if ($targetPath === false && strpos($file, '/backend/views') !== false) {
                if (strpos($filePath, '../') !== 0 && strpos($filePath, '/') !== 0) {
                    $targetPath = realpath($rootDir . '/backend/' . $filePath);
                }
            }
            
            // If it can't resolve locally, maybe it's relative to domain root?
            // Since it's /FYP/, let's check
            if ($targetPath === false) {
                if (strpos($filePath, '../') === 0) {
                     $targetPath = realpath($baseDir . '/' . $filePath);
                } else if (strpos($filePath, '/') === 0) {
                     $targetPath = realpath($rootDir . $filePath);
                } else {
                     $targetPath = realpath($baseDir . '/' . $filePath);
                }
            }

            if ($targetPath === false || !file_exists($targetPath)) {
                $isBroken = true;
                $reason = "Local file not found ($filePath)";
            }
        }

        if ($isBroken) {
            $brokenLinks[] = [
                'file' => $relPath,
                'link' => $link,
                'reason' => $reason
            ];
        }
    }
}

if (empty($brokenLinks)) {
    echo "✅ No broken links or missing assets found!\n";
} else {
    echo "❌ Found " . count($brokenLinks) . " potentially broken links/assets:\n";
    foreach ($brokenLinks as $bl) {
        echo "- In {$bl['file']}:\n  Link: {$bl['link']}\n  Reason: {$bl['reason']}\n\n";
    }
}
?>
