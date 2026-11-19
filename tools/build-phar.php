<?php
/*
 * Package the extracted WolfCore-MP Legacy runtime with its bundled, patched dependencies.
 * Usage: php -d phar.readonly=0 tools/build-phar.php
 * Requires the PHP Phar extension; no Composer download is performed.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$meta = json_decode((string) file_get_contents($root . '/build/package-metadata.json'), true, 512, JSON_THROW_ON_ERROR);
$dist = $root . '/dist';
if(!is_dir($dist) && !mkdir($dist, 0775, true) && !is_dir($dist)){
    throw new RuntimeException('Cannot create dist directory');
}
$output = $dist . '/WolfCore-MP-Legacy.phar';
if(is_file($output) && !unlink($output)){
    throw new RuntimeException('Cannot replace existing PHAR');
}
$phar = new Phar($output, 0, (string) $meta['alias']);
$phar->startBuffering();
foreach(['src', 'vendor', 'resources'] as $directory){
    $base = $root . '/' . $directory;
    if(!is_dir($base)) throw new RuntimeException('Missing required directory: ' . $directory);
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
    foreach($files as $file){
        if(!$file->isFile()) continue;
        $relative = substr($file->getPathname(), strlen($root) + 1);
        $phar->addFile($file->getPathname(), str_replace('\\', '/', $relative));
    }
}
$phar->setMetadata(['git' => (string) $meta['git'], 'build' => (int) $meta['build'], 'owner' => (string) $meta['owner']]);
$phar->setStub(<<<'STUB'
<?php
$tmpDir = sys_get_temp_dir();
if(!is_readable($tmpDir) || !is_writable($tmpDir)){
    fwrite(STDERR, "ERROR: temp directory is inaccessible: $tmpDir" . PHP_EOL);
    exit(1);
}
require('phar://' . __FILE__ . '/src/WolfCore.php');
__HALT_COMPILER(); ?>
STUB
);
$phar->setSignatureAlgorithm(Phar::SHA256);
$phar->stopBuffering();
echo "Created $output with " . count($phar) . " files" . PHP_EOL;
