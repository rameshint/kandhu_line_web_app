<?php
$title = 'Report - Customer Loans';
include('header.php');
require 'db.php';
 
$loan_type = $_SESSION['line'];
 
$sql = "
SELECT distinct c.customer_no,c.name FROM loans l
INNER JOIN customers c ON c.id = l.customer_id
WHERE l.`status` = 'Open' AND l.loan_type = '$loan_type'
ORDER BY c.customer_no

";

/*$sql = "WITH RECURSIVE numbers AS (
  SELECT 1 AS n
  UNION ALL
  SELECT n + 1 FROM numbers WHERE n < 140
)
SELECT n customer_no, 'Ramesh Nallamuthu Ramesh' name FROM numbers";
*/
$result = $conn->query($sql);
?>

<div class="row">
    <div class="col-12">
        <!-- The icons -->

        <div class="card card-outline">
            <div class="card-header">
                <h3 class="card-title">Customer List</h3>
            </div>
            <div class="card-body">
                <form method="GET" accept="#" style="display:none">
                    <div class="row">

                        <div class="col-md-5">
                            <table class="table table-striped">
                                <tr>
                                    <td style="vertical-align: middle;">Select Loan Type</td>
                                    <td><select name="loan_type" class="form-select">
                                            <option <?= $loan_type == 'Daily' ? 'selected' : '' ?>>Daily</option>
                                            <option <?= $loan_type == 'Weekly' ? 'selected' : '' ?>>Weekly</option>
                                            <option <?= $loan_type == 'Monthly' ? 'selected' : '' ?>>Monthly</option>
                                        </select></td>
                                    <td><input type="submit" value="Submit" class="btn btn-primary" /></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </form>
                <div style="width: 100%;">
                    <button onclick="printTable()" class="btn btn-secondary" style="position:relative; float: right;">
                        <i class="bi bi-printer"></i> Print
                    </button>
                    <br />
                </div>
                <div id="printSection">
                    DATE : <b><?= formatDate(getBusinessDate()) ?></b>
					<style>
					.table > :not(caption) > * > * {
						padding: 0px !important;
					}
					.table{
						font-size:10px !important;
					}
					.table td, .table th, .table tr{
						border-color: #000;
					}
					.table td{
						padding-left:2px !important;
					}
					</style>
                    <div class="row" style="display: flex;">

                        <div class="col-md-4">

                            <table class="table table-bordered table-33" >
                                <thead>
                                    <tr>
                                        <th width="10%">No</th>
                                        <th>Name</th>
                                        <th width="20%">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 0;
                                    $table = 1;
                                    while ($row = $result->fetch_assoc()) {
                                        if ($i % 51 == 0 && $i > 0) {
                                            echo '</tbody>
                                            </table>
                                        </div>';
                                            if ($table % 3 == 0) {
                                                echo '</div>
                                            <div class="row" style="display: flex;">';
                                                $table = 0;
                                            }
                                            echo '<div class="col-md-4">
                                        <table class="table table-bordered table-33" >
                                            <thead>
                                                <tr>
                                                    <th width="10%">No</th>
                                                    <th >Name</th>
                                                    <th width="20%">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>';
                                            $table++;
                                        }
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['customer_no']) ?></td>
                                            <td><?= substr($row['name'], 0, length: 23) ?></td>
                                            <td></td>
                                        </tr>
                                    <?php
                                        $i++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
                        for ($t = 1; $t <= 3 - $table; $t++) {
                            echo '<div class="col-md-4">
                                    <table class="table-33">
                                        <tr>
                                            <th>&nbsp;</th>
                                        </tr>
                                    </table>
                                </div>';
                        }
                        ?>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


<?php
include('footer.php');
?>