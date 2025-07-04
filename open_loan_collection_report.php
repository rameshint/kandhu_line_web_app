<?php
$title = 'Report - Collection Report';
include('header.php');
require 'db.php';


if ($_GET['customer_no'] != '') {
    $sql = "
        SELECT l.id, l.loan_date,l.amount, l.tenure, l.expiry_date, l.loan_type,c.name,c.customer_no FROM loans l
        INNER JOIN customers c ON c.id = l.customer_id
        WHERE c.customer_no = ? AND l.`status` = 'Open' and l.loan_type = ?  ORDER BY loan_date DESC LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $_GET['customer_no'], $_SESSION['line']);
    $stmt->execute();
    $result = $stmt->get_result();
    $loan = $result->fetch_assoc();

    if ($loan['id'] > 0) {
        $sql = "
        SELECT case 
                when l.loan_type = 'Daily' then c.collection_date 
                when l.loan_type = 'Weekly' then CONCAT(DATE_FORMAT(c.collection_date,'%Y-'), WEEKOFYEAR(c.collection_date))
                when l.loan_type = 'Monthly' then DATE_FORMAT(c.collection_date, '%Y-%m')
            END dat, c.collection_date, sum(c.amount) amount FROM collections c 
        INNER JOIN loans l ON l.id = c.loan_id
        WHERE loan_id = ? and head = 'EMI' AND c.flag = 1 
        group BY 
            case 
                when l.loan_type = 'Daily' then c.collection_date 
                when l.loan_type = 'Weekly' then CONCAT(DATE_FORMAT(c.collection_date,'%Y-'), WEEKOFYEAR(c.collection_date))
                when l.loan_type = 'Monthly' then DATE_FORMAT(c.collection_date, '%Y-%m')
            END 
        ORDER by c.collection_date 
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $loan['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $collections = [];
        $late_collections = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['collection_date'] <= $loan['expiry_date']) {
                $collections[$row['dat']] = $row['amount'];
            } else {
                $late_collections[$row['collection_date']] = $row['amount'];
            }
        }
    }
}

?>

<div class="row">
    <div class="col-12">
        <!-- The icons -->

        <div class="card card-outline">
            <div class="card-header">
                <h3 class="card-title"> Collection Report</h3>
            </div>
            <div class="card-body">
                <form method="GET" accept="#">
                    <div class="row">
                        <div class="col-md-5">
                            <table class="table table-striped">
                                <tr>
                                    <td style="vertical-align: middle;">Enter Customer No</td>
                                    <td><input type="text" name="customer_no" class="form-control" value="<?= $_GET['customer_no'] ?>" /></td>
                                    <td><input type="submit" value="Submit" class="btn btn-primary" /></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </form>

                <?php
                if ($loan['id'] > 0) {
                ?>

                    <div style="width: 100%;">
                        <button onclick="printTable()" class="btn btn-secondary" style="position:relative; float: right; top:-35px">
                            <i class="bi bi-printer"></i> Print
                        </button>

                    </div>


                    <div id="printSection">

                        <table class="table table-bordered">
                            <tr>
                                <th>No</th>
                                <td><?= $loan['customer_no'] ?></td>
                                <th>Name</th>
                                <td><?= $loan['name'] ?></td>
                                <th>Loan Amount</th>
                                <td><?= $loan['amount'] ?></td>
                                <th>Opening Date</th>
                                <td><?= $loan['loan_date'] ?></td>
                                <th>End Date</th>
                                <td><?= $loan['expiry_date'] ?></td>
                            </tr>
                        </table>

                        <div class="row" style="display: flex;">

                            <div class="col-md-4">

                                <table class="table table-bordered table-33">
                                    <thead>
                                        <tr>
                                            <th style="text-align:center">Date</th>
                                            <th style="text-align:center">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $days = '+1 days';
                                        if ($loan['loan_type'] == 'Daily') {
                                            $days = '+1 days';
                                        } else if ($loan['loan_type'] == 'Weekly') {
                                            $days = '+7 days';
                                        } else if ($loan['loan_type'] == 'Monthly') {
                                            $days = '+1 months';
                                        }
                                        $i = 0;
                                        $table = 1;
                                        $opening_date = new DateTime($loan['loan_date']);
                                        $end_date = new DateTime($loan['expiry_date']);
                                        $opening_date->modify($days);
                                        while ($opening_date <= $end_date) {
                                            if ($i % 34 == 0 && $i > 0) {
                                                echo '</tbody>
                                                    </table>
                                                </div>';
                                                if ($table % 3 == 0) {
                                                    echo '</div>
                                                    <div class="row" style="display: flex;">';
                                                    $table = 0;
                                                }
                                                echo '<div class="col-md-4">
                                                <table class="table table-bordered table-33">
                                                    <thead>
                                                        <tr>
                                                            <th style="text-align:center">Date</th>
                                                            <th style="text-align:center">Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>';
                                                $table++;
                                            }
                                        ?>
                                            <tr>
                                                <td style="text-align:center"><?php
                                                                                echo $opening_date->format('d-m-Y');
                                                                                ?></td>
                                                <td align="center"><?php
                                                                    if ($loan['loan_type'] == 'Daily') {
                                                                        echo $collections[$opening_date->format('Y-m-d')];
                                                                    } elseif ($loan['loan_type'] == 'Weekly') {
                                                                        echo $collections[$opening_date->format('Y-W')];
                                                                    } elseif ($loan['loan_type'] == 'Monthly') {
                                                                        echo $collections[$opening_date->format('Y-m')];
                                                                    }
                                                                    ?></td>
                                            </tr>
                                        <?php

                                            $opening_date->modify($days);
                                            $i++;
                                        }

                                        foreach ($late_collections as $collection_date => $amount) {
                                            if ($i % 34 == 0 && $i > 0) {
                                                echo '</tbody>
                                                    </table>
                                                </div>';
                                                if ($table % 3 == 0) {
                                                    echo '</div>
                                                    <div class="row" style="display: flex;">';
                                                    $table = 0;
                                                }
                                                echo '<div class="col-md-4">
                                                <table class="table table-bordered table-33">
                                                    <thead>
                                                        <tr>
                                                            <th style="text-align:center">Date</th>
                                                            <th style="text-align:center">Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>';
                                                $table++;
                                            }
                                        ?>
                                            <tr>
                                                <td style="text-align:center"><?php
                                                                                $collection_date = new DateTime($collection_date);
                                                                                echo $collection_date->format('d-m-Y');
                                                                                ?></td>
                                                <td align="center"><?= $amount ?></td>
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
                <?php
                }
                ?>
            </div>

        </div>
    </div>
</div>


<?php
include('footer.php');
?>