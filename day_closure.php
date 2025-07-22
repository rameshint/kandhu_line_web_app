<?php
$title = 'Day Closure';
include 'header.php';
?>
<div class="row">
    <div class="col-4">
        <!-- The icons -->

        <div class="card card-outline">
            <div class="card-header">
                <h3 class="card-title">Summary</h3>
            </div>
            <div class="card-body">
                <table class="table  table-sm" id="summary_table">
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-8">
        <!-- The icons -->

        <div class="card card-outline">
            <div class="card-header">
                <h3 class="card-title">Break Down</h3>
            </div>
            <div class="card-body">
                <h4>Loans</h4>
                <table class="table table-sm" id="loans_table">
                    <thead>
                        <tr>
                            <th>Customer No</th>
                            <th>Customer</th>
                            <th>Agent</th>
                            <th>Loan Amount</th>
                            <th>Interest</th>
                            <th>File Charge</th>
                            <th>Type of Loan</th>
                            <th>Tenure</th>
                            <th>Issued On</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <br />
                <h4>Collections</h4>
                <table class="table table-sm" id="collections_table">
                    <thead>
                        <tr>
                            <th>Customer No</th>
                            <th>Customer</th>
                            <th>Agent</th>
                            <th>Type of Loan</th>
                            <th>Loan Amount</th>
                            <th>Head</th>
                            <th>Collection Amount</th>
                            <th>Collected On</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                </script>
            </div>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>
<script>
    $(document).ready(function() {


        $.get('get_summary_data.php', function(data) {
            const rows = JSON.parse(data);
            const loans = rows.loans;
            const collections = rows.collections;
            const summary = rows.summary;
            let html = '';
            loans.forEach(row => {
                html += `
                <tr>
                    <td>${row.customer_no}</td>
                    <td>${row.name}</td>
                    <td>${row.agent}</td>
                    <td>${formatAmount(row.amount)}</td>
                    <td>${formatAmount(row.interest)}</td>
                    <td>${formatAmount(row.file_charge)}</td>
                    <td>${row.loan_type}</td>
                    <td>${row.tenure}</td>
                    <td>${formatDate(row.loan_date)}</td>
                </tr>`;
            });
            $('#loans_table tbody').html(html);

            html = '';
            collections.forEach(row => {
                html += `
                <tr>
                    <td>${row.customer_no}</td>
                    <td>${row.name}</td>
                    <td>${row.agent}</td>
                    <td>${row.loan_type}</td>
                    <td>${formatAmount(row.amount)}</td>
                    <td>${row.head}</td>
                    <td>${formatAmount(row.collection_amount)}</td>
                    <td>${formatDate(row.collection_date)}</td>
                </tr>`;
            });
            $('#collections_table tbody').html(html);

            $('#summary_table tbody').html(summary);
        });
    });
</script>