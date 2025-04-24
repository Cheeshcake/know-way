<?php
// admin-statistics.php - Admin Statistics Page

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    // Redirect non-admin users
    header('Location: dashboard.php');
    exit;
}

include 'db.php';

// Get time period filter
$period = isset($_GET['period']) ? $_GET['period'] : 'month';

// Sample data for statistics (in a real app, this would come from database)
$statistics = [
    'users' => [
        'total' => 217,
        'active' => 185,
        'new' => [
            'today' => 3,
            'week' => 15,
            'month' => 42,
            'year' => 217
        ],
        'growth' => [
            'week' => 8.5,
            'month' => 12.3,
            'year' => 45.7
        ]
    ],
    'courses' => [
        'total' => 24,
        'active' => 20,
        'completed' => 548,
        'average_rating' => 4.8,
        'categories' => [
            'HTML' => 5,
            'CSS' => 4,
            'JavaScript' => 6,
            'PHP' => 3,
            'Design' => 4,
            'Marketing' => 2
        ]
    ],
    'engagement' => [
        'average_completion_rate' => 68,
        'average_time_spent' => '3h 45m',
        'most_active_day' => 'Wednesday',
        'peak_hours' => '18:00 - 21:00'
    ],
    'revenue' => [
        'total' => '€24,850',
        'month' => '€3,250',
        'average_per_user' => '€114.52'
    ]
];

// Chart data
$monthlyUsers = [42, 38, 55, 48, 65, 58, 70, 75, 62, 80, 85, 90];
$monthlyCompletions = [28, 32, 40, 35, 50, 45, 55, 60, 48, 65, 70, 75];
$categoryDistribution = [
    'HTML' => 25,
    'CSS' => 20,
    'JavaScript' => 30,
    'PHP' => 15,
    'Design' => 20,
    'Marketing' => 10
];
$userRetention = [100, 85, 75, 68, 62, 58, 55, 52, 50, 48, 47, 45];

