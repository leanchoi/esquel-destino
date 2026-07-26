</main>

<footer class="site-footer">
  <div class="container">
    <div class="footer-top">
      <div class="footer-brand">
        <img src="<?= $base ?>assets/images/logo-esquel-lab-horizontal.png" alt="Esquel LAB" class="footer-logo">
        <p>Programa municipal gratuito de desarrollo de experiencias turísticas. Subsecretaría de Turismo y Subsecretaría de Producción de Esquel.</p>
        <img src="<?= $base ?>assets/images/logo-municipio-esquel.png" alt="Municipio de Esquel" class="footer-muni">
      </div>
      <div class="footer-col">
        <h3>El programa</h3>
        <a href="<?= $base ?>index.php#para-vos">¿Es para vos?</a>
        <a href="<?= $base ?>index.php#lineas">Acelera y Raíz</a>
        <a href="<?= $base ?>index.php#como-es">Cómo es el trabajo</a>
        <a href="<?= $base ?>index.php#preguntas">Preguntas frecuentes</a>
      </div>
      <div class="footer-col">
        <h3>Postulación</h3>
        <a href="<?= $base ?>inscribirse.php">Formulario</a>
        <a href="<?= $base ?>index.php#el-camino">Fechas y cupos</a>
        <a href="<?= $base ?>media-kit.php">Pasá la voz y prensa</a>
      </div>
      <div class="footer-col">
        <h3>Contacto</h3>
        <a href="mailto:<?= e(EMAIL_PROGRAMA) ?>"><?= e(EMAIL_PROGRAMA) ?></a>
        <span class="footer-plain">Esquel, Chubut<br>Patagonia Argentina</span>
      </div>
    </div>

    <div class="footer-bottom">
      <p>
        &copy; 2026 Laboratorio de Destino Esquel · Municipalidad de Esquel
        <span class="footer-sep">·</span>
        <a href="<?= $base ?>terminos.php" class="footer-legal">Términos y condiciones</a>
      </p>
      <a href="<?= $base ?>admin/login.php" class="footer-lock" aria-label="Acceso del equipo" title="Acceso del equipo">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="5" y="11" width="14" height="10" rx="2"></rect><path d="M8 11V7a4 4 0 0 1 8 0v4"></path></svg>
      </a>
    </div>
  </div>
</footer>

<?php if (!empty($visita)): ?>
<script id="datosVisita" type="application/json"><?= json_encode(['id' => $visita[0], 't' => $visita[1], 'url' => $base . 'track.php']) ?></script>
<?php endif; ?>
<script src="<?= $base ?><?= asset('assets/js/main.js') ?>"></script>
</body>
</html>
