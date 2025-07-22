<?php
$title = 'Report - Day Closure';
include('header.php');
?>
<div class="row">
    <div class="col-12">
        <!-- The icons -->

        <div class="card card-outline">
            <div class="card-header">
                <h3 class="card-title">Day Closure Report</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <table class="table table-striped" id="summaryTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Loans</th>
                                <th>Collections</th>
                                <th>Investments</th>
                                <th>Expenses</th>
                                <th>Temporary Loans</th>
                                <th>Temporary Loan Payments</th>
                                <th>Net</th>
                                <th>Closing Balance</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="breakdownModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Day Closure Breakdown</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="breakdownContent">
                <!-- AJAX content -->
            </div>
        </div>
    </div>
</div>
<?php
include('footer.php');
?>
<script>
    let summaryTable;

    function loadSummaries() {
        $.get("get_day_summaries.php", function(data) {
            const summaries = JSON.parse(data);
            summaryTable.clear();
            summaries.forEach(r => {
                summaryTable.row.add([
                    formatDate(r.created_on),
                    formatAmount(r.total_loans),
                    formatAmount(r.total_collections),
                    formatAmount(r.total_investments),
                    formatAmount(r.total_expenses),
                    formatAmount(r.total_temp_loans),
                    formatAmount(r.total_temp_loan_payments),
                    formatAmount(r.net),
                    formatAmount(r.net_amount),
                    `<button class="btn btn-sm btn-info" onclick="showBreakdown(${r.id})">View</button>`
                ]);
            });
            summaryTable.draw();
        });
    }

    function showBreakdown(summaryId) {
        $.get("get_day_breakdown.php", {
            id: summaryId
        }, function(data) {
            $('#breakdownContent').html(data);
            $('#breakdownModal').modal('show');
        });
    }

    $(document).ready(function() {
        summaryTable = $('#summaryTable').DataTable({
                order: [[0, 'desc']],
                columnDefs: [{
                        targets: [1,2,3,4,5,6,7, 8],   // target column
                        className: "align-right",    
                    },{
                        targets: [0],   // target column
                        className: "white-space-nowrap",    
                    },
                ]
            });
        loadSummaries();
    });
</script>