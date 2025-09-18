<?php
// Create collections_temp table for temporary storage of collections from Firebase
require_once 'db.php';

$sql = "
CREATE TABLE IF NOT EXISTS `collections_temp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `collection_date` date NOT NULL,
  `collection_time` time DEFAULT NULL,
  `head` varchar(50) NOT NULL DEFAULT 'EMI',
  `amount` decimal(10,2) NOT NULL,
  `firebase_collection_id` varchar(255) DEFAULT NULL,
  `sync_status` enum('synced','moved') DEFAULT 'synced',
  `created_on` datetime DEFAULT CURRENT_TIMESTAMP,
  `moved_on` datetime DEFAULT NULL,
  `moved_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loan_id` (`loan_id`),
  KEY `agent_id` (`agent_id`),
  KEY `collection_date` (`collection_date`),
  KEY `sync_status` (`sync_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
    $conn->query($sql);
    echo "<h1>Collections Temp Table Created Successfully!</h1>";
    echo "<p>The table structure has been created with the following columns:</p>";
    echo "<ul>";
    echo "<li>id - Auto increment primary key</li>";
    echo "<li>loan_id - Reference to loans table</li>";
    echo "<li>agent_id - Reference to agents table</li>";
    echo "<li>collection_date - Date of collection</li>";
    echo "<li>collection_time - Time of collection</li>";
    echo "<li>head - Collection type (default: EMI)</li>";
    echo "<li>amount - Collection amount</li>";
    echo "<li>firebase_collection_id - Firebase reference ID</li>";
    echo "<li>sync_status - Status (synced/moved)</li>";
    echo "<li>created_on - Timestamp when record created</li>";
    echo "<li>moved_on - Timestamp when moved to main table</li>";
    echo "<li>moved_by - User who moved the record</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
