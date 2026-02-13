<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | Set some default values. It is possible to add all defines that can be set
    | in dompdf_config.inc.php. You can also override the entire config file.
    |
    */
    'show_warnings' => false,
    'orientation' => 'portrait',
    'defines' => [
        /**
         * The location of the DOMPDF font directory
         */
        'DOMPDF_FONT_DIR' => storage_path('fonts/'),

        /**
         * The location of the DOMPDF font cache directory
         */
        'DOMPDF_FONT_CACHE' => storage_path('fonts/'),

        /**
         * The location of a temporary directory.
         */
        'DOMPDF_TEMP_DIR' => storage_path('tmp/'),

        /**
         * The location of the DOMPDF font directory
         */
        'DOMPDF_CHROOT' => realpath(base_path()),

        /**
         * The location of the DOMPDF font directory
         */
        'DOMPDF_UNICODE_ENABLED' => true,

        /**
         * The location of the DOMPDF font directory
         */
        'DOMPDF_ENABLE_FONTSUBSETTING' => false,

        /**
         * The location of the DOMPDF font directory
         */
        'DOMPDF_PDF_BACKEND' => 'CPDF',

        /**
         * The location of the DOMPDF font directory
         */
        'DOMPDF_DEFAULT_MEDIA_TYPE' => 'screen',

        /**
         * The location of the DOMPDF font directory
         */
        'DOMPDF_DEFAULT_PAPER_SIZE' => 'A4',

        /**
         * The location of the DOMPDF font directory
         */
        'DOMPDF_DEFAULT_FONT' => 'sans-serif',

        /**
         * The location of the DOMPDF font directory
         */
        'DOMPDF_DPI' => 96,

        /**
         * The location of the DOMPDF font directory
         */
        'DOMPDF_ENABLE_PHP' => false,

        /**
         * The location of the DOMPDF font directory
         */
        'DOMPDF_ENABLE_REMOTE' => true,

        /**
         * The location of the DOMPDF font directory
         */
        'DOMPDF_ENABLE_CSS_FLOAT' => false,

        /**
         * The location of the DOMPDF font directory
         */
        'DOMPDF_ENABLE_JAVASCRIPT' => false,

        /**
         * The location of the DOMPDF font directory
         */
        'DOMPDF_LOG_OUTPUT_FILE' => storage_path('logs/dompdf.html'),
    ],
];
