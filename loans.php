<?php
$title = 'Loans';
include('header.php');
include_once'utility.php';

$loan_type = $_SESSION['line'];
if($loan_type == 'Daily'){
    $running_date = getBusinessDate();
}else{
    $date_obj = new DateTime();
    $running_date = $date_obj->format('Y-m-d');
}
$closing_date = new DateTime($running_date);
 
         
if($loan_type=='Daily'){
    $tenure = 100;
    $closing_date->modify('+100 days');
}else if($loan_type=='Weekly'){
    $tenure = 10;
    $closing_date->modify('+10 weeks');
}else if($loan_type=='Monthly'){
    $tenure = 10;
    $closing_date->modify('+10 months');
}

?>

<div class="row">
    <div class="col-12">
        <!-- The icons -->
        <div class="col-12">
            <div class="card card-outline">
                <div class="card-header">
                    <h3 class="card-title">Loans</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3 row">
                        <div class="col-md-4">
                            <label for="customerNo" class="form-label">Enter Customer No</label>
                            <input type="text" id="customerNo" class="form-control" placeholder="Customer No">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100" onclick="fetchCustomer()">Search</button>
                        </div>
                    </div>

                    <div id="loanSection" style="display: none;">
                        <div id="customerDetails" class="mb-3"></div>
                        <div id="loanDetails" class="mb-3">
                            <?php
                            if($running_date <= date("Y-m-d")){
                            ?>
                            <button class="btn btn-primary mb-3 "  data-bs-toggle="modal" data-bs-target="#loanModal" id="add-new-loan" onclick="resetLoanForm()">Add New Loan</button>
                            <?php
                            }
                            ?>

                            <table id="loanTable" class="table table-bordered  table-striped">
                                <thead>
                                    <tr>
                                        <th>Opening Date</th>
                                        <th>End Date</th>
                                        <th>Closing Date</th>
                                        <th>Tenure Taken</th>
                                        <th>Amount</th>
                                        <th>Interest</th>
                                        <th>File Charge</th>
										<th>Balance</th>
                                        <th>Type of Loan</th>
                                        <th>Tenure</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="loanBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Loan Modal -->
<div class="modal fade" id="loanModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="loanForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Loan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <input type="hidden" name="id" id="loan_id">
                    <input type="hidden" name="customer_id" id="form_customer_id">
                    <div class="col-md-6">
                        <label class="form-label">Line *</label>
                        <input type="text" readonly class="form-control " id="loan_type" name="loan_type" value="<?=$loan_type?>" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label ">Tenure *</label>
                        <input type="number" class="form-control calculateLoanAmount calculateExpiryDate" name="tenure" id="tenure"  value="<?=$tenure?>" required max="100" min="1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Opening Date *</label>
                        <input type="date" class="form-control calculateExpiryDate" name="loan_date" id="loan_date" <?=$_SESSION['line']=='Daily'?'readonly':''?> required value="<?=$running_date?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Closing Date *</label>
                        <input type="date" class="form-control" name="expiry_date" id="expiry_date" readonly="true" required value="<?=$closing_date->format('Y-m-d')?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Amount *</label>
                        <input type="number" class="form-control calculateLoanAmount" name="amount" id="total_amount" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Interest *</label>
                        <input type="number" class="form-control calculateLoanAmount" name="interest" id="loan_interest" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">File Charge *</label>
                        <input type="number" class="form-control calculateLoanAmount" name="file_charge" id="loan_file_charge" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Agent *</label>
                        <select name="agent_id" id="agent_id" class="form-select" required>
                            <option value="">Select Agent</option>
                        </select>
                    </div>
                    <div class="clear"></div>
                    <div class="col-md-6">
                        <label>Loan Amount</label>
                        <div id="loan_amount" style="font-size:x-large; ">
                            0.00
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label>EMI Amount</label>
                        <div id="emi_amount" style="font-size:x-large; ">
                            0.00
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" id="save_loan" class="btn btn-success">Save Loan</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="loanViewModal" tabindex="-1" aria-labelledby="loanViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loanViewModalLabel">Loan Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="loanDetailsContent">
                <!-- Loan details will be filled here -->
            </div>
        </div>
    </div>
