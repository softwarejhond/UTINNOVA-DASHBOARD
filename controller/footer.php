<footer class="text-center text-lg-start text-light fixed-bottom bg-indigo-dark text-white">
  <!-- Copyright -->
  <div class="text-center p-2">

    <?php
    $queryCompany = mysqli_query($conn, "SELECT nombre,nit FROM company");
    while ($empresaLog = mysqli_fetch_array($queryCompany)) {
      $empresa = $empresaLog['nombre'] . '</label>';
    }
    ?>
    <b>SYGNIA</b> &copy; Copyright <?php echo date("Y"); ?> Todos los derechos de uso para 
    <label class="text-lime-dark">
      <b><?php echo $empresa ?> </b>
      <a class="text-light" href="https://agenciaeaglesoftware.com/" target="_blank">Agencia de Desarrollo Eagle Software |</a>
      <span style="display: inline-flex; gap: 8px; vertical-align: middle;">
      <a href="https://www.linkedin.com/company/89372098/admin/feed/posts/" target="_blank" class="linkFooter">
        <i class="fa-brands fa-linkedin redes"></i>
      </a>
      <a href="https://www.instagram.com/eaglesoftwares/" target="_blank" class="linkFooter">
        <i class="fa-brands fa-instagram redes"></i>
      </a>
      <a href="https://www.facebook.com/eaglesoftwares/" target="_blank" class="linkFooter">
        <i class="fa-brands fa-facebook redes"></i>
      </a>
      </span>
    </label>

  </div>
  <!-- Copyright -->

</footer>