<?php
$title = 'Agents';
include 'header.php';
?>
<div class="row">
    <div class="col-12">
        <!-- The icons -->
        <div class="col-12">
            <div class="card card-outline">
                <div class="card-header">
                    <h3 class="card-title">Agents</h3>
                </div>
                <div class="card-body">

                    <button class="btn btn-primary mb-3" onclick="showAgentModal()">Add Agent</button>

                    <table class="table table-striped" id="agentTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Contact No</th>
                                <th>MAC Address</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="agentBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Agent Modal -->
<div class="modal fade" id="agentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="agentForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agent Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="agent_id">
                    <div class="mb-2">
                        <label>Name</label>
                        <input type="text" name="name" id="agent_name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Contact No</label>
                        <input type="text" name="contact_no" id="agent_contact" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label>Address</label>
                        <textarea name="address" id="agent_address" class="form-control"></textarea>
                    </div>
                    <div class="mb-2">
                        <label>Mobile MAC Address</label>
                        <input type="text" name="mac_address" id="agent_mac_address" class="form-control" placeholder="e.g., 00:1B:44:11:3A:B7">
                        <small class="form-text text-muted">MAC address of the authorized mobile device</small>
                    </div>
                    <div class="mb-2">
                        <label>Status</label>
                        <select name="status" id="agent_status" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" type="submit">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
include('footer.php');
?>
<script>
    $(document).ready(function() {
        loadAgents();

        $('#agentForm').on('submit', function(e) {
            e.preventDefault();
            $.post('save_agent.php', $(this).serialize(), function(res) {
                let result = JSON.parse(res);
                if (result.status === 'success') {
                    $('#agentModal').modal('hide');
                    loadAgents();
                } else {
                    alert(result.message);
                }
            });
        });
    });

    function loadAgents() {
        $.get('get_agents.php', function(data) {
            let agents = JSON.parse(data);
            let rows = '';
            agents.forEach(a => {
                rows += `
                <tr>
                    <td>${a.name}</td>
                    <td>${a.contact_no}</td>
                    <td>${a.mac_address || 'Not Set'}</td>
                    <td>${a.status == 1 ? 'Active' : 'Inactive'}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick='editAgent(${JSON.stringify(a)})'>Edit</button>
                    </td>
                </tr>
            `;
            });
            $('#agentBody').html(rows);
            $('#agentTable').DataTable();
        });
    }

    function showAgentModal() {
        $('#agentForm')[0].reset();
        $('#agent_id').val('');
        $('#agentModal').modal('show');
    }

    function editAgent(agent) {
        $('#agent_id').val(agent.id);
        $('#agent_name').val(agent.name);
        $('#agent_contact').val(agent.contact_no);
        $('#agent_address').val(agent.address);
        $('#agent_mac_address').val(agent.mac_address || '');
        $('#agent_status').val(agent.status);
        $('#agentModal').modal('show');
    }
</script>