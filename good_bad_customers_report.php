<?php
$title = 'Report - Good/Bad Customers';
include('header.php');
require 'db.php';

$loan_type = $_SESSION['line'];

$sql = "
SELECT s.customer_no, s.name, s.total_loan_amount, s.total_paid_amount, s.total_balance,MAX(s.has_overdue) AS has_overdue FROM (
SELECT 
	c.id, 
	c.customer_no,
	c.name,
	SUM(l.amount) AS total_loan_amount,
	sum(IFNULL(col.amount,0)) AS total_paid_amount,
	(SUM(l.amount) - SUM(IFNULL(col.amount,0))) AS total_balance,
		case 
			when CURDATE() > l.expiry_date AND 	(SUM(l.amount) - SUM(IFNULL(col.amount,0))) > 0
			then 1 ELSE 0
		END 
	as has_overdue
FROM customers c
JOIN (SELECT s.* ,ROW_NUMBER() over(PARTITION BY s.customer_id ORDER BY s.id DESC) rn from loans s ) l ON l.customer_id = c.id AND l.loan_type = '$loan_type' AND l.rn <= 3
LEFT JOIN collections col ON col.loan_id = l.id AND l.flag = 1 AND col.head = 'EMI'
GROUP BY l.id
) s GROUP BY s.id
";
// Apply filter if set
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
if ($filter == 'good') {
    $sql .= " HAVING has_overdue = 0";
} elseif ($filter == 'bad') {
    $sql .= " HAVING has_overdue = 1";
}

$result = $conn->query($sql);
?>
<div class="row">
    <div class="col-12">
        <!-- The icons -->

        <div class="card card-outline">
            <div class="card-header">
                <h3 class="card-title">Good vs Bad Customers</h3>
            </div>
            <div class="card-body">

                <div class="row">

                    <div class="mb-3">
                        <a href="?filter=all" class="btn btn-secondary <?= $filter == 'all' ? 'active' : '' ?>">All</a>
                        <a href="?filter=good" class="btn btn-success <?= $filter == 'good' ? 'active' : '' ?>">Good</a>
                        <a href="?filter=bad" class="btn btn-danger <?= $filter == 'bad' ? 'active' : '' ?>">Bad</a>
                    </div>

                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Customer No</th>
                                <th>Customer Name</th>
                                <th>Total Loan Amount</th>
                                <th>Total Collected</th>
                                <th>Total Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()) {

                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['customer_no']) ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td align="right"><?= number_format($row['total_loan_amount'], 2) ?></td>
                                    <td align="right"><?= number_format($row['total_paid_amount'], 2) ?></td>
                                    <td align="right"><?= number_format($row['total_balance'], 2) ?></td>
                                    <td align="right">
                                        <?php if ($row['has_overdue']) { ?>
                                            <span class="badge bg-danger">Bad</span>
                                        <?php } else { ?>
                                            <span class="badge bg-success">Good</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>
</div>


<?php
include('footer.php');
?>