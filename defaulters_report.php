<?php
$title = 'Report - Defaulters';
include('header.php');
require 'db.php';

$loan_type = $_SESSION['line'];

$sql = "
SELECT c.customer_no, c.name,c.address_line1,c.contact_no,c.secondary_contact_no, l.loan_date, l.expiry_date,l.tenure, l.amount loan_amount, 
IFNULL(SUM(a.amount),0) paid_amount,  
(l.amount - IFNULL(SUM(a.amount),0)) balance_amount,
DATEDIFF(CURDATE(), l.expiry_date) overdue_days
FROM loans l
INNER JOIN customers c ON c.id = l.customer_id
LEFT JOIN collections a ON a.loan_id = l.id AND a.flag = 1
WHERE l.loan_closed IS NULL AND l.loan_type = '$loan_type'
GROUP BY l.id
HAVING CURDATE() > l.expiry_date AND balance_amount > 0
ORDER BY l.expiry_date asc
";

$result = $conn->query($sql);
?>
<div class="row">
    <div class="col-12">
        <!-- The icons -->

        <div class="card card-outline">
            <div class="card-header">
                <h3 class="card-title">Defaulters List</h3>
            </div>
            <div class="card-body">
                <form method="GET" accept="#" style="display: none">
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
                <br />
                <div class="row">
                    <div style="width: 100%;">
                        <button onclick="printTable()" class="btn btn-secondary" style="position:relative; float: right;">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <br />
                    </div>
                    <div id="printSection">
                        <style>
                            @media print {
                                .table> :not(caption)>*>* {
                                    padding: 2px !important;
                                }

                                .table {
                                    font-size: 12px !important;
                                }

                                .table td,
                                .table th,
                                .table tr {
                                    border-color: #000;
                                }

                                .table td {
                                    padding-left: 2px !important;
                                }
                            }
                        </style>
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Customer No</th>
                                    <th>Customer Name</th>
                                    <th>Address</th>
                                    <th>Contact No</th>
                                    <th>Opening Date</th>
                                    <th>End Date</th>
                                    <th>Loan Amount</th>
                                    <th>Collected</th>
                                    <th>Balance</th>
                                    <th>Overdue Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()) {

                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['customer_no']) ?></td>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= htmlspecialchars($row['address_line1']) ?></td>
                                        <td><?= $row['contact_no'] . (!empty($row['secondary_contact_no']) ? "<br />" : "") . $row['secondary_contact_no'] ?></td>
                                        <td><?= formatDate($row['loan_date']) ?></td>
                                        <td><?= formatDate($row['expiry_date']) ?></td>
                                        <td align="right"><?= number_format($row['loan_amount'], 2) ?></td>
                                        <td align="right"><?= number_format($row['paid_amount'], 2) ?></td>
                                        <td align="right"><?= number_format($row['balance_amount'], 2) ?></td>
                                        <td align="right"><?= $row['overdue_days'] ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>


<?php
include('footer.php');
?>