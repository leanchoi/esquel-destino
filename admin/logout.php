<?php
// Creado para el Laboratorio de Destino Esquel
// admin/logout.php

session_start();
session_unset();
session_destroy();

header("Location: login.php");
exit();
?>
