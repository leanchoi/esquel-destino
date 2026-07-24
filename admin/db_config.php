<?php
/**
 * Compatibilidad: la conexión y el esquema viven ahora en includes/db.php.
 * Este archivo se mantiene para no romper enlaces internos previos y expone
 * $db como antes.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$db = db();
