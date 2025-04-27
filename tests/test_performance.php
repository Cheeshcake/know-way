<?php
include("../config/db.php");

$start = microtime(as_float: true);

$result = $conn->query("
    SELECT id,creator_id, title, description, image , video , video_type , status , created_at , updated_at , approved_at ,approved_by
    FROM courses
");
$courses = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}

$end = microtime(as_float: true);

$temps = round(num: ($end - $start) * 1000, precision: 2);

echo "Test Performance Liste Cours<br>";
echo "Temps d'exécution : {$temps} ms";
?>
