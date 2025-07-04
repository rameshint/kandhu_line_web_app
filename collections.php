<?php
$title = 'Collections';
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
    <div class="col-6">
        <!-- The icons -->

        <div class="card card-outline">
            <div class="card-header">
                <h3 class="card-title">Bill Entry</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    if ($running_date > date("Y-m-d")) {
                        echo '<div class="alert alert-danger">Day closure has been done for the day.</div>';
                    } else {
                    ?>
                        <div class="row">
                            <div class="col-md-5">
                                <label>Select Date</label>
                                <input type="date" class="form-control" name="collection_date" id="collection_date" <?=$line=='Daily'?'readonly':''?> value="<?php echo $running_date ?>">
                            </div>
                            <div class="col-md-5">
                                <label>Select Agent</label>
                                <select id="agent_id" class="form-select" required>
                                    <option value="">Select Agent</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <br />
                                <button class="btn btn-primary" id="agent_search">Search</button>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label>Enter Customer No</label>
                            <input type="text" id="customer_no" class="form-control" placeholder="Enter customer no">
                        </div>
                        <div id="customerDetails" class="mb-3"></div>
                        <form id="collectionForm">
                            <div id="loanList"></div>
                        </form>
                    <?php
                    }
                    ?>
                </div>

            </div>

        </div>
    </div>
    <div class="col-6">
        <div class="card card-outline">
            <div class="card-header">
                <h3 class="card-title">Today's Collections</h3>
            </div>

            <div class="card-body">
                <div class="row">

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th width="10%">Cus. No</th>
                                <th>Name</th>
                                <th width="20%">Date</th>
                                <th width="10%">Head</th>
                                <th width="15%">Amount</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="todayCollections"></tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>
</div>

<?php
include('footer.php');
?>
<script>
    function fetchLoans(customerNo) {
        $('#customerDetails').empty();

        $.get('get_customer_by_no.php?customer_no=' + customerNo, function(data) {
            const result = JSON.parse(data);
            if (result.status === 'error') {
                $('#customerDetails').html('<div class="alert alert-danger">Customer not found</div>');
                $('#loanList').empty()
            } else {
                const c = result.customer;
                $('#customerDetails').html(`
                                        <table class="table table-sm">
                                            <tr><th>Name</th><td>${c.name}</td></tr>
                                            <tr><th>Contact No</th><td>${c.contact_no}</td></tr>
                                            <tr><th>District</th><td>${c.district}</td></tr>
                                        </table>
                                    `);
                $.get('fetch_loans_by_customer.php?customer_id=' + c.id, function(data) {
                    const result = JSON.parse(data);

                    if (result.status == 'success') {
                        loans = result.loans;
                        if (loans.length === 0) {
                            $('#loanList').html('<div class="alert alert-danger">No loans found for this customer.</div>');
                        } else {
                            let html = '<table class="table table-sm">';
                            let overdue = ''
                            html += '<tr><th>Loan Date</th><th>Type of Loan</th><th>Amount</th><th>Balance</th><th>EMI</th><th>Interest</th></tr>';
                            loans.forEach(loan => {

                                if (loan.overdue) {
                                    overdue = 'table-danger'
                                }

                                html += `
                                <tr class="${overdue}">
                                    <td style="white-space: nowrap;">${loan.loan_date}<input type="hidden" name="loan_id[]" value="${loan.id}"></td>
                                    <td>${loan.loan_type}</td>
                                    <td align=right>${formatAmount(loan.amount)}</td>
                                    <td align=right>${formatAmount(loan.balance)}</td>
                                    <td><input type="number" name="amount[]" max="${parseFloat(loan.balance)}" class="form-control"></td>
                                    <td><input type="number" name="interest[]" class="form-control"></td>
                                </tr>`;
                            });
                            html += '</table><button class="btn btn-primary mt-2" type="submit">Submit Collections</button>';
                            if (overdue != '') {
                                html += '<div class="col-md-1" style="float:right">                         <span class="badge text-bg-danger">Overdue</span>                        </div>'
                            }
                            $('#loanList').html(html);
                            $("#loanList table input[name='amount[]']").first().focus()

                        }
                    } else {
                        $('#loanList').html(`<div class="alert alert-danger">${result.message}</div>`);
                    }

                });

            }
        });


    }


    function loadTodaysCollections() {
        /*if (!agentId) {
            $('#todayCollections').empty();
            return;
        }*/

        collection_date = $("#collection_date").val()

        $.get('get_todays_collections.php?collection_date=' + collection_date, function(data) {
            const rows = JSON.parse(data);
            let html = '';
            total_collected_amount = 0
            rows.forEach(row => {
                total_collected_amount += parseInt(row.amount)
                html += `<tr>
                        <td>${row.customer_no}</td>
                        <td>${row.customer_name}</td>
                        <td>${row.collection_date}</td>
                        <td>${row.head}</td>
                        <td align=right>${formatAmount(row.amount)}</td>
                        <td><button class="btn btn-sm btn-danger" onclick="deleteCollection(${row.id})">Delete</button></td>
                    </tr>`;
            });
            html += `<tr>
                <th colspan=4>Total</th>
                <th class="align-right">${formatAmount(total_collected_amount)}</th>
            </tr>`
            $('#todayCollections').html(html);
        });
    }


    function loadAgentsDropdown() {
        $.get('get_agents.php', function(data) {
            let agents = JSON.parse(data);
            let options = '<option value="">Select Agent</option>';
            agents.forEach(agent => {
                options += `<option value="${agent.id}">${agent.name}</option>`;
            });
            $('#agent_id').html(options);
        });
    }

    function deleteCollection(id) {
        if (confirm("Delete this Entry?")) {
            $.post('delete_collection.php', {
                id
            }, function(res) {
                const r = JSON.parse(res);
                if (r.status === 'success')
                    loadTodaysCollections()
            });
        }
    }
    $(document).ready(function() {

        $("#customer_no").focus();

        loadAgentsDropdown();

        loadTodaysCollections();

        $("#agent_search").on('click', function() {
            $("#agent_id").trigger('change');
        })

        $('#agent_id').on('change', function() {
            const agentId = $(this).val();
            if (agentId) {
                $('#customer_no').prop('disabled', false);
            } else {
                $('#customer_no').val('').prop('disabled', true);
                $('#loanList').empty();
            }
            //loadTodaysCollections(agentId);
            $("#customer_no").focus();
        });

        $('#customer_no').on('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                let customerNo = $(this).val();
                let agentId = $('#agent_id').val();
                if (!agentId) {
                    alert('Please select an agent first.');
                    return;
                }
                fetchLoans(customerNo);
            }
        });

        $('#collectionForm').on('submit', function(e) {
            e.preventDefault();
            let agentId = $('#agent_id').val();
            let collection_date = $("#collection_date").val()
            if (!agentId) {
                alert('Please select an agent.');
                return;
            }
            const formData = $(this).serialize() + '&agent_id=' + agentId + '&collection_date=' + collection_date;
            $.post('save_collections.php', formData, function(res) {
                const result = JSON.parse(res);
                if (result.status === 'success') {
                    $("#toastSuccess.toast-body").text('Bill Entered Successfully')
                    const toastBootstrap = bootstrap.Toast.getOrCreateInstance($("#toastSuccess"));
                    toastBootstrap.show();
                    $('#collectionForm')[0].reset();
                    $('#loanList').empty();
                    $('#customer_no').val('');
                    //loadTodaysCollections(agentId);
                    loadTodaysCollections();
                    $("#customer_no").focus();
                    $('#customerDetails').empty();
                } else {
                    alert(result.message);
                }
            });
        });
    });
</script>