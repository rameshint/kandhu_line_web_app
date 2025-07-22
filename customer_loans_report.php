<?php
$title = 'Report - Customer Loans';
include('header.php');
require 'db.php';

$loan_type = $_SESSION['line'];

$sql = "
select * from (
    SELECT 
        c.customer_no,
        l.id,
        c.name,
        l.loan_date,
        l.tenure,
        l.expiry_date,
        case when l.expiry_date IS NOT NULL and l.status = 'Open' then if(l.expiry_date<CURRENT_DATE,1,0) ELSE 0 END overdue,
        g.late_pay,
        IFNULL(SUM(l.amount), 0) AS total_loan,
        IFNULL(SUM(col.paid_amount), 0) AS total_paid,
        IFNULL(SUM(l.amount), 0) - IFNULL(SUM(col.paid_amount), 0) AS balance,
        col.interest_amount,
        l.loan_type,
        l.amount / l.tenure emi
    FROM loans l 
    LEFT JOIN customers c ON c.id = l.customer_id
    left join (SELECT l.id,  case 
when l.loan_type = 'Daily' then (TIMESTAMPDIFF(DAY, l.loan_date, CURRENT_DATE) * (l.amount / l.tenure) - ((l.tenure/100)*5) * l.amount / l.tenure) - ifnull(c.collected,0) > 0 
when l.loan_type = 'Weekly' then TIMESTAMPDIFF(WEEK, l.loan_date, CURRENT_DATE) * (l.amount/l.tenure) - ifnull(c.collected,0) > 0 
when l.loan_type = 'Monthly' then TIMESTAMPDIFF(MONTH, l.loan_date, CURRENT_DATE) * (l.amount/l.tenure) - ifnull(c.collected,0) > 0
END late_pay FROM loans l
            LEFT JOIN (SELECT loan_id, SUM(amount) collected FROM collections WHERE flag = 1 GROUP BY loan_id) c ON c.loan_id = l.id
            WHERE l.flag = 1 AND l.`status` = 'Open' AND l.expiry_date > current_date) g on l.id = g.id
    LEFT JOIN (
        SELECT loan_id, SUM(if(head='EMI' , amount,0)) paid_amount, SUM(if(head='Interest' , amount,0)) interest_amount 
        FROM collections
        WHERE flag = 1
        GROUP BY loan_id
    ) col ON l.id = col.loan_id
    where l.loan_type = '$loan_type'
    GROUP BY c.id,l.id
    ORDER BY c.customer_no
    )  f where balance > 0
";

$result = $conn->query($sql);
?>
<div class="row">
    <div class="col-12">
        <!-- The icons -->

        <div class="card card-outline">
            <div class="card-header">
                <h3 class="card-title">Customer Loans Report</h3>
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
                    
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Customer No</th>
                                <th>Customer Name</th>
                                <th>Tenure</th>
                                <th>EMI</th>
                                <th>Opening Date</th>
                                <th>End Date</th>
                                <th>Loan Amount</th>
                                <th>Collected</th>
                                <th>Interest</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()) {
                                $className = '';
                                if ($row['overdue']) {
                                    $className = 'table-danger';
                                } elseif ($row['late_pay']) {
                                    $className = 'table-warning';
                                }
                            ?>
                                <tr class="<?= $className ?>">
                                    <td><?= htmlspecialchars($row['customer_no']) ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['tenure']) ?></td>
                                    <td align="right"><?= number_format($row['emi'], 2) ?></td>
                                    <td><?= formatDate($row['loan_date']) ?></td>
                                    <td><?= formatDate($row['expiry_date']) ?></td>
                                    <td align="right"><?= number_format($row['total_loan'], 2) ?></td>
                                    <td align="right"><?= number_format($row['total_paid'], 2) ?></td>
                                    <td align="right"><?= number_format($row['interest_amount'], 2) ?></td>
                                    <td align="right"><?= number_format($row['balance'], 2) ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <div class="col-md-3">
                        <span class="badge text-bg-warning">Late Pay</span>
                        <span class="badge text-bg-danger">Overdue</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


<?php
include('footer.php');
?>