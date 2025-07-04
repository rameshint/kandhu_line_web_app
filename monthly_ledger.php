<?php
$title = 'Report - Monthly Ledger';
include('header.php');
require 'db.php';


$current_date = new DateTime();
#$current_date->modify('-1 Months');
$month = $current_date->format('Y-m');
if ($_GET['month'] != '') {
    $month = $_GET['month'];
}
$start_date = new DateTime($month . "-01");
$end_date = new DateTime($start_date->format("Y-m-t"));
$st = $start_date->format('Y-m-d');
$et = $end_date->format('Y-m-d');



if ($start_date < $end_date) {
    $sql = "select net_amount FROM day_summary WHERE closure_date < ? ORDER BY closure_date DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $st);
    $stmt->execute();
    $result = $stmt->get_result();
    $opening_balance = 0;
    $row = $result->fetch_assoc();
    $opening_balance = $row['net_amount'];
    $sql = "
        SELECT SUM(total_loans) tot_loans, SUM(total_collections) tot_cols, SUM(total_expenses) tot_exp, SUM(total_temp_loans) tot_temp_loans,
        SUM(total_temp_loan_payments) tot_pay,SUM(total_investments) tot_inv 
        FROM day_summary 
        WHERE closure_date BETWEEN ? AND ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $st, $et);
    $stmt->execute();
    $result = $stmt->get_result();
    $collections = [];

    $summary = $result->fetch_assoc();

    $closing_balance = $opening_balance + $summary['tot_cols'] + $summary['tot_temp_loans'] + $summary['tot_inv'] - $summary['tot_loans'] - $summary['tot_exp'] - $summary['tot_pay'];
}

?>
<div class="row">
    <div class="col-12">
        <!-- The icons -->

        <div class="card card-outline">
            <div class="card-header">
                <h3 class="card-title"> Monthly Ledger</h3>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-md-5">
                        <form method="GET" accept="#">

                            <table class="table table-striped">
                                <tr>
                                    <td style="vertical-align: middle;">Select Month</td>
                                    <td><input type="month" name="month" class="form-control" value="<?= $month ?>" /></td>
                                    <td><input type="submit" value="Submit" class="btn btn-primary" /></td>
                                </tr>
                            </table>

                        </form>

                        <table class="table table-bordered">
                            <tr>
                                <td>Opening Balance</td>
                                <th class="align-right"><?= formatToIndianCurrency($opening_balance) ?>
                                </th>
                            </tr>
                            <tr>
                                <td>Loans<span style="position: relative; float: right;">(-)</span></td>
                                <th class="align-right"><?= formatToIndianCurrency($summary['tot_loans']) ?> </th>
                            </tr>
                            <tr>
                                <td>Collections<span style="position: relative; float: right;">(+)</span></td>
                                <th class="align-right"><?= formatToIndianCurrency($summary['tot_cols']) ?></th>
                            </tr>
                            <tr>
                                <td>Expenses<span style="position: relative; float: right;">(-)</span></td>
                                <th class="align-right"><?= formatToIndianCurrency($summary['tot_exp']) ?></th>
                            </tr>
                            <tr>
                                <td>Temporary Loans<span style="position: relative; float: right;">(+)</span></td>
                                <th class="align-right"><?= formatToIndianCurrency($summary['tot_temp_loans']) ?></th>
                            </tr>
                            <tr>
                                <td>Loan Repayments<span style="position: relative; float: right;">(-)</span></td>
                                <th class="align-right"><?= formatToIndianCurrency($summary['tot_pay']) ?></th>
                            </tr>
                            <tr>
                                <td>Investments<span style="position: relative; float: right;">(+)</span></td>
                                <th class="align-right"><?= formatToIndianCurrency($summary['tot_inv']) ?></th>
                            </tr>
                            <tr>
                                <td>Closing Balance</td>
                                <th class="align-right"><?= formatToIndianCurrency($closing_balance) ?></th>
                            </tr>
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
