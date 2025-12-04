<?php

/**
 * Système d'autoload.
 * A chaque fois que PHP va avoir besoin d'une classe, il va appeler cette fonction
 * et chercher dnas les divers dossiers (ici models, controllers, views, services) s'il trouve
 * un fichier avec le bon nom. Si c'est le cas, il l'inclut avec require_once.
 */

spl_autoload_register(function ($className) {
    $className = str_replace('\\', '/', $className);

    $baseDirs = [
        'Controllers' => 'controllers/',
        'Models'      => 'models/',
        'Views'       => 'views/',
        'Services'    => 'services/',
        'Config'      => 'config/'
    ];

    foreach ($baseDirs as $namespace => $dir) {
        if (str_starts_with($className, $namespace . '/')) {
            $file = $dir . str_replace($namespace . '/', '', $className) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});
