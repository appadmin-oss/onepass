<?php

class View {
    /**
     * Render a page inside a layout.
     *
     *   View::render('pages/home', $data, 'main')
     *
     * The page file echoes its body and may set $title, $meta, $jsonld, $bodyClass,
     * $needsSvgJs, $needsGraphicsJs by writing to the captured $data array.
     */
    public static function render(string $page, array $data = [], string $layout = 'main'): void {
        $pagePath   = AFS_ROOT . '/src/views/' . $page . '.php';
        $layoutPath = AFS_ROOT . '/src/views/layouts/' . $layout . '.php';
        if (!is_file($pagePath))   { http_response_code(404); $pagePath = AFS_ROOT . '/src/views/pages/404.php'; }
        if (!is_file($layoutPath)) { throw new RuntimeException("Missing layout: {$layout}"); }

        $defaults = [
            'title'           => AFS_NAME . ' — ' . AFS_TAGLINE,
            'description'     => AFS_DESC,
            'bodyClass'       => '',
            'needsSvgJs'      => false,
            'needsGraphicsJs' => false,
            'canonical'       => url(current_path()),
            'jsonld'          => [],
            'breadcrumbs'     => [],
        ];
        $data = array_merge($defaults, $data);

        // Capture page body
        ob_start();
        extract($data, EXTR_SKIP);
        require $pagePath;
        $bodyContent = ob_get_clean();

        // Render layout
        extract($data, EXTR_SKIP);
        require $layoutPath;
    }
}
