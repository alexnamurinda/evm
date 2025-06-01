
<?php
// // admin_dashboard.php - Protected admin dashboard
session_start();

// Check if admin is authenticated
if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
    // Redirect to login page if not authenticated
    header("Location: admin_login.php");
    exit;
}

// Continue with admin dashboard content
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVM Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .dashboard-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .header {
            margin-bottom: 30px;
        }
        .nav-tabs {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-container">
            <div class="header d-flex justify-content-between align-items-center">
                <h2>EVM Admin Dashboard</h2>
                <a href="logout.php" class="btn btn-outline-danger">Logout</a>
            </div>
           
            <ul class="nav nav-tabs" id="adminTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="results-tab" data-toggle="tab" href="#results" role="tab">Voting Results</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="manage-tab" data-toggle="tab" href="#manage" role="tab">Manage Candidates</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="voters-tab" data-toggle="tab" href="#voters" role="tab">Registered Voters</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="settings-tab" data-toggle="tab" href="#settings" role="tab">System Settings</a>
                </li>
            </ul>
           
            <div class="tab-content" id="adminTabContent">
                <div class="tab-pane fade show active" id="results" role="tabpanel">
                    <h4>Election Results</h4>
                    <!-- Results content will be loaded here -->
                    <div id="results-content">
                        Loading results...
                    </div>
                </div>
               
                <div class="tab-pane fade" id="manage" role="tabpanel">
                    <h4>Manage Candidates</h4>
                    <!-- Candidate management interface -->
                </div>
               
                <div class="tab-pane fade" id="voters" role="tabpanel">
                    <h4>Registered Voters</h4>
                    <!-- Voter management interface -->
                </div>
               
                <div class="tab-pane fade" id="settings" role="tabpanel">
                    <h4>System Settings</h4>
                    <!-- Settings interface -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load voting results
            $.get('get_results.php', function(data) {
                $('#results-content').html(data);
            });
        });
    </script>
</body>
</html>
