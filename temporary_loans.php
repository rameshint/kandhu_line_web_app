<?php
$title = 'Temporary Loans';
include('header.php');
include('db.php');
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
        <h3 class="card-title">Temporary Loans</h3>
      </div>
      <div class="card-body">
        <div class="row">

          <?php
          if ($running_date <= date("Y-m-d")) {
          ?>

            <!-- Add Investment -->
            <form method="POST" action="save_temporary_loans.php" class="row g-2 mb-4">
              <div class="col-md-3">
                <input type="text" name="source_name" class="form-control" placeholder="Enter finance name" required>
              </div>
              <div class="col-md-2">
                <input type="date" name="borrow_date" class="form-control" <?=$line=='Daily'?'readonly':''?>   required value="<?= $running_date ?>">
              </div>
              <div class="col-md-2">
                <input type="number" name="amount" class="form-control" placeholder="Enter the Amount" step="0.01" required>
              </div>
              <div class="col-md-3">
                <input type="text" name="remarks" class="form-control" placeholder="Enter Description">
              </div>
              <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Add</button>
              </div>
            </form>
          <?php
          }
          ?>


          <!-- Investment Table -->
          <table class="table table-bordered table-sm table-striped">
            <thead>
              <tr>
                <th>Source</th>
                <th>Borrow Date</th>
                <th>Borrowed</th>
                <th>Repaid</th>
                <th>Repay Date</th>
                <th>Unclear Payments</th>
                <th>Interest Paid</th>
                <th>Balance</th>
                <th>Remarks</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $res = $conn->query("SELECT t.*, ifnull(u.amount,0) unclear_repaid_amount, ifnull(i.amount,0) interest_paid FROM temp_loans t 
              LEFT JOIN (SELECT temp_loan_id, SUM(amount) amount FROM temp_loan_payments WHERE flag = 0 GROUP BY temp_loan_id) u ON u.temp_loan_id = t.id
              LEFT JOIN (SELECT temp_loan_id, SUM(amount) amount FROM temp_loan_payments WHERE flag = 1 and head = 'Interest' GROUP BY temp_loan_id) i ON i.temp_loan_id = t.id
              where line = '".$_SESSION['line']."'
              ORDER BY borrow_date DESC");

              while ($row = $res->fetch_assoc()) {
                $balance = $row['amount'] - $row['repaid_amount'];
                echo "<tr>
                        <td>{$row['source_name']}</td>
                        <td>".formatDate($row['borrow_date'])."</td>
                        <td>" . formatToIndianCurrency($row['amount']) . "</td>
                        <td>" . formatToIndianCurrency($row['repaid_amount']) . "</td>
                        <td>{$row['repay_date']}</td>
                        <td>" . formatToIndianCurrency($row['unclear_repaid_amount']) . "</td>
                        <td>" . formatToIndianCurrency($row['interest_paid']) . "</td>
                        <td>" . formatToIndianCurrency($balance) . "</td>
                        <td>{$row['remarks']}</td>
                        <td>";
                if ($row['flag'] == 0) {
                  echo "<button class='btn btn-sm btn-danger' onclick='deleteLoan(" . $row['id'] . ")'>Delete</button>";
                } else {
                  if ($row['unclear_repaid_amount'] > 0) {
                    echo "&nbsp;<button class='btn btn-sm btn-danger' onclick='deleteUnclearRepayments(" . $row['id'] . ")'>Clear Repayment</button>";
                  } elseif ($balance > 0 && $running_date <= date("Y-m-d")) {
                    echo "<button class='btn btn-sm btn-success' onclick='showRepayModal(" . json_encode($row) . ")'>Repay</button>";
                  }
                }
                echo "</td>
                    </tr>";
              }
              ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>

</div>

<!-- Repay Modal -->
<div class="modal fade" id="repayModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="repay_temporary_loans.php">
      <input type="hidden" name="id" id="repay_id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Repay Loans</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label>Date</label>
            <input type="date" name="repay_date" class="form-control mb-2" required  <?=$line=='Daily'?'readonly':''?>   value="<?=$running_date ?>">
          </div>
          <div class="mb-2">
            <label>Repayment Type</label>
            <select name="head" class="form-control">
              <option>Principal</option>
              <option>Interest</option>
            </select>
          </div>
          <div class="mb-2">
            <label>Amount</label>
            <input type="number" name="repaid_amount" class="form-control" step="0.01" placeholder="Repaid Amount" required>
          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Submit</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php
include('footer.php');
?>

<script>
  function showRepayModal(data) {
    document.getElementById('repay_id').value = data.id;
    new bootstrap.Modal(document.getElementById('repayModal')).show();
  }

  function deleteLoan(id) {
    if (confirm("Delete this Entry?")) {
      $.post('delete_temporary_loan.php', {
        id
      }, function(res) {
        const r = JSON.parse(res);
        if (r.status === 'success')
          location.reload();
      });
    }
  }

  function deleteUnclearRepayments(id) {
    if (confirm("Clear UnClear Payments?")) {
      $.post('delete_temporary_loan_payments.php', {
        id
      }, function(res) {
        const r = JSON.parse(res);
        if (r.status === 'success')
          location.reload();
      });
    }
  }
</script>