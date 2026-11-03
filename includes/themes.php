<?php defined('APP') or exit;

/* Themes the admin can choose on the Appearance page. Styles: assets/css/themes.css.
   preview = colours for the small mock-up: page, sidebar, card, primary, accent */
function ui_themes() {
    return array(
        'classic' => array('name' => 'Classic', 'desc' => 'Blue and teal with a dark navy sidebar. The original look.',
            'preview' => array('#f3f6fb', '#0b1324', '#ffffff', '#2563eb', '#0d9488')),
        'glassy'  => array('name' => 'Glassy', 'desc' => 'Frosted, see-through panels over a soft colourful background.',
            'preview' => array('linear-gradient(135deg,#c7d2fe,#fbcfe8 55%,#a5f3fc)', 'rgba(255,255,255,.45)', 'rgba(255,255,255,.65)', '#4f46e5', '#db2777')),
        'minimal' => array('name' => 'Minimalist', 'desc' => 'Black and white, flat, no shadows. Clean and calm.',
            'preview' => array('#fafafa', '#ffffff', '#ffffff', '#18181b', '#71717a')),
        'soft'    => array('name' => 'Soft UI', 'desc' => 'Raised, pillow-like panels with gentle shadows.',
            'preview' => array('#e6ebf2', '#e6ebf2', '#e6ebf2', '#2563eb', '#0d9488')),
        'emerald' => array('name' => 'Emerald', 'desc' => 'Medical green with a deep forest sidebar.',
            'preview' => array('#f1f7f4', '#04201a', '#ffffff', '#059669', '#0891b2')),
        'ocean'   => array('name' => 'Ocean', 'desc' => 'Sky blue and cyan with a deep sea sidebar.',
            'preview' => array('#f0f6fa', '#041b2b', '#ffffff', '#0284c7', '#06b6d4')),
        'sunset'  => array('name' => 'Sunset', 'desc' => 'Warm orange and rose.',
            'preview' => array('#faf5f2', '#22110a', '#ffffff', '#ea580c', '#e11d48')),
        'royal'   => array('name' => 'Royal', 'desc' => 'Purple and magenta.',
            'preview' => array('#f5f3fb', '#140b2a', '#ffffff', '#7c3aed', '#c026d3')),
        'blossom' => array('name' => 'Blossom', 'desc' => 'Pink and violet.',
            'preview' => array('#fbf3f7', '#22091a', '#ffffff', '#db2777', '#9333ea'))
    );
}

/* The theme the admin saved (Classic until one is chosen) */
function current_ui_theme($pdo) {
    static $theme = null;
    if ($theme === null) {
        $themes = ui_themes();
        $saved = meta_get($pdo, 'ui_theme');
        $theme = ($saved !== null && isset($themes[$saved])) ? $saved : 'classic';
    }
    return $theme;
}
