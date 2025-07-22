<?php
$title = 'Investments';
include('header.php');
include_once 'utility.php';
$line = $_SESSION['line'];
if($line == 'Daily'){
    $running_date = getBusinessDate();
}else{
    $date_obj = new DateTime();
    $running_date = $date_obj->format('Y-m-d');
}
?>
<div class="row">
    <div class="col-12">
        <!-- The icons -->

        <div class="card card-outline">
            <div class="card-header">
                <h3 class="card-title">Investment Management</h3>
            </div>
            <div class="card-body">

                <?php
                if ($running_date <= date("Y-m-d")) {
                ?>
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#investmentModal"><i class="bi bi-plus-circle"></i> Add Investment</button>
                <?php
                }
                ?>
                <table class="table table-sm table-striped" id="investmentTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Source</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Agent</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Investment Modal -->
<div class="modal fade" id="investmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="investmentForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Investment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-2">
                <div class="col-md-6">
                    <label for="">Date</label>
                    <input type="date" value="<?= $running_date ?>" <?=$line=='Daily'?'readonly':''?>  name="investment_date" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="">Select Investor </label>
                    <?php include 'agent_dropdown.php'; ?>
                </div>
                <div class="col-md-6">
                    <label for="">Source</label>
                    <input type="text" name="source" class="form-control" required placeholder="Enter source of investment">
                </div>
                <div class="col-md-6">
                    <label for="">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required placeholder="Enter the Amount">
                </div>
                <div class="col-md-12">
                    <label for="">Description</label>
                    <input type="text" name="description" class="form-control" placeholder="Enter Description">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>

<?php
include_once 'footer.php';
?>
<script>
    let investmentTable;

    function loadInvestments() {
        $.get("get_investments.php", function(data) {
            const rows = JSON.parse(data);
            investmentTable.clear();
            rows.forEach(r => {
                let actions = ''
                if (r.flag == 0) {
                    actions = `<button class="btn btn-sm btn-danger" onclick="deleteInvestment(${r.id})">Delete</button>`
                }
                investmentTable.row.add([
                    formatDate(r.investment_date),
                    r.source,
                    r.description,
                    formatAmount(r.amount),
                    r.agent_name || '',
                    actions
                ]);
            });
            investmentTable.draw();
        });
    }

    $('#investmentForm').on('submit', function(e) {
        e.preventDefault();
        $.post('save_investment.php', $(this).serialize(), function(res) {
            const r = JSON.parse(res);
            if (r.status === 'success') {
                $('#investmentForm')[0].reset();
                $('#investmentModal').modal('hide');
                loadInvestments();
            } else {
                alert(r.message);
            }
        });
    });

    function deleteInvestment(id) {
        if (confirm("Delete this investment?")) {
            $.post('delete_investment.php', {
                id
            }, function(res) {
                const r = JSON.parse(res);
                if (r.status === 'success') loadInvestments();
            });
        }
    }

    $(document).ready(function() {
        investmentTable = $('#investmentTable').DataTable({
            order: [
                [0, 'desc']
            ],
            columnDefs: [{
                targets: [3], // target column
                className: "align-right",

            }]
        });
        loadInvestments();
    });
</script>