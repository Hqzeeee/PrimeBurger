<?php
/**
 * Bootstrap
 * Included at the top of every public-facing PHP page. Loads config,
 * core classes, models, and starts the session.
 */

require_once __DIR__ . '/../config/config.php';

// ---- Core -----------------------------------------------------------
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Model.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/Csrf.php';
require_once __DIR__ . '/core/Validator.php';
require_once __DIR__ . '/core/Flash.php';

// ---- Models -----------------------------------------------------------
foreach (glob(__DIR__ . '/models/*.php') as $modelFile) {
    require_once $modelFile;
}

// ---- Services -----------------------------------------------------------
require_once __DIR__ . '/services/ActivityLogger.php';
require_once __DIR__ . '/services/NotificationService.php';

Auth::start();
