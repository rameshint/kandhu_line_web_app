<?php
$title = "Dashboard";
include_once 'header.php';
?>
<!--begin::Row-->
<div class="row">
  <!--begin::Col-->
  <div class="col-lg-3 col-6">
    <!--begin::Small Box Widget 1-->
    <div class="small-box text-bg-primary" id="closing_balance">
      <div class="inner">
        <h3>0.00</h3>
        <p>Closing Balance</p>
      </div>

      <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="currentColor" class="small-box-icon" viewBox="0 0 16 16">
        <path d="M0 3a2 2 0 0 1 2-2h13.5a.5.5 0 0 1 0 1H15v2a1 1 0 0 1 1 1v8.5a1.5 1.5 0 0 1-1.5 1.5h-12A2.5 2.5 0 0 1 0 12.5zm1 1.732V12.5A1.5 1.5 0 0 0 2.5 14h12a.5.5 0 0 0 .5-.5V5H2a2 2 0 0 1-1-.268M1 3a1 1 0 0 0 1 1h12V2H2a1 1 0 0 0-1 1"></path>
      </svg>
      <a
        href="day_closure_report.php"
        class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
        More info <i class="bi bi-link-45deg"></i>
      </a>
    </div>
    <!--end::Small Box Widget 1-->
  </div>
  <!--end::Col-->
  <div class="col-lg-3 col-6">
    <!--begin::Small Box Widget 2-->
    <div class="small-box text-bg-success" id="total_investments">
      <div class="inner">
        <h3>0.00</h3>
        <p>Total Investments</p>
      </div>

      <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="currentColor" class="small-box-icon bi bi-graph-up-arrow" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M0 0h1v15h15v1H0zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5" />
      </svg>
      <a
        href="investments.php"
        class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
        More info <i class="bi bi-link-45deg"></i>
      </a>
    </div>
 
    <!--end::Small Box Widget 2-->
  </div>
  <!--end::Col-->
  <div class="col-lg-3 col-6">
    <!--begin::Small Box Widget 3-->
    <div class="small-box text-bg-warning" id="loan_outstanding">
      <div class="inner">
        <h3>0.00</h3>
        <p>Loans Outstanding</p>
      </div>
      <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="currentColor" class="small-box-icon bi bi-graph-up-arrow" viewBox="0 0 16 16">
        <path d="M8.235 1.559a.5.5 0 0 0-.47 0l-7.5 4a.5.5 0 0 0 0 .882L3.188 8 .264 9.559a.5.5 0 0 0 0 .882l7.5 4a.5.5 0 0 0 .47 0l7.5-4a.5.5 0 0 0 0-.882L12.813 8l2.922-1.559a.5.5 0 0 0 0-.882zM8 9.433 1.562 6 8 2.567 14.438 6z" />
      </svg>
      <a
        href="customer_loans_report.php"
        class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
        More info <i class="bi bi-link-45deg"></i>
      </a>
    </div>
    <!--end::Small Box Widget 3-->
  </div>
  <!--end::Col-->
  <div class="col-lg-3 col-6">
    <!--begin::Small Box Widget 4-->
    <div class="small-box text-bg-danger" id="temporary_loan_outstanding">
      <div class="inner">
        <h3>0.00</h3>
        <p>Temporary Loans Outstanding</p>
      </div>
      <svg
        class="small-box-icon"
        fill="currentColor"
        viewBox="0 0 24 24"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true">
        <path
          clip-rule="evenodd"
          fill-rule="evenodd"
          d="M2.25 13.5a8.25 8.25 0 018.25-8.25.75.75 0 01.75.75v6.75H18a.75.75 0 01.75.75 8.25 8.25 0 01-16.5 0z"></path>
        <path
          clip-rule="evenodd"
          fill-rule="evenodd"
          d="M12.75 3a.75.75 0 01.75-.75 8.25 8.25 0 018.25 8.25.75.75 0 01-.75.75h-7.5a.75.75 0 01-.75-.75V3z"></path>
      </svg>
      <a
        href="temporary_loans.php"
        class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
        More info <i class="bi bi-link-45deg"></i>
      </a>
    </div>
    <!--end::Small Box Widget 4-->
  </div>
  <!--end::Col-->