// Monthly labels
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Get admin initials
$admin_initials = 'A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay Admin - Statistics</title>
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="stylesheet" href="statistics.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container" id="adminContainer">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h1 class="logo">KnowWay</h1>
                <p class="admin-label">Admin Panel</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="admin.php"><i class="fas fa-th-large"></i>Dashboard</a></li>
                    <li><a href="admin-courses.php"><i class="fas fa-book"></i>Courses</a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i>Users</a></li>
                    <li class="active"><a href="admin-statistics.php"><i class="fas fa-chart-bar"></i>Statistics</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i>Settings</a></li>
                    <li><a href="dashboard.php"><i class="fas fa-graduation-cap"></i>Student Dashboard</a></li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                    <h2>Statistics</h2>
                </div>
                
                <div class="header-right">
                    <div class="user-profile">
                        <div class="user-avatar"><?php echo htmlspecialchars($admin_initials); ?></div>
                        <span class="user-name">Admin</span>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <!-- Time Period Filter -->
                <div class="stats-filter">
                    <div class="filter-label">Time Period:</div>
                    <div class="filter-options">
                        <a href="?period=week" class="filter-option <?php echo $period === 'week' ? 'active' : ''; ?>">Week</a>
                        <a href="?period=month" class="filter-option <?php echo $period === 'month' ? 'active' : ''; ?>">Month</a>
                        <a href="?period=year" class="filter-option <?php echo $period === 'year' ? 'active' : ''; ?>">Year</a>
                        <a href="?period=all" class="filter-option <?php echo $period === 'all' ? 'active' : ''; ?>">All Time</a>
                    </div>
                    
                    <button class="export-btn"><i class="fas fa-download"></i> Export Data</button>
                </div>
                
                <!-- Key Metrics -->
                <div class="stats-overview">
                    <div class="stat-card">
                        <div class="stat-icon users-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Total Users</h3>
                            <p class="stat-number"><?php echo $statistics['users']['total']; ?></p>
                            <p class="stat-growth positive">
                                <i class="fas fa-arrow-up"></i> 
                                <?php echo $statistics['users']['growth'][$period === 'all' ? 'year' : $period]; ?>% 
                                <span class="growth-period">this <?php echo $period; ?></span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon courses-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Total Courses</h3>
                            <p class="stat-number"><?php echo $statistics['courses']['total']; ?></p>
                            <p class="stat-detail"><?php echo $statistics['courses']['active']; ?> active courses</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon completions-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Course Completions</h3>
                            <p class="stat-number"><?php echo $statistics['courses']['completed']; ?></p>
                            <p class="stat-detail"><?php echo $statistics['engagement']['average_completion_rate']; ?>% completion rate</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon revenue-icon">
                            <i class="fas fa-euro-sign"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Total Revenue</h3>
                            <p class="stat-number"><?php echo $statistics['revenue']['total']; ?></p>
                            <p class="stat-detail"><?php echo $statistics['revenue']['month']; ?> this month</p>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Section -->
                <div class="charts-container">
                    <!-- User Growth Chart -->
                    <div class="chart-card large">
                        <div class="chart-header">
                            <h3>User Growth & Course Completions</h3>
                            <div class="chart-actions">
                                <button class="chart-action-btn" id="toggleDataBtn">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="chart-action-btn">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="userGrowthChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Course Category Distribution -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Course Categories</h3>
                        </div>
                        <div class="chart-body">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- User Retention -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>User Retention</h3>
                        </div>
                        <div class="chart-body">
                            <canvas id="retentionChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Engagement Metrics -->
                <div class="engagement-section">
                    <h3 class="section-title">Engagement Metrics</h3>
                    
                    <div class="engagement-metrics">
                        <div class="metric-card">
                            <div class="metric-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="metric-content">
                                <h4>Average Time Spent</h4>
                                <p class="metric-value"><?php echo $statistics['engagement']['average_time_spent']; ?></p>
                                <p class="metric-label">per user per week</p>
                            </div>
                        </div>
                        
                        <div class="metric-card">
                            <div class="metric-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="metric-content">
                                <h4>Most Active Day</h4>
                                <p class="metric-value"><?php echo $statistics['engagement']['most_active_day']; ?></p>
                                <p class="metric-label">highest user activity</p>
                            </div>
                        </div>
                        
                        <div class="metric-card">
                            <div class="metric-icon">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="metric-content">
                                <h4>Peak Hours</h4>
                                <p class="metric-value"><?php echo $statistics['engagement']['peak_hours']; ?></p>
                                <p class="metric-label">highest engagement</p>
                            </div>
                        </div>
                        
                        <div class="metric-card">
                            <div class="metric-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="metric-content">
                                <h4>Average Rating</h4>
                                <p class="metric-value"><?php echo $statistics['courses']['average_rating']; ?>/5.0</p>
                                <p class="metric-label">across all courses</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Top Performing Content -->
                <div class="top-content-section">
                    <h3 class="section-title">Top Performing Content</h3>
                    
                    <div class="top-content-grid">
                        <div class="content-card">
                            <h4>Top Courses by Enrollment</h4>
                            <ul class="ranking-list">
                                <li class="ranking-item">
                                    <span class="rank">1</span>
                                    <span class="item-name">JavaScript Fundamentals</span>
                                    <span class="item-value">245 enrollments</span>
                                </li>
                                <li class="ranking-item">
                                    <span class="rank">2</span>
                                    <span class="item-name">Advanced CSS Techniques</span>
                                    <span class="item-value">198 enrollments</span>
                                </li>
                                <li class="ranking-item">
                                    <span class="rank">3</span>
                                    <span class="item-name">PHP for Beginners</span>
                                    <span class="item-value">176 enrollments</span>
                                </li>
                                <li class="ranking-item">
                                    <span class="rank">4</span>
                                    <span class="item-name">Responsive Web Design</span>
                                    <span class="item-value">154 enrollments</span>
                                </li>
                                <li class="ranking-item">
                                    <span class="rank">5</span>
                                    <span class="item-name">HTML5 Essentials</span>
                                    <span class="item-value">132 enrollments</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="content-card">
                            <h4>Top Courses by Completion Rate</h4>
                            <ul class="ranking-list">
                                <li class="ranking-item">
                                    <span class="rank">1</span>
                                    <span class="item-name">HTML5 Essentials</span>
                                    <span class="item-value">92%</span>
                                </li>
                                <li class="ranking-item">
                                    <span class="rank">2</span>
                                    <span class="item-name">Introduction to Web Development</span>
                                    <span class="item-value">87%</span>
                                </li>
                                <li class="ranking-item">
                                    <span class="rank">3</span>
                                    <span class="item-name">CSS Basics</span>
                                    <span class="item-value">85%</span>
                                </li>
                                <li class="ranking-item">
                                    <span class="rank">4</span>
                                    <span class="item-name">JavaScript Fundamentals</span>
                                    <span class="item-value">78%</span>
                                </li>
                                <li class="ranking-item">
                                    <span class="rank">5</span>
                                    <span class="item-name">PHP for Beginners</span>
                                    <span class="item-value">75%</span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="content-card">
                            <h4>Top Courses by Rating</h4>
                            <ul class="ranking-list">
                                <li class="ranking-item">
                                    <span class="rank">1</span>
                                    <span class="item-name">Advanced JavaScript</span>
                                    <span class="item-value">4.9 <i class="fas fa-star"></i></span>
                                </li>
                                <li class="ranking-item">
                                    <span class="rank">2</span>
                                    <span class="item-name">React Fundamentals</span>
                                    <span class="item-value">4.8 <i class="fas fa-star"></i></span>
                                </li>
                                <li class="ranking-item">
                                    <span class="rank">3</span>
                                    <span class="item-name">PHP Object-Oriented Programming</span>
                                    <span class="item-value">4.8 <i class="fas fa-star"></i></span>
                                </li>
                                <li class="ranking-item">
                                    <span class="rank">4</span>
                                    <span class="item-name">CSS Animations</span>
                                    <span class="item-value">4.7 <i class="fas fa-star"></i></span>
                                </li>
                                <li class="ranking-item">
                                    <span class="rank">5</span>
                                    <span class="item-name">Responsive Web Design</span>
                                    <span class="item-value">4.7 <i class="fas fa-star"></i></span>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="content-card">
                            <h4>Most Active Users</h4>
                            <ul class="ranking-list">
                                <li class="ranking-item">
                                    <span class="rank">1</span>
                                    <span class="item-name">Sophie Martin</span>
                                    <span class="item-value">32h weekly</span>
                                </li>
                                <li class="ranking-item">
                                    <span class="rank">2</span>
                                    <span class="item-name">Thomas Dubois</span>
                                    <span class="item-value">28h weekly</span>
                                </li>
                                <li class="ranking-item">
                                    <span class="rank">3</span>
                                    <span class="item-name">Lucas Bernard</span>
                                    <span class="item-value">25h weekly</span>
                                </li>
                                <li class="ranking-item">
                                    <span class="rank">4</span>
                                    <span class="item-name">Emma Leroy</span>
                                    <span class="item-value">22h weekly</span>
                                </li>
                                <li class="ranking-item">
                                    <span class="rank">5</span>
                                    <span class="item-name">Chloé Petit</span>
                                    <span class="item-value">20h weekly</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar
            const menuToggle = document.getElementById('menuToggle');
            const adminContainer = document.getElementById('adminContainer');
            const sidebar = document.getElementById('sidebar');
            
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                adminContainer.classList.toggle('sidebar-collapsed');
            });
            
            // User Growth Chart
            const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
            const userGrowthChart = new Chart(userGrowthCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($months); ?>,
                    datasets: [
                        {
                            label: 'New Users',
                            data: <?php echo json_encode($monthlyUsers); ?>,
                            borderColor: '#4361ee',
                            backgroundColor: 'rgba(67, 97, 238, 0.1)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Course Completions',
                            data: <?php echo json_encode($monthlyCompletions); ?>,
                            borderColor: '#3bc9db',
                            backgroundColor: 'rgba(59, 201, 219, 0.1)',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
            
            // Category Distribution Chart
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            const categoryChart = new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(<?php echo json_encode($categoryDistribution); ?>),
                    datasets: [{
                        data: Object.values(<?php echo json_encode($categoryDistribution); ?>),
                        backgroundColor: [
                            '#4361ee',
                            '#3bc9db',
                            '#f72585',
                            '#4cc9f0',
                            '#7209b7',
                            '#4895ef'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    },
                    cutout: '70%'
                }
            });
            
            // User Retention Chart
            const retentionCtx = document.getElementById('retentionChart').getContext('2d');
            const retentionChart = new Chart(retentionCtx, {
                type: 'bar',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7', 'Week 8', 'Week 9', 'Week 10', 'Week 11', 'Week 12'],
                    datasets: [{
                        label: 'User Retention (%)',
                        data: <?php echo json_encode($userRetention); ?>,
                        backgroundColor: '#4cc9f0',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
            
            // Toggle data visibility
            const toggleDataBtn = document.getElementById('toggleDataBtn');
            if (toggleDataBtn) {
                toggleDataBtn.addEventListener('click', function() {
                    const isVisible = userGrowthChart.isDatasetVisible(1);
                    if (isVisible) {
                        userGrowthChart.hide(1);
                        toggleDataBtn.innerHTML = '<i class="fas fa-eye-slash"></i>';
                    } else {
                        userGrowthChart.show(1);
                        toggleDataBtn.innerHTML = '<i class="fas fa-eye"></i>';
                    }
                });
            }
            
            // Export button functionality
            const exportBtn = document.querySelector('.export-btn');
            if (exportBtn) {
                exportBtn.addEventListener('click', function() {
                    alert('This would export the statistics data in a real application.');
                });
            }
        });
    </script>
</body>
</html>
