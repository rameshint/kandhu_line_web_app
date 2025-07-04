</div>
<!--end::Container-->
</div>
<!--end::App Content-->
</main>
<!--end::App Main-->
<!--begin::Footer-->
<footer class="app-footer">
  <!--begin::To the end-->
  <div class="float-end d-none d-sm-inline">Anything you want</div>
  <!--end::To the end-->
  <!--begin::Copyright-->
  <strong>
    Copyright &copy; 2014-2024&nbsp;

  </strong>
  All rights reserved.
  <!--end::Copyright-->
</footer>
<!--end::Footer-->
</div>
<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div
    id="toastSuccess"
    class="toast toast-success"
    role="alert"
    aria-live="assertive"
    aria-atomic="true">
    <div class="toast-header">
      <i class="bi bi-circle me-2"></i>
      <strong class="me-auto">Success</strong>
      <button
        type="button"
        class="btn-close"
        data-bs-dismiss="toast"
        aria-label="Close"></button>
    </div>
    <div class="toast-body">Bill Entered Successfully</div>
  </div>
</div>
<div id="loader" class="position-fixed top-0 start-0 w-100 h-100 bg-white d-flex justify-content-center align-items-center" style="z-index: 9999;">
  <div class="spinner-border text-secondary" role="status">
    <span class="visually-hidden">Loading...</span>
  </div>
</div>
<!--end::App Wrapper-->
<!--begin::Script-->
<!--begin::Third Party Plugin(OverlayScrollbars)-->
<script src="dist/js/jquery-3.7.1.min.js"></script>
<script src="dist/js/jquery.dataTables.min.js"></script>
<script src="dist/js/dataTables.fixedColumns.min.js"></script>
<script
  src="dist/js/overlayscrollbars.browser.es6.min.js"
  integrity="sha256-dghWARbRe2eLlIJ56wNB+b760ywulqK3DzZYEpsg2fQ="
  crossorigin="anonymous"></script>
<!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
<script
  src="dist/js/popper.min.js"
  integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
  crossorigin="anonymous"></script>
<!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
<script
  src="dist/js/bootstrap.min.js"
  integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
  crossorigin="anonymous"></script>
<!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
<script src="dist/js/adminlte.js"></script>
<!--end::Required Plugin(AdminLTE)--><!--begin::OverlayScrollbars Configure-->
<script>
  const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
  const Default = {
    scrollbarTheme: 'os-theme-light',
    scrollbarAutoHide: 'leave',
    scrollbarClickScroll: true,
  };
  document.addEventListener('DOMContentLoaded', function() {
    const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
    if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
      OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
        scrollbars: {
          theme: Default.scrollbarTheme,
          autoHide: Default.scrollbarAutoHide,
          clickScroll: Default.scrollbarClickScroll,
        },
      });
    }
  });
  window.addEventListener("load", function () {
    const loader = document.getElementById("loader");
    loader.classList.add("fade-out");
    setTimeout(() => {
      loader.style.display = "none";
    }, 500); // Match with transition duration
  });
</script>
<script>
  function printTable() {
    var printContents = document.getElementById('printSection').innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload(); // optional: refresh to reload event listeners/styles
  }

  function formatAmount(amount){
    const formattedAmount = new Intl.NumberFormat('en-IN', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(amount);
    return formattedAmount
  }
</script>
<!--end::OverlayScrollbars Configure-->
<!-- OPTIONAL SCRIPTS -->
<!-- sortablejs -->
<script
  src="dist/js/Sortable.min.js"
  integrity="sha256-ipiJrswvAR4VAx/th+6zWsdeYmVae0iJuiR+6OqHJHQ="
  crossorigin="anonymous"></script>
<!-- sortablejs -->
<script>
  const connectedSortables = document.querySelectorAll('.connectedSortable');
  connectedSortables.forEach((connectedSortable) => {
    let sortable = new Sortable(connectedSortable, {
      group: 'shared',
      handle: '.card-header',
    });
  });

  const cardHeaders = document.querySelectorAll('.connectedSortable .card-header');
  cardHeaders.forEach((cardHeader) => {
    cardHeader.style.cursor = 'move';
  });
</script>


<!-- jsvectormap -->
<script
  src="dist/js/jsvectormap.min.js"
  integrity="sha256-/t1nN2956BT869E6H4V1dnt0X5pAQHPytli+1nTZm2Y="
  crossorigin="anonymous"></script>

<script
  src="dist/js/world.js"
  integrity="sha256-XPpPaZlU8S/HWf7FZLAncLg2SAkP8ScUTII89x9D3lY="
  crossorigin="anonymous"></script>
<!-- jsvectormap -->

<!--end::Script-->
</body>
<!--end::Body-->

</html>