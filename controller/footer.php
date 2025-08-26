<style>
@font-face {
    font-family: 'Sparose';
    src: url('css/fonts/fonnts.com-Sparose.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}
.eagle-link-footer {
    font-family: 'Sparose', sans-serif !important;
    font-size: 14px;
    color: #fff !important;
    text-decoration: none !important;
    font-weight: normal;
}
.footer-social-group {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
</style>

<footer class="text-center text-lg-start text-light fixed-bottom bg-indigo-dark text-white d-flex align-items-center justify-content-center" style="max-height:55px;">
    <!-- Copyright -->
    <div class="text-center pb-2 w-100">

        <?php
         $queryCompany = mysqli_query($conn, "SELECT nombre,nit FROM company");
         while ($empresaLog = mysqli_fetch_array($queryCompany)) {
           $empresa = $empresaLog['nombre'] . '</label>';
         }
        ?>
        <br>
        <b>SYGNIA</b> &copy; Copyright <?php echo date("Y"); ?> Todos los derechos de uso para <label class="text-lime-dark"><b><?php echo $empresa ?> </b></label>|
        <span class="footer-social-group">
            <a class="eagle-link-footer" href="https://agenciaeaglesoftware.com/" target="_blank">Eagle Software</a>
            <a href="https://www.linkedin.com/company/89372098/admin/feed/posts/" target="_blank" class="linkFooter"><i class="fa-brands fa-linkedin redes"></i></a>
            <a href="https://www.instagram.com/eaglesoftwares/" target="_blank" class="linkFooter"><i class="fa-brands fa-instagram redes"></i></a>
            <a href="https://www.facebook.com/eaglesoftwares/" target="_blank" class="linkFooter"><i class="fa-brands fa-facebook redes"></i></a>
        </span>

    </div>
    <!-- Copyright -->

</footer>