</div>
<?php
include('footer.php');
?>
<script>

    function fetchCustomer() {
        const customerNo = $('#customerNo').val().trim();

        // Clear previous content first
        $('#customerDetails').empty();
        $('#loanBody').empty();
        $('#form_customer_id').val('');

        if (!customerNo) {
            alert('Please enter a Customer No');
            return;
        }

        $.get('get_customer_by_no.php?customer_no=' + customerNo, function(data) {
            const result = JSON.parse(data);
            console.log(result)
            if (result.status === 'error') {


                $('#customerDetails').html('<div class="alert alert-danger">Customer not found</div>');
                $('#loanBody').empty();
                $('#loanSection').show();
                $('#loanDetails').hide();
            } else {
                const c = result.customer;
                $('#form_customer_id').val(c.id);
                $('#customerDetails').html(`
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-sm table-striped">
                                                    <tr><th width=20%>Customer No</th><td width=30%>${c.customer_no}</td><th width=20%>Name</th><td>${c.name}</td></tr>
                                                    <tr><th>Address Line1</th><td>${c.address_line1}</td><th>Address Line1</th><td>${c.address_line2}</td></tr>
                                                    <tr><th>Contact No</th><td>${c.contact_no}</td><th>Secondary Contact No</th><td>${c.secondary_contact_no}</td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    `);
                loadLoans(c.id);
                $('#loanSection').show();
                $('#loanDetails').show();
            }
        });
        $("#customerData").css('hide')
    }

    $(document).ready(function() {

        $("#customerNo").focus();
        $(document).on("blur", ".calculateLoanAmount", function() {
            calculateLoanAmount()
        });

         

        $(document).on("blur", ".calculateExpiryDate", function() {
            calculateExpiryDate()
        });


        $('#customerNo').keypress(function(e) {
            if (e.which === 13) { // 13 is Enter key
                fetchCustomer();
            }
        });

        $('#loanForm').submit(function(e) {
            e.preventDefault();
            $.post('save_loan.php', $(this).serialize(), function(res) {
                let result = JSON.parse(res);
                if (result.status === 'success') {
                    $('#loanModal').modal('hide');
                    loadLoans($('#form_customer_id').val());
                } else {
                    alert(result.message);
                }
            });
        });


    });

    function loadLoans(customerId) {
        $.get('get_loans.php?customer_id=' + customerId, function(data) {
            let loans = JSON.parse(data);
            let rows = '';
            let open_loans = 0;
			let balance = 0;
            loans.forEach(l => {
                editBtn = ''
                deleteBtn = ''
                if (l.flag == 0) {
                    editBtn = `<button class="btn btn-sm btn-primary" onclick='editLoan(${JSON.stringify(l)})'>Edit</button>`


                    if (l.bills == 0) {
                        deleteBtn = `<button class="btn btn-sm btn-danger" onclick='deleteLoan(${l.id}, ${l.customer_id})'>Delete</button>`
                    }
                }
                status = ''
                if (l.status == 'Open') {
                    open_loans += 1
                    status = '<span class="badge text-bg-primary">Open</span>'
                } else {
                    status = '<span class="badge text-bg-secondary">Closed</span>'
                }
				
                if (parseFloat(l.balance) > 0)
				    balance += parseFloat(l.balance)

                expiry_date = ''
                if (l.expiry_date == null) {
                    expiry_date = ''
                } else {
                    expiry_date = l.expiry_date
                }

                loan_closed = ''
                if (l.loan_closed == null) {
                    loan_closed = ''
                } else {
                    loan_closed = l.loan_closed
                }

                rows += `<tr >
                            <td>${l.loan_date}</td>
                            <td>${expiry_date}</td>
                            <td>${loan_closed}</td>
                            <td>${l.days}</td>
                            <td>${formatAmount(l.amount)}</td>
                            <td>${formatAmount(l.interest)}</td>
                            <td>${formatAmount(l.file_charge)}</td>
							<td>${formatAmount(l.balance)}</td>
                            <td>${l.loan_type}</td>
                            <td>${l.tenure}</td>
                            <td>${status}</td>
                            <td style="white-space: nowrap">
                                <button class="btn btn-sm btn-info" onclick="viewLoan(${l.id})">View</button>
                                ${editBtn}   
                                ${deleteBtn}  
                            </td>
                        </tr>`;
            });
             
            if (balance > 0 ) {
                $("#add-new-loan").prop("disabled", true)
            } else {
                $("#add-new-loan").prop("disabled", false)
            }

            if ($.fn.DataTable.isDataTable('#loanTable')) {
                $('#loanTable').DataTable().clear().destroy();
            }
            $('#loanBody').html(rows);

            $('#loanTable').DataTable({
                order: [
                    [0, 'desc']
                ],
                columnDefs: [{
                    targets: [4, 5, 6], // target column
                    className: "align-right",

                }]
            });
        });
    }

    function resetLoanForm() {
        $('#loanForm')[0].reset();
        $('#loan_id').val('');
    }

    function editLoan(data) {
        Object.keys(data).forEach(k => {
            $(`[name="${k}"]`).val(data[k]);
        });
        calculateLoanAmount();
        $('#loanModal').modal('show');
    }

    function calculateExpiryDate() {
        tenure = isNaN(parseInt($("#tenure").val())) ? 0 : parseInt($("#tenure").val())
        loan_type = $("#loan_type").val()
        opening_date = new Date($("#loan_date").val())
        if (tenure > 0) {
            let formattedDate = new Date()
            if (loan_type == 'Daily') {
                opening_date.setDate(opening_date.getDate() + tenure)
                formattedDate = opening_date.toISOString().split('T')[0];
            } else if (loan_type == 'Weekly') {
                tenure = tenure * 7
                opening_date.setDate(opening_date.getDate() + tenure)
                formattedDate = opening_date.toISOString().split('T')[0];
            } else if (loan_type == 'Monthly') {
                opening_date.setMonth(opening_date.getMonth() + tenure)
                formattedDate = opening_date.toISOString().split('T')[0];
            }
            $("#expiry_date").val(formattedDate)
        }



    }

    function calculateLoanAmount() {
        total_amount = isNaN(parseInt($("#total_amount").val())) ? 0 : parseInt($("#total_amount").val())
        loan_interest = isNaN(parseInt($("#loan_interest").val())) ? 0 : parseInt($("#loan_interest").val())
        loan_file_charge = isNaN(parseInt($("#loan_file_charge").val())) ? 0 : parseInt($("#loan_file_charge").val())
        tenure = isNaN(parseInt($("#tenure").val())) ? 0 : parseInt($("#tenure").val())
        var loan_amount = total_amount - loan_interest - loan_file_charge
        let emi_amount = total_amount / tenure
        $("#loan_amount").text(loan_amount.toFixed(2))
        $("#emi_amount").text(emi_amount.toFixed(2))
        if (loan_amount <= 0) {
            $("#save_loan").hide();
            $("#loan_amount").css("color", "red");
        } else {
            $("#save_loan").show();
            $("#loan_amount").css("color", "black");
        }
    }

    function viewLoan(loanId) {
        $.get('get_loan_by_id.php?id=' + loanId, function(data) {
            const loanDetails = JSON.parse(data);
            const loan = loanDetails.loan
            const collections = loanDetails.collections

            status = ''
            if (loan.status == 'Open') {
                status = '<span class="badge text-bg-primary">Open</span>'
            } else {
                status = '<span class="badge text-bg-secondary">Closed</span>'
            }


            if (loan.expiry_date == null) {
                expiry_date = ''
            } else {
                expiry_date = loan.expiry_date
            }

            if (loan.loan_closed == null) {
                loan_closed = ''
            } else {
                loan_closed = loan.loan_closed
            }

            let html = `
                <table class="table table-bordered">
                    <tr><th>Type of Loan</th><td>${loan.loan_type}</td><th>Tenure</th><td>${loan.tenure}</td></tr> 
                    <tr><th>Loan Date</th><td>${loan.loan_date}</td><th>End Date</th><td>${expiry_date}</td></tr> 
                    <tr><th>Amount</th><td>${loan.amount}</td><th>Interest</th><td>${loan.interest}</td></tr>
                    <tr><th>File Charge</th><td>${loan.file_charge}</td><th>Status</th><td>${status}</td></tr>                    
                    <tr><th>Closed On</th><td>${loan_closed}</td><th>Created On</th><td>${loan.created_on}</td></tr>
                    <tr><th>Agent</th><td>${loan.agent_name}</td></tr>
                </table>
            `;
            if (collections.length > 0) {
                html += `<h5>Collections</h5><table class="table table-bordered"><thead><tr><th>Date</th><th>Head</th><th>Amount</th></tr></thead><tbody>`
                collections.forEach(collection => {
                    html += `<tr>
                            <td>${collection.collection_date}</td>
                            <td>${collection.head}</td>
                            <td align=right>${formatAmount(collection.amount)}</td>
                        </tr>`
                });
                html += `</tobdy></table>`
            }

            $('#loanDetailsContent').html(html);
            const loanModal = new bootstrap.Modal(document.getElementById('loanViewModal'));
            loanModal.show();
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

    function deleteLoan(id, customer_id) {
        if (confirm("Delete this Loan?")) {
            $.post('delete_loan.php', {
                id
            }, function(res) {
                const r = JSON.parse(res);
                if (r.status === 'success') {
                    loadLoans(customer_id)
                }
            });
        }
    }

    // Call this when the page loads
    $(document).ready(function() {
        loadAgentsDropdown();
    });
</script>