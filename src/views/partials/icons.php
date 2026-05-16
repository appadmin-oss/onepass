<?php
/**
 * Inline SVG icon library — Phosphor-inspired, 24×24, 1.4 stroke.
 * Usage: <?= icon('strategy', 24) ?>
 */

function icon(string $name, int $size = 24, string $extraClass = ''): string {
    $stroke  = 'currentColor';
    $w       = (string)$size;
    $glyphs = [
        'strategy'   => '<circle cx="12" cy="12" r="9"/><path d="M15 9l-2 4-4 2 2-4z" stroke-linejoin="round"/>',
        'brief'      => '<path d="M3 7h18M3 12h18M3 17h12"/>',
        'deliver'    => '<rect x="3.5" y="3.5" width="17" height="17" rx="2"/><path d="M3.5 9h17M9 3.5v17"/>',
        'brand'      => '<path d="M12 3v18M3 12h18" stroke-linecap="round"/><circle cx="12" cy="12" r="3.5"/>',
        'founders'   => '<circle cx="12" cy="9" r="3.5"/><path d="M5 20c1-4 4-6 7-6s6 2 7 6"/>',
        'teams'      => '<circle cx="8" cy="9" r="3"/><circle cx="16" cy="9" r="3"/><path d="M2 20c0-3 2.5-5 6-5s6 2 6 5M10 20c0-3 2.5-5 6-5s6 2 6 5"/>',
        'media'      => '<rect x="3" y="6" width="18" height="13" rx="2"/><circle cx="12" cy="12.5" r="3.5"/><path d="M8 6l1.5-2h5L16 6"/>',
        'outcomes'   => '<path d="M4 18l5-5 3 3 7-7M14 9h5v5"/>',
        'editorial'  => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18M8 13h8M8 16h5"/>',
        'method'     => '<path d="M3 12h4l3-7 4 14 3-7h4"/>',
        'timeline'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'cert'       => '<path d="M12 2l2.5 6.5L21 10l-5.5 4 1.5 7-5-3.5L7 21l1.5-7L3 10l6.5-1.5z"/>',
        'arrow-out'  => '<path d="M5 17L17 5M8 5h9v9"/>',
        'arrow-r'    => '<path d="M2 7h10M8 3l4 4-4 4"/>',
        'globe'      => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
        'menu'       => '<path d="M3 7h18M3 12h18M3 17h18"/>',
        'close'      => '<path d="M5 5l14 14M5 19L19 5"/>',
        'check'      => '<path d="M4 12l5 5L20 6" stroke-linecap="round" stroke-linejoin="round"/>',
        'play'       => '<polygon points="6,4 20,12 6,20" fill="currentColor" stroke="none"/>',
        'spark'      => '<path d="M12 3v6M12 15v6M3 12h6M15 12h6"/>',
        'stack'      => '<path d="M12 3l9 5-9 5-9-5 9-5zM3 13l9 5 9-5M3 18l9 5 9-5"/>',
        'compass'    => '<circle cx="12" cy="12" r="9"/><path d="M16 8l-2 6-6 2 2-6z" stroke-linejoin="round"/>',
        'flag'       => '<path d="M5 21V4M5 4h14l-3 4 3 4H5"/>',
        'pulse'      => '<path d="M3 12h4l2-6 4 12 2-6h6"/>',
        'sparkle'    => '<path d="M12 2v4M12 18v4M2 12h4M18 12h4M5 5l3 3M16 16l3 3M5 19l3-3M16 8l3-3"/>',
        'mail'       => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 6l9 7 9-7"/>',
        'phone'      => '<path d="M5 4h4l2 5-2 1a11 11 0 005 5l1-2 5 2v4a2 2 0 01-2 2A18 18 0 013 6a2 2 0 012-2z"/>',
        'pin'        => '<path d="M12 22s7-7 7-12a7 7 0 10-14 0c0 5 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/>',
        'instagram'  => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/>',
        'linkedin'   => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 10v8M8 6v.01M12 18v-5a2 2 0 014 0v5"/>',
        'x'          => '<path d="M4 4l16 16M20 4L4 20"/>',
        'youtube'    => '<rect x="3" y="6" width="18" height="12" rx="3"/><path d="M11 9l4 3-4 3z" fill="currentColor" stroke="none"/>',
        'search'     => '<circle cx="11" cy="11" r="6"/><path d="M20 20l-4-4" stroke-linecap="round"/>',
        'user'       => '<circle cx="12" cy="9" r="3.5"/><path d="M5 20c1-4 4-6 7-6s6 2 7 6"/>',
    ];
    $body = $glyphs[$name] ?? '<rect x="3" y="3" width="18" height="18" rx="2"/>';
    $cls  = $extraClass ? ' class="' . htmlspecialchars($extraClass) . '"' : '';
    return "<svg{$cls} width=\"{$w}\" height=\"{$w}\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"{$stroke}\" stroke-width=\"1.4\" stroke-linecap=\"round\" aria-hidden=\"true\">{$body}</svg>";
}

/**
 * Brand mark — crimson circle with a stylised Africa silhouette
 * cut out in negative. Matches the official Afrostrength Limited logo.
 *
 * @param int    $size   pixel size (square)
 * @param string $tone   "light" (default — crimson disc on transparent) | "white" (mono white)
 */
function icon_logo(int $size = 28, string $tone = 'light'): string {
    // Backward-compat: legacy callers passed hex strings ('#FFFFFF' / '#0A0A0A').
    $toneLc = strtolower($tone);
    $isWhite = ($toneLc === 'white' || $toneLc === '#ffffff' || $toneLc === '#fff');
    $disc   = $isWhite ? '#FFFFFF' : '#C0392B';
    $cutout = $isWhite ? 'rgba(255,255,255,0.0)' : '#FFFFFF';
    // The cutout uses a mask so it stays crisp on any background. Africa shape
    // approximates the official mark — single closed path, no Madagascar (the
    // small mark on the real logo is preserved as a separate path).
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 64 64" aria-hidden="true">'
        . '<defs><mask id="afsMask"><rect width="64" height="64" fill="#fff"/>'
        . '<path d="M30 14 C 36 14 40 18 41 24 C 41.5 28 39 31 41 34 C 43 37 46 38 46 42 C 46 45 43 47 41 49 C 39 51 40 53 38 54 C 35 56 30 56 27 53 C 24 50 22 47 20 44 C 18 41 16 38 17 34 C 18 30 21 27 22 24 C 23 21 22 18 25 16 C 27 14.5 28 14 30 14 Z" fill="#000"/>'
        . '<path d="M44 45 q 1 4 -0.5 6 q -2 2 -2 -1 q 0 -3 2.5 -5 Z" fill="#000"/>'
        . '</mask></defs>'
        . '<circle cx="32" cy="32" r="30" fill="' . $disc . '" mask="url(#afsMask)"/>'
        . '</svg>';
}

function icon_chev(int $size = 14): string {
    return '<svg class="chev" width="' . $size . '" height="' . $size . '" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M2 7h10M8 3l4 4-4 4"/></svg>';
}
