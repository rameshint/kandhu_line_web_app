<?php
$title = 'Report -  Monthly Expenses';
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
    

    $sql = "
        SELECT DATE_FORMAT(e.expense_date,'%d') dat, category,SUM(amount) amount 
        FROM expenses e 
        WHERE e.flag = 1 and e.expense_date BETWEEN ? AND ? 
        GROUP BY DATE_FORMAT(e.expense_date,'%d'), category 
        ORDER BY DATE_FORMAT(e.expense_date,'%d'), category 
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $st, $et);
    $stmt->execute();
    $result = $stmt->get_result();
    $expenses = [];
    $date_wise_expenses = [];
    
    while ($row = $result->fetch_assoc()) {
        $expenses[$row['category']][$row['dat']] = $row['amount'];
        $date_wise_expenses[$row['dat']]  += $row['amount'];
    }
}

?>
<div class="row">
    <div class="col-12">
        <!-- The icons -->

        <div class="card card-outline">
            <div class="card-header">
                <h3 class="card-title"> Monthly Expenses Report</h3>
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
                if (count($expenses)) {
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
                                    <th>Category</th>
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
                                foreach ($expenses as $category => $dates) {
                                    
                                ?>
                                    <tr >
                                        <td><?= $i++ ?></td>
                                        <td><?= $category ?></td>
                                        <?php
                                        $start = clone $start_date;
                                        while ($start <= $end_date) {
                                            if ($dates[$start->format('d')] > 0) {
                                                echo '<td align=center>' . formatToIndianCurrency($dates[$start->format('d')]) . '</td>';
                                            } else{
                                                echo '<td></td>';
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
                                    <td>Total</td>
                                    <?php
                                    $start = clone $start_date;
                                    while ($start <= $end_date) {
                                        if ($date_wise_expenses[$start->format('d')] > 0) {
                                            echo '<td align=center>' . formatToIndianCurrency($date_wise_expenses[$start->format('d')]) . '</td>';
                                        } else {
                                            echo '<td align=center>0.00</td>';
                                        }

                                        $start->modify('+1 days');
                                    }
                                    ?>
                                </tr>
                            </tfoot>
                        </table>
                       
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
                leftColumns: 2
            }
        });
    })
</script>