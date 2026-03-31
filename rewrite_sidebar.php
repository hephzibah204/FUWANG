<?php
$content = file_get_contents("resources/views/layouts/nexus.blade.php");

// Replace aside tag
$content = str_replace('<aside class="sidebar" id="sidebar">', '<aside class="sidebar" id="sidebar" aria-label="Main Navigation" role="navigation">', $content);

// Function to convert submenus
$content = preg_replace_callback('/<a href="javascript:void\(0\)" class="submenu-toggle">(.*?)<i class="fa-solid fa-chevron-down ml-auto small"><\/i><\/a>/', function($matches) {
    $inner = trim($matches[1]);
    // find icon
    if (preg_match('/^(<i class="[^"]+"><\/i>)\s*(.*?)$/', $inner, $m)) {
        return '<button type="button" class="submenu-toggle" aria-expanded="false">' . $m[1] . ' <span class="nav-text">' . $m[2] . '</span> <i class="fa-solid fa-chevron-down ml-auto small submenu-arrow"></i></button>';
    }
    return $matches[0];
}, $content);

// Function to convert simple nav-item links (with icon and text)
$content = preg_replace_callback('/<div class="nav-item(.*?)">\s*<a href="([^"]+)">(.*?)<\/a>\s*<\/div>/s', function($matches) {
    $class = $matches[1];
    $href = $matches[2];
    $inner = trim($matches[3]);
    if (strpos($inner, 'nav-text') !== false) return $matches[0]; // already processed
    
    // Look for icon and text, optionally badge
    if (preg_match('/^(<i class="[^"]+"><\/i>)\s*(.*?)$/s', $inner, $m)) {
        $icon = $m[1];
        $rest = $m[2];
        return '<div class="nav-item' . $class . '">
                <a href="' . $href . '">' . $icon . ' <span class="nav-text">' . $rest . '</span></a>
            </div>';
    }
    return $matches[0];
}, $content);

// Change `<div class="submenu">` to `<div class="submenu" role="region">`
$content = str_replace('<div class="submenu">', '<div class="submenu" role="region">', $content);

// Add the collapse button
$btn = '
            <div class="nav-item mt-auto pt-3" style="border-top: 1px solid rgba(255,255,255,0.05);">
                <button type="button" id="minimizeSidebarBtn" aria-label="Minimize Sidebar">
                    <i class="fa-solid fa-angle-left collapse-icon" style="transition: transform 0.3s;"></i>
                    <span class="nav-text">Collapse Menu</span>
                </button>
            </div>
';
if (strpos($content, 'minimizeSidebarBtn') === false) {
    $content = str_replace('</nav>', $btn . '        </nav>', $content);
}

file_put_contents("resources/views/layouts/nexus.blade.php", $content);
echo "Done\n";
