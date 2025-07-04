<?php
$title = 'Customers';
include('header.php');
?>
<div class="row">
    <div class="col-12">
        <!-- The icons -->
        <div class="col-12">
            <div class="card card-outline">
                <div class="card-header">
                    <h3 class="card-title">Customers</h3>
                </div>
                <div class="card-body">
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#customerModal" onclick="resetForm()">Add New Customer</button>

                    <table id="customerTable" class=" table table-striped">
                        <thead>
                            <tr>
                                <th width="10%">Cust. No</th>
                                <th>Name</th>
                                <th>Address Line 1</th>
                                <th>Contact No</th>
                                <th>Secondary Contact No</th>
                                <th>District</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="customerBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /.col -->
</div>

<!-- Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="customerForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Customer Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <input type="hidden" id="customer_id" name="id">
                    <div class="col-md-2">
                        <label class="form-label">Customer No *</label>
                        <input type="text" class="form-control" name="customer_no" id="customer_no" required>
                    </div>
                    <div class="col-md-10">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact No *</label>
                        <input id="phoneMask" name="contact_no" type="text" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Secondary Contact No</label>
                        <input id="phoneMask" name="secondary_contact_no" type="text" class="form-control" >
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Address Line 1 *</label>
                        <input type="text" class="form-control" name="address_line1" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Address Line 2</label>
                        <input type="text" class="form-control" name="address_line2">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">District *</label>
                        <input type="text" class="form-control" name="district" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pincode *</label>
                        <input type="text" class="form-control" name="pincode" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Aadhar Card</label>
                        <input type="text" class="form-control" name="aadharcard">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" type="submit">Save Customer</button>
                </div>
            </div>
        </form>
    </div>
</div>




<?php
include('footer.php');
?>
<script>
    $(document).ready(function() {
        loadCustomers();

        $('#customerForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'save_customer.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'error') {
                        alert(res.message); // Show validation message
                    } else {
                        $('#customerModal').modal('hide');
                        loadCustomers();
                        resetForm();
                    }
                }
            });
        });

    });

    function loadCustomers() {
        $.get('get_customers.php', function(data) {
            let customers = JSON.parse(data);
            let rows = '';
            customers.forEach(c => {
                rows += `<tr>
                <td>${c.customer_no}</td>
                <td>${c.name}</td> 
                <td>${c.address_line1}</td> 
                <td>${c.contact_no}</td>
                <td>${c.secondary_contact_no}</td>
                <td>${c.district}</td>
                <td style="white-space: nowrap;">
                    <button class='btn btn-sm btn-primary' onclick='editCustomer(${JSON.stringify(c)})'>Edit</button>
                    <button class='btn btn-sm btn-success' onclick='newCustomer(${JSON.stringify(c)})'>Create New</button>
                </td>
            </tr>`;
            });
            $('#customerBody').html(rows);
            $('#customerTable').DataTable();
        });
    }

    function resetForm() {
        $('#customerForm')[0].reset();
        $('#customer_id').val('');
        $('#customerModal').modal('show');
    }

    function editCustomer(data) {
        Object.keys(data).forEach(key => {
            $(`[name="${key}"]`).val(data[key]);
        });
        $('#customerModal').modal('show');
    }

    function newCustomer(data) {
        Object.keys(data).forEach(key => {
             
            if(key !='id' && key!='customer_no')
                $(`[name="${key}"]`).val(data[key]);
            else{
                $(`[name="${key}"]`).val('');
            }
        });
        $('#customerModal').modal('show');
    }

    
</script>