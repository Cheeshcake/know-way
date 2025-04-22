<?php
// Connexion à la base de données (si nécessaire)
include 'db.php';

// Récupération des paramètres de recherche et filtrage
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$page = $_GET['page'] ?? 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Requête pour récupérer les utilisateurs
$sql = "SELECT * FROM users WHERE 
        (name LIKE '%$search%' OR email LIKE '%$search%')";

if ($filter !== 'all') {
    $sql .= " AND status = '$filter'";
}

$sql .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";

// Exécution de la requête (commentée pour l'exemple)
// $result = $conn->query($sql);

// Données fictives pour l'exemple
$usersData = [
    [
        'id' => 1,
        'name' => 'Sophie Martin',
        'email' => 'sophie.martin@example.com',
        'status' => 'active',
        'enrolledCourses' => 3,
        'lastActive' => '15/04/2023',
    ],
    [
        'id' => 2,
        'name' => 'Thomas Dubois',
        'email' => 'thomas.dubois@example.com',
        'status' => 'active',
        'enrolledCourses' => 2,
        'lastActive' => '14/04/2023',
    ],
    [
        'id' => 3,
        'name' => 'Emma Leroy',
        'email' => 'emma.leroy@example.com',
        'status' => 'inactive',
        'enrolledCourses' => 1,
        'lastActive' => '20/03/2023',
    ],
    [
        'id' => 4,
        'name' => 'Lucas Bernard',
        'email' => 'lucas.bernard@example.com',
        'status' => 'active',
        'enrolledCourses' => 5,
        'lastActive' => '12/04/2023',
    ],
    [
        'id' => 5,
        'name' => 'Chloé Petit',
        'email' => 'chloe.petit@example.com',
        'status' => 'pending',
        'enrolledCourses' => 0,
        'lastActive' => '10/04/2023',
    ],
];

// Statistiques
$totalUsers = count($usersData);
$activeUsers = count(array_filter($usersData, function($user) { return $user['status'] === 'active'; }));
$inactiveUsers = count(array_filter($usersData, function($user) { return $user['status'] === 'inactive'; }));
$pendingUsers = count(array_filter($usersData, function($user) { return $user['status'] === 'pending'; }));

// Filtrage des utilisateurs selon les critères de recherche
if ($search || $filter !== 'all') {
    $filteredUsers = array_filter($usersData, function($user) use ($search, $filter) {
        $matchesSearch = empty($search) || 
                        stripos($user['name'], $search) !== false || 
                        stripos($user['email'], $search) !== false;
        
        $matchesFilter = $filter === 'all' || $user['status'] === $filter;
        
        return $matchesSearch && $matchesFilter;
    });
} else {
    $filteredUsers = $usersData;
}

// Pagination
$totalPages = ceil(count($filteredUsers) / $limit);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay Admin - Gestion des Utilisateurs</title>
    <link rel="stylesheet" href="users-dashboard.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
   
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo">KnowWay</h1>
                <p class="admin-label">Admin Panel</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="admin.php"><span class="nav-icon dashboard-icon"></span>Dashboard</a></li>
                    <li><a href="courses.php"><span class="nav-icon courses-icon"></span>Courses</a></li>
                    <li class="active"><a href="users.php"><span class="nav-icon users-icon"></span>Users</a></li>
                    <li><a href="statistics.php"><span class="nav-icon stats-icon"></span>Statistics</a></li>
                    <li><a href="settings.php"><span class="nav-icon settings-icon"></span>Settings</a></li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">Sign Out</a>
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
                    <h2>Gestion des Utilisateurs</h2>
                </div>
                
                <div class="header-right">
                    <div class="admin-profile">
                        <span class="admin-avatar">A</span>
                        <span class="admin-name">Admin</span>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <div class="toolbar">
                    <div class="search-filter">
                        <form method="GET" class="search-form">
                            <div class="search-input-wrapper">
                                <input type="text" name="search" placeholder="Rechercher des utilisateurs..." value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="search-btn"></button>
                            </div>
                            
                            <div class="filter-wrapper">
                                <select name="filter" class="filter-select">
                                    <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>Tous les statuts</option>
                                    <option value="active" <?php echo $filter === 'active' ? 'selected' : ''; ?>>Actifs</option>
                                    <option value="inactive" <?php echo $filter === 'inactive' ? 'selected' : ''; ?>>Inactifs</option>
                                    <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    
                    <button class="add-user-btn" id="addUserBtn">Ajouter un utilisateur</button>
                </div>
                
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <h3>Total Utilisateurs</h3>
                        <p class="stat-number"><?php echo $totalUsers; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Utilisateurs Actifs</h3>
                        <p class="stat-number"><?php echo $activeUsers; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Utilisateurs Inactifs</h3>
                        <p class="stat-number"><?php echo $inactiveUsers; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>En Attente</h3>
                        <p class="stat-number"><?php echo $pendingUsers; ?></p>
                    </div>
                </div>
                
                <div class="users-table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Statut</th>
                                <th>Cours inscrits</th>
                                <th>Dernière activité</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($filteredUsers) > 0): ?>
                                <?php foreach ($filteredUsers as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <?php 
                                                    $initials = '';
                                                    $name_parts = explode(' ', $user['name']);
                                                    foreach ($name_parts as $part) {
                                                        $initials .= substr($part, 0, 1);
                                                    }
                                                    echo htmlspecialchars($initials);
                                                    ?>
                                                </div>
                                                <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $user['status']; ?>">
                                                <?php 
                                                    echo $user['status'] === 'active' ? 'Actif' : 
                                                        ($user['status'] === 'inactive' ? 'Inactif' : 'En attente'); 
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo $user['enrolledCourses']; ?></td>
                                        <td><?php echo $user['lastActive']; ?></td>
                                        <td>
                                            <a href="user-detail.php?id=<?php echo $user['id']; ?>" class="action-btn view-btn">Voir</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucun utilisateur trouvé. Essayez d'ajuster votre recherche ou vos filtres.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>" class="pagination-btn prev">&laquo; Précédent</a>
                    <?php endif; ?>
                    
                    <div class="pagination-numbers">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>" class="pagination-number <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>" class="pagination-btn next">Suivant &raquo;</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const adminContainer = document.querySelector('.admin-container');
            
            menuToggle.addEventListener('click', function() {
                adminContainer.classList.toggle('sidebar-collapsed');
            });
        });
    </script>
</body>
</html>