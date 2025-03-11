<?php
include 'db.php';

$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 6;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM courses WHERE title LIKE '%$search%'";

if ($filter) {
    $sql .= " ORDER BY created_at " . ($filter === 'newest' ? "DESC" : "ASC");
}

$sql .= " LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

$total_courses = $conn->query("SELECT COUNT(*) AS total FROM courses WHERE title LIKE '%$search%'")->fetch_assoc()['total'];
$total_pages = ceil($total_courses / $limit);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <h1>Admin Dashboard</h1>

        <form method="GET">
            <input type="text" name="search" placeholder="Search courses..." value="<?= htmlspecialchars($search) ?>">
            <select name="filter">
                <option value="">Sort by</option>
                <option value="newest" <?= $filter === 'newest' ? 'selected' : '' ?>>Newest</option>
                <option value="oldest" <?= $filter === 'oldest' ? 'selected' : '' ?>>Oldest</option>
            </select>
            <button type="submit">Search</button>
        </form>

        <div class="courses">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="course-card">
                    <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['title']) ?>">
                    <h2><?= htmlspecialchars($row['title']) ?></h2>
                    <p><?= htmlspecialchars($row['description']) ?></p>
                    <form method="POST" action="delete_course.php">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit">Delete</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= $search ?>&filter=<?= $filter ?>" class="<?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    </body>
</html>
