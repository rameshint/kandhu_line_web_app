<?php
$title = 'Expenses';
include('header.php');
include_once 'utility.php';
$running_date = getBusinessDate();

?>
<div class="row">
    <div class="col-12">
        <!-- The icons -->

        <div class="card card-outline">
            <div class="card-header">
                <h3 class="card-title">Expense Management</h3>
            </div>
            <div class="card-body">

                <?php
                if ($running_date <= date("Y-m-d")) {
                ?>
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#expenseModal">Add Expense</button>
                    <button class="btn btn-outline-primary mb-3" data-bs-toggle="modal" data-bs-target="#categoryModal">
                        <i class="bi bi-plus-circle"></i> Add Expense Category
                    </button>
                <?php
                }
                ?>


                <table class="table table-bordered table-striped" id="expenseTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
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
<!-- Expense Modal -->
<div class="modal fade" id="expenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="expenseForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-2">
                <div class="col-md-6">
                    <label for="expense_date">Date</label>
                    <input type="date" name="expense_date" readonly class="form-control" value="<?php echo $running_date ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="agent_id">Agent</label>
                    <?php include 'agent_dropdown.php'; ?>
                </div>
                <div class="col-md-6">
                    <label for="category">Category</label>
                    <select id="category" name="category" class="form-select" required>
                        <!-- Options will be populated via PHP or JS -->
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="amount">Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label for="description">Description</label>
                    <input type="text" name="description" class="form-control">
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="categoryForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Manage Expense Categories</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="newCategory" class="form-label">New Category Name</label>
                        <input type="text" class="form-control" id="newCategory" required>
                    </div>
                    <button type="submit" class="btn btn-primary mb-3">Add Category</button>

                    <hr>

                    <h6>Existing Categories</h6>
                    <ul class="list-group" id="categoryList">
                        <!-- Filled via JS -->
                    </ul>

                </div>
            </form>
        </div>
    </div>
</div>

<?php
include_once 'footer.php';
?>
<script>
    let expenseTable;

    function loadExpenses() {
        $.get("get_expenses.php", function(data) {
            const rows = JSON.parse(data);
            expenseTable.clear();
            rows.forEach(r => {
                let actions = ''
                if (r.flag == 0) {
                    actions = `<button class="btn btn-sm btn-danger" onclick="deleteExpense(${r.id})">Delete</button>`
                }

                expenseTable.row.add([
                    r.expense_date,
                    r.category,
                    r.description,
                    formatAmount(r.amount),
                    r.agent_name || '',
                    actions
                ]);
            });
            expenseTable.draw();
        });
    }

    $('#expenseForm').on('submit', function(e) {
        e.preventDefault();
        $.post('save_expense.php', $(this).serialize(), function(res) {
            const r = JSON.parse(res);
            if (r.status === 'success') {
                $('#expenseForm')[0].reset();
                $('#expenseModal').modal('hide');
                loadExpenses();
            } else {
                alert(r.message);
            }
        });
    });

    function deleteExpense(id) {
        if (confirm("Delete this expense?")) {
            $.post('delete_expense.php', {
                id
            }, function(res) {
                const r = JSON.parse(res);
                if (r.status === 'success') loadExpenses();
            });
        }
    }

    $(document).ready(function() {
        expenseTable = $('#expenseTable').DataTable({
            order: [
                [0, 'desc']
            ],
            columnDefs: [{
                targets: [3], // target column
                className: "align-right",

            }]
        });
        loadExpenses();
        loadCategories();
    });

    function loadCategories() {
        $.get('get_categories.php', function(data) {
            const categories = JSON.parse(data);
            const select = $('#category');
            const list = $('#categoryList');

            select.empty().append('<option value="">Select Category</option>');
            list.empty();

            categories.forEach(cat => {
                select.append(`<option>${cat.name}</option>`);
                list.append(`<li class="list-group-item">${cat.name}</li>`);
            });
        });
    }

    $('#categoryForm').submit(function(e) {
        e.preventDefault();
        const newCategory = $('#newCategory').val().trim();
        if (newCategory === '') return;

        $.post('add_category.php', {
            name: newCategory
        }, function(res) {
            if (res == 'success') {
                $('#newCategory').val('');
                loadCategories();
            } else {
                alert('Category already exists or error occurred.');
            }
        });
    });

    $('#categoryModal').on('shown.bs.modal', function() {
        loadCategories();
    });
</script>