</div>
<!--end::Row-->
<div class="row">
  <!-- Start col -->
  <div class="col-lg-6 connectedSortable">
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Loans given vs EMI collected (per month)</h3>
      </div>
      <div class="card-body">
        <div id="loan-collection-chart"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Loans given vs EMI collected (past 7 days)</h3>
      </div>

      <div class="card-body">
        <!--
        <div class="d-flex">
          <p class="d-flex flex-column">
            <span class="fw-bold fs-5">$18,230.00</span> <span>Sales Over Time</span>
          </p>
          <p class="ms-auto d-flex flex-column text-end">
            <span class="text-success"> <i class="bi bi-arrow-up"></i> 33.1% </span>
            <span class="text-secondary">Since Past Year</span>
          </p>
        </div> -->
        <!-- /.d-flex -->

        <div id="last-week-loans-collections-chart"></div>


      </div>
    </div>
    <!-- /.card -->

  </div>
  <div class="col-lg-6">
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Collections (Last 35 days)</h3>
      </div>

      <div class="card-body">


        <div id="collections-heat-chart"></div>


      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">Loans</h3>
      </div>

      <div class="card-body">
        <div class="table-responsive" style="max-height: 314px;min-height: 314px; overflow-y: auto;">
          <table class="table table-sm">
            <thead>
              <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Name</th>
                <th>Type</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Pending EMI</th>
                <th>Balance</th>
                <th style="width: 5%;">Status</th>
              </tr>
            </thead>
            <tbody id="loansBody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>

<?php
include_once 'footer.php';
?>
<script
  src="dist/js/apexcharts.min.js"
  integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8="
  crossorigin="anonymous"></script>
<script>
  $(document).ready(function() {
    $.get('get_dashboard_data.php', function(data) {
      const dashboardData = JSON.parse(data);
      const tiles = dashboardData.tiles;
      const loan_collections = dashboardData.loan_collections;
      const loan_collections_last_week = dashboardData.loan_collections_days;
      const heat_map_collections = dashboardData.heat_map;
      const loans = dashboardData.loans;
      tiles.forEach(tile => {

        $("#" + tile.head).find('h3').text(formatAmount(tile.net_amount))

      });
      loadLoanCollectionsChart(loan_collections);
      LastWeekLoanCollections(loan_collections_last_week);
      heatMap(heat_map_collections);
      loansTable(loans);
    });




  });


  function loansTable(loans) {
    rows = ''

    loans.forEach(loan => {
      status = ''
      if (loan.status == 'Overdue') {
        status = '<span class="badge text-bg-danger">Overdue</span>'
      } else {
        status = '<span class="badge text-bg-warning">LatePay</span>'
      }

      rows += `<tr>
          <td>${loan.customer_no}</td>
          <td>${loan.name}</td>
          <td>${loan.loan_type}</td>
          <td>${loan.loan_date}</td>
          <td align=right>${formatAmount(loan.amount)}</td>
          <td align=center>${loan.pending_emi}</td>
          <td align=right>${formatAmount(loan.to_be_paid - loan.collected)}</td>
          <td>${status}</td>
      </tr>`
    });

    $("#loansBody").html(rows)
  }

  function heatMap(heat_map_collections) {
    var options = {
      series: heat_map_collections,
      chart: {
        height: 300,
        type: 'heatmap',
      },
      dataLabels: {
        enabled: false
      },
      colors: ["#008FFB"],

    };

    var chart = new ApexCharts(document.querySelector("#collections-heat-chart"), options);
    chart.render();
  }

  function loadLoanCollectionsChart(loan_collections) {
    const loans_collections_chart_options = {
      series: [{
          name: 'Loans',
          data: loan_collections.loans,
        },
        {
          name: 'Collections',
          data: loan_collections.collections,
        },
      ],
      chart: {
        height: 300,
        type: 'area',
        toolbar: {
          show: false,
        },
      },
      legend: {
        show: false,
      },
      colors: ['#0d6efd', '#20c997'],
      dataLabels: {
        enabled: false,
      },
      stroke: {
        curve: 'smooth',
      },
      xaxis: {
        type: 'datetime',
        categories: loan_collections.months,
      },
      tooltip: {
        x: {
          format: 'MMMM yyyy',
        },
      },
    };

    const sales_chart = new ApexCharts(
      document.querySelector('#loan-collection-chart'),
      loans_collections_chart_options,
    );
    sales_chart.render();
  }


  function LastWeekLoanCollections(loan_collections_last_week) {
    const last_week_loan_chart_options = {
      series: [{
          name: 'Loans',
          data: loan_collections_last_week.loans,
        },
        {
          name: 'Collections',
          data: loan_collections_last_week.collections,
        },
      ],
      chart: {
        type: 'bar',
        height: 300,
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '55%',
          endingShape: 'rounded',
        },
      },
      legend: {
        show: false,
      },
      colors: ['#0d6efd', '#20c997'],
      dataLabels: {
        enabled: false,
      },
      stroke: {
        show: true,
        width: 2,
        colors: ['transparent'],
      },
      xaxis: {
        categories: loan_collections_last_week.dates,
      },
      fill: {
        opacity: 1,
      },
      tooltip: {
        y: {
          formatter: function(val) {
            return formatAmount(val);
          },
        },
      },
    };

    const sales_chart = new ApexCharts(
      document.querySelector('#last-week-loans-collections-chart'),
      last_week_loan_chart_options,
    );
    sales_chart.render();
  }
</script>