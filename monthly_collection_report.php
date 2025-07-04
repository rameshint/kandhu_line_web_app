<?php
$title = 'Report -  Monthly Collection';
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
    $sql = "select f.* , g.late_pay, case when DATE_FORMAT(CURDATE(), '%Y-%m-01') <= ? then if(IFNULL(j.id,0)>0,1,0) ELSE 0 END due_delay from (
            SELECT DISTINCT l.id, c.customer_no,c.name,l.amount,l.loan_date, l.expiry_date,l.status, 
            case when l.expiry_date IS NOT NULL and l.status = 'Open' then if(l.expiry_date<CURRENT_DATE,1,0) ELSE 0 END overdue FROM loans l
            INNER JOIN customers c ON c.id = l.customer_id
            WHERE (? BETWEEN l.loan_date AND IFNULL(l.loan_closed,l.expiry_date)
            OR ? BETWEEN l.loan_date AND IFNULL(l.loan_closed,l.expiry_date))
            and l.flag = 1
            and l.loan_type = 'Daily'
            UNION 
            SELECT DISTINCT l.id,c.customer_no,c.name,l.amount,l.loan_date, l.expiry_date,l.status, 
            case when l.expiry_date IS NOT NULL and l.status = 'Open' then if(l.expiry_date<CURRENT_DATE,1,0) ELSE 0 END overdue FROM collections cs
            inner join loans l ON l.id = cs.loan_id and l.loan_type = 'Daily'
            INNER JOIN customers c ON c.id = l.customer_id
            WHERE cs.collection_date BETWEEN  ? AND ? and cs.flag = 1 
            ORDER BY 2
            ) f 
            left join (SELECT l.id,  (datediff(CURRENT_DATE , l.loan_date) * l.amount / l.tenure - ((l.tenure/100)*5) * l.amount / l.tenure) - ifnull(c.collected,0) > 0 late_pay FROM loans l
            LEFT JOIN (SELECT loan_id, SUM(amount) collected FROM collections WHERE flag = 1 GROUP BY loan_id) c ON c.loan_id = l.id
            WHERE l.flag = 1 AND l.`status` = 'Open' AND l.loan_type='Daily' AND l.expiry_date > current_date) g on f.id = g.id
            LEFT JOIN (SELECT l.id FROM loans l
            WHERE l.loan_type = 'Daily' AND l.`status` = 'Open'
            AND not EXISTS(SELECT 1 FROM collections c WHERE c.loan_id = l.id AND c.flag = 1 and c.collection_date >= DATE_SUB(CURRENT_DATE, INTERVAL 2 DAY))) j ON j.id = f.id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $st, $st, $et, $st, $et);
    $stmt->execute();
    $result = $stmt->get_result();
    $loans = [];
    while ($row = $result->fetch_assoc()) {
        $loans[] = $row;
    }

    $sql = "
        SELECT c.loan_id, DATE_FORMAT(c.collection_date,'%d') collection_date,SUM(c.amount) amount 
        FROM collections c 
        INNER JOIN loans l ON l.id = c.loan_id AND l.loan_type = 'Daily'
        WHERE c.flag = 1 AND collection_date BETWEEN  ? AND ? GROUP BY loan_id, DATE_format(collection_date,'%d') 
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $st, $et);
    $stmt->execute();
    $result = $stmt->get_result();
    $collections = [];
    $date_wise_collections = [];
    while ($row = $result->fetch_assoc()) {
        $collections[$row['loan_id']][$row['collection_date']] = $row['amount'];
        $date_wise_collections[$row['collection_date']]  += $row['amount'];
    }
}

?>
<div class="row">
    <div class="col-12">
        <!-- The icons -->

        <div class="card card-outline">
            <div class="card-header">
                <h3 class="card-title"> Monthly Collection Report</h3>
            </div>
            <div class="card-body">
                <form method="GET" accept="#">
                    <div class="row">

                        <div class="col-md-5">
                            <table class="table table-striped">
                                <tr>
                                    <td style="vertical-align: middle;">Select Month</td>
                                    <td><input type="month" name="month" class="form-control" value="<?= $month ?>" /></td>
                                    <td><input type="submit" value="Submit" class="btn btn-primary" /></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </form>

                <?php
                if (count($loans)) {
                ?>
                    <div style="width: 100%;">
                        <!--<button onclick="printTable()" class="btn btn-secondary" style="position:relative; float: right;">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <br />-->

                    </div>

                    <br />
                    <div id="printSection">
                        <!--Month : <b><?= $start_date->format('Y-M') ?></b>-->
                        <table class="table table-sm table-bordered display nowrap" id="report_table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Amount</th>
                                    <th>Opening Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <?php
                                    $start = clone $start_date;
                                    while ($start <= $end_date) {
                                        echo '<th style="text-align:center">' . $start->format('d') . '<br />' . $start->format('D') . '</th>';
                                        $start->modify('+1 days');
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                $start_date->format("Y-m-d");

                                foreach ($loans as $loan) {
                                    $class = '';
                                    if ($loan['overdue']) {
                                        $class = 'table-danger';
                                    }

                                    if ($loan['due_delay']) {
                                        $class = 'table-info';
                                    }

                                    if ($loan['late_pay']) {
                                        $class = 'table-warning';
                                    }

                                    $loan_date_obj = new DateTime($loan['loan_date']);
                                    $expiry_date_obj = new DateTime($loan['expiry_date']);
                                ?>
                                    <tr class="<?= $class ?>">
                                        <td><?= $i++ ?></td>
                                        <td><?= $loan['customer_no'] ?></td>
                                        <td style="white-space: nowrap"><?= $loan['name'] ?></td>
                                        <td><?= $loan['amount'] ?></td>
                                        <td style="white-space: nowrap"><?= $loan_date_obj->format('d-m-Y') ?></td>
                                        <td style="white-space: nowrap"><?= $expiry_date_obj->format('d-m-Y') ?></td>
                                        <td style="white-space: nowrap"><?= $loan['status'] ?></td>
                                        <?php
                                        $start = clone $start_date;
                                        while ($start <= $end_date) {
                                            if ($collections[$loan['id']][$start->format('d')] > 0) {
                                                echo '<td align=center>' . formatToIndianCurrency($collections[$loan['id']][$start->format('d')]) . '</td>';
                                            } else {
                                                if ($start <= $current_date && $start->format('Y-m-d') >= $loan['loan_date'] && $start->format('Y-m-d') <= $loan['expiry_date'])
                                                    echo '<td align=center>-</td>';
                                                else
                                                    echo '<td align=center></td>';
                                            }
                                            $start->modify('+1 days');
                                        }
                                        ?>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>Total</td>
                                    <?php
                                    $start = clone $start_date;
                                    while ($start <= $end_date) {
                                        if ($date_wise_collections[$start->format('d')] > 0) {
                                            echo '<td align=center>' . formatToIndianCurrency($date_wise_collections[$start->format('d')]) . '</td>';
                                        } else {
                                            echo '<td align=center>0.00</td>';
                                        }

                                        $start->modify('+1 days');
                                    }
                                    ?>
                                </tr>
                            </tfoot>
                        </table>
                        <div class="col-md-3">
                        <span class="badge text-bg-info">Due Delay</span>    
                        <span class="badge text-bg-warning">Late Pay</span>
                            <span class="badge text-bg-danger">Overdue</span>
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
<script>
    $(document).ready(function() {
        $("#report_table").DataTable({
            scrollX: true,
            scrollCollapse: true,
            fixedColumns: {
                leftColumns: 7
            }
        });
    })
</script>