<?php
$dirs = ['.'];
foreach ($dirs as $dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $f) {
        if ($f->isFile() && strpos($f->getPathname(), '.git') === false && strpos($f->getPathname(), 'node_modules') === false) {
            $content = @file_get_contents($f->getPathname());
            if ($content !== false && strpos($content, 'exceptionAsMarkdown') !== false) {
                echo $f->getPathname() . "\n";
            }
        }
    }
}
