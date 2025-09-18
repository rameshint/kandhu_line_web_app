<?php
$title = 'Mobile App Sync';
include('header.php');
?>

<!-- Mobile App Sync Dashboard -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Individual Sync Operations</h3>
            </div>
            <div class="card-body">
                <button class="btn btn-primary" onclick="syncData('sync_agents')">Sync Agents to Firebase</button>
                <button class="btn btn-primary" onclick="syncData('sync_pending_loans')">Sync Pending Loans to Firebase</button>
                <button class="btn btn-warning" onclick="syncData('sync_collections')">Sync Collections from Mobile App</button>
                <button class="btn btn-info" onclick="syncData('reset_daily_collections')">Reset Daily Collections</button>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Full Sync</h3>
            </div>
            <div class="card-body">
                <button class="btn btn-success" onclick="syncData('full_sync')">Perform Full Sync</button>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Admin: Collection Management</h3>
            </div>
            <div class="card-body">
                <button class="btn btn-warning" onclick="loadPendingCollections()">View Pending Collections</button>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div id="pendingCollections"></div>
        <div id="result"></div>
    </div>
</div>

<style>
    .result {
        margin-top: 20px;
        padding: 15px;
        border-radius: 4px;
    }

    .success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }

    .error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
</style>

<?php
include('footer.php');
?>

<script>
    function syncData(action) {
        const resultDiv = document.getElementById('result');
        resultDiv.innerHTML = '<div class="result">Syncing... Please wait.</div>';

        // Using fetch with POST method for better security
        fetch('firebase_sync_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${action}&line=Daily`
            })
            .then(response => response.json())
            .then(data => {
                const className = data.success ? 'success' : 'error';
                resultDiv.innerHTML = `<div class="result ${className}">
                        <h4>Sync Result</h4>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    </div>`;
            })
            .catch(error => {
                resultDiv.innerHTML = `<div class="result error">
                        <h4>Error</h4>
                        <p>${error.message}</p>
                    </div>`;
            });
    }

    function loadPendingCollections() {
        const pendingDiv = document.getElementById('pendingCollections');
        pendingDiv.innerHTML = '<div class="result">Loading pending collections...</div>';

        fetch('firebase_sync_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_pending_collections'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = '<h4>Pending Collections Summary</h4>';
                    if (data.pending_collections.length > 0) {
                        html += '<table style="width:100%; border-collapse: collapse; margin-top: 10px;">';
                        html += '<tr style="background: #f8f9fa; border: 1px solid #dee2e6;"><th style="padding: 8px; border: 1px solid #dee2e6;">Date</th><th style="padding: 8px; border: 1px solid #dee2e6;">Collections</th><th style="padding: 8px; border: 1px solid #dee2e6;">Total Amount</th><th style="padding: 8px; border: 1px solid #dee2e6;">Agents</th><th style="padding: 8px; border: 1px solid #dee2e6;">Actions</th></tr>';

                        data.pending_collections.forEach(row => {
                            html += `<tr style="border: 1px solid #dee2e6;">
                                    <td style="padding: 8px; border: 1px solid #dee2e6;">${row.collection_date}</td>
                                    <td style="padding: 8px; border: 1px solid #dee2e6;">${row.collection_count}</td>
                                    <td style="padding: 8px; border: 1px solid #dee2e6;">₹${parseFloat(row.total_amount).toFixed(2)}</td>
                                    <td style="padding: 8px; border: 1px solid #dee2e6;">${row.agents || 'N/A'}</td>
                                    <td style="padding: 8px; border: 1px solid #dee2e6;">
                                        <button class="btn btn-primary btn-sm" style="margin-right: 5px;" onclick="viewCollectionDetails('${row.collection_date}')">View Details</button>
                                        <button class="btn btn-success btn-sm" onclick="moveCollections('${row.collection_date}')">Move to Main</button>
                                    </td>
                                </tr>`;
                        });
                        html += '</table>';
                    } else {
                        html += '<p>No pending collections found.</p>';
                    }
                    pendingDiv.innerHTML = html;
                } else {
                    pendingDiv.innerHTML = `<div class="result error"><p>${data.error}</p></div>`;
                }
            })
            .catch(error => {
                pendingDiv.innerHTML = `<div class="result error"><p>${error.message}</p></div>`;
            });
    }

    function viewCollectionDetails(date) {
        const resultDiv = document.getElementById('result');
        resultDiv.innerHTML = '<div class="result">Loading collection details...</div>';

        fetch('firebase_sync_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=get_collections_by_date&date=${date}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = `<h4>Collections for ${date}</h4>`;
                    if (data.collections.length > 0) {
                        html += '<table style="width:100%; border-collapse: collapse; margin-top: 10px; font-size: 14px;">';
                        html += '<tr style="background: #f8f9fa; border: 1px solid #dee2e6;"><th style="padding: 6px; border: 1px solid #dee2e6;">Customer</th><th style="padding: 6px; border: 1px solid #dee2e6;">Agent</th><th style="padding: 6px; border: 1px solid #dee2e6;">Loan Type</th><th style="padding: 6px; border: 1px solid #dee2e6;">Amount</th><th style="padding: 6px; border: 1px solid #dee2e6;">Time</th></tr>';

                        data.collections.forEach(collection => {
                            html += `<tr style="border: 1px solid #dee2e6;">
                                    <td style="padding: 6px; border: 1px solid #dee2e6;">${collection.customer_no} - ${collection.customer_name}</td>
                                    <td style="padding: 6px; border: 1px solid #dee2e6;">${collection.agent_name}</td>
                                    <td style="padding: 6px; border: 1px solid #dee2e6;">${collection.loan_type}</td>
                                    <td style="padding: 6px; border: 1px solid #dee2e6;">₹${parseFloat(collection.amount).toFixed(2)}</td>
                                    <td style="padding: 6px; border: 1px solid #dee2e6;">${collection.collection_time}</td>
                                </tr>`;
                        });
                        html += '</table>';
                    } else {
                        html += '<p>No collections found for this date.</p>';
                    }
                    resultDiv.innerHTML = `<div class="result success">${html}</div>`;
                } else {
                    resultDiv.innerHTML = `<div class="result error"><p>${data.error}</p></div>`;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `<div class="result error"><p>${error.message}</p></div>`;
            });
    }

    function moveCollections(date) {
        if (!confirm(`Are you sure you want to move all collections for ${date} to the main table?`)) {
            return;
        }

        const resultDiv = document.getElementById('result');
        resultDiv.innerHTML = '<div class="result">Moving collections...</div>';

        fetch('firebase_sync_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=move_collections&date=${date}`
            })
            .then(response => response.json())
            .then(data => {
                const className = data.success ? 'success' : 'error';
                resultDiv.innerHTML = `<div class="result ${className}">
                        <h4>Move Collections Result</h4>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    </div>`;

                // Refresh pending collections if successful
                if (data.success) {
                    setTimeout(() => {
                        loadPendingCollections();
                    }, 1000);
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `<div class="result error">
                        <h4>Error</h4>
                        <p>${error.message}</p>
                    </div>`;
            });
    }

    // Auto-load pending collections on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadPendingCollections();
    });
</script>