<?php
/**
 * Fuzzy Search Interface
 * 
 * This file provides an interface between PHP and the Python fuzzy search implementation.
 */

/**
 * Perform a fuzzy search on courses using the Python implementation
 * 
 * @param string $search The search query
 * @param mysqli $conn The database connection
 * @param int $threshold The similarity threshold (0-100)
 * @return array The search results
 */
function fuzzy_search_courses($search, $conn, $threshold = 50) {
    // If search is empty, return null to use normal query
    if (empty($search)) {
        return null;
    }
    
    // Escape the search query for command line use
    $escaped_search = escapeshellarg($search);
    
    // Path to Python script
    $script_path = __DIR__ . '/search.py';
    
    try {
        // Option 1: Pass query only and let Python connect to database
        // $output = shell_exec("python $script_path $escaped_search");
        
        // Option 2: Get all courses from database and pass to Python
        // For better security, we'll query the database in PHP and pass the data to Python
        $courses_sql = "SELECT c.*, COUNT(cq.id) as quizzes_count 
                        FROM courses c 
                        LEFT JOIN course_quizzes cq ON c.id = cq.course_id 
                        WHERE c.status = 'published' 
                        GROUP BY c.id";
        
        $result = $conn->query($courses_sql);
        $courses = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }
        }
        
        // Save courses to temporary file for Python to read
        $temp_file = tempnam(sys_get_temp_dir(), 'courses_');
        file_put_contents($temp_file, json_encode($courses));
        
        // Execute Python script with search query and data file
        $output = shell_exec("python $script_path $escaped_search --data-file " . escapeshellarg($temp_file) . " --threshold $threshold");
        
        // Clean up temp file
        unlink($temp_file);
        
        // Parse the output
        $search_results = json_decode($output, true);
        
        // Check if the output is valid
        if (!is_array($search_results)) {
            error_log("Fuzzy search failed: " . $output);
            return null; // Fall back to regular search
        }
        
        return $search_results;
    } catch (Exception $e) {
        error_log("Error in fuzzy search: " . $e->getMessage());
        return null; // Fall back to regular search
    }
}

/**
 * Create SQL for fuzzy search results
 *
 * @param array $search_results The fuzzy search results
 * @return string The SQL condition for the fuzzy search
 */
function create_fuzzy_search_condition($search_results) {
    if (empty($search_results)) {
        return "AND 0"; // No matches found
    }
    
    // Extract course IDs
    $course_ids = array_map(function($course) {
        return (int)$course['id'];
    }, $search_results);
    
    // Create the IN clause
    $ids_string = implode(',', $course_ids);
    
    return "AND c.id IN ($ids_string)";
}
?> 