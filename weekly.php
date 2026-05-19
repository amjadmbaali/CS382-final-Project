<?php
session_start();

include 'db_config.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id']; 

class WeeklyProgress {

    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    public function getOverallProgress($uid) {

        $query = "
        SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed
        FROM tasks
        WHERE user_id = '$uid'
        ";

        return mysqli_query($this->conn, $query);
    }

    public function getWeeklyStats($uid) {

        $query = "
        SELECT 
        DAYNAME(created_at) AS day_name,
        COUNT(*) AS total_created,

        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS total_completed,

        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS total_incomplete

        FROM tasks

        WHERE user_id = '$uid'

        GROUP BY DAYNAME(created_at)

        ORDER BY FIELD(
            day_name,
            'Sunday',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday'
        )
        ";

        return mysqli_query($this->conn, $query);
    }
}

$weekly = new WeeklyProgress($conn);

$progressQuery = $weekly->getOverallProgress($uid);
$progressRow = mysqli_fetch_assoc($progressQuery);
$totalTasks = $progressRow['total'];
$completedTasks = $progressRow['completed'];
$pending_tasks = $totalTasks - $completedTasks;
$overallRate = ($totalTasks > 0)
? round(($completedTasks / $totalTasks) * 100)
: 0;

$statsResult = $weekly->getWeeklyStats($uid);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Progress - YIC To-Do List</title>

    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>

<div class="container">

    <aside class="sidebar">

        <div class="logo">
            <img src="YICLogo.jpg" alt="YIC Logo" class="logo-img">
            <h3>YIC To-Do</h3>
        </div>

        <nav>
            <ul>
                <li class="nav-item">
                    <a href="index.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a href="tasks.php">
                        <i class="fas fa-tasks"></i> My Tasks
                    </a>
                </li>

                <li class="nav-item active">
                    <a href="weekly.php">
                        <i class="fas fa-chart-line"></i> Weekly Progress
                    </a>
                </li>

                <li class="nav-item">
                    <a href="notification.php">
                        <i class="fas fa-bell"></i> Notifications
                    </a>
                </li>

                <li class="nav-item logout-item">
                    <a href="logout.php" style="color: #ff8a80;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>

            </ul>
        </nav>

    </aside>
    <main class="main-content">

        <header>
            <div class="header-title">
                <h1>Weekly Performance</h1>
                <p>Detailed analysis of your task completion rates</p>
            </div>
        </header>
        <section class="weekly-analysis">

            <div class="chart-box">

                <h3>Overall Completion Rate</h3>

                <div class="progress-bar-container">

                    <div class="progress-fill"
                    style="width: <?php echo $overallRate; ?>%;">

                    <?php echo $overallRate; ?>% Achievement

                    </div>

                </div>

            </div>
            <div class="table-container">

                <h2>Weekly Activity Log</h2>

                <table>

                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Tasks Created</th>
                            <th>Completed</th>
                            <th>Incomplete</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody id="weekly-table">

                    <?php if (mysqli_num_rows($statsResult) > 0): ?>

                        <?php while($row = mysqli_fetch_assoc($statsResult)): ?>

                            <?php

                            if ($row['total_incomplete'] == 0) {

                                $badgeClass = "perfect";
                                $badgeText = "Excellent";

                            }

                            elseif ($row['total_completed'] >= $row['total_incomplete']) {

                                $badgeClass = "warning";
                                $badgeText = "Good Progress";

                            }

                            else {

                                $badgeClass = "danger";
                                $badgeText = "Action Required";

                            }

                            ?>

                            <tr>
                                <td><?php echo $row['day_name']; ?></td>

                                <td><?php echo $row['total_created']; ?></td>

                                <td class="text-complete">
                                    <?php echo $row['total_completed']; ?>
                                </td>

                                <td class="text-incomplete">
                                    <?php echo $row['total_incomplete']; ?>
                                </td>

                                <td>
                                    <span class="status-badge <?php echo $badgeClass; ?>">
                                        <?php echo $badgeText; ?>
                                    </span>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" style="color: #9e9e9e;">
                                No database activity recorded for this week yet.
                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>

</body>
</html>