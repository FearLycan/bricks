<?php

/**
 * Configuration for the Yii message extraction command.
 *
 * Run from project root:
 *     ./yii message frontend/config/i18n.php
 *
 * It scans the frontend tree for `T::tr()` / `Yii::t()` calls and updates
 * the per-language PHP message files under @frontend/messages/.
 */

return [
    'sourcePath'           => dirname(__DIR__),
    'messagePath'          => dirname(__DIR__) . '/messages',
    'languages'            => ['en', 'pl', 'de', 'fr', 'es', 'it', 'ja', 'zh'],
    'translator'           => ['T::tr', 'Yii::t', '\Yii::t'],
    'sort'                 => true,
    'removeUnused'         => false,
    'markUnused'           => true,
    'only'                 => ['*.php'],
    'except'               => [
        '.svn',
        '.git',
        '.gitignore',
        '.gitkeep',
        '.hgignore',
        '.hgkeep',
        '/messages',
        '/tests',
        '/runtime',
        '/web/assets',
        '/vendor',
    ],
    'format'               => 'php',
    'overwrite'            => true,
    'phpFileHeader'        => '',
    'phpDocBlock'          => null,
];
