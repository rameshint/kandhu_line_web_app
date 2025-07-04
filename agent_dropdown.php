<?php
require 'db.php';
$result = $conn->query("SELECT id, name FROM agents ORDER BY name ASC");
echo '<select name="agent_id" class="form-control" required>';
echo '<option value="">Select Agent</option>';
while ($row = $result->fetch_assoc()) {
    echo "<option value='{$row['id']}'>{$row['name']}</option>";
}
echo '</select>';
?>
