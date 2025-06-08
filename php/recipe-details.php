<?php
// Set headers for JSON response and allow CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log the request for debugging
$logFile = 'recipe_details_log.txt';
file_put_contents($logFile, "Request received: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
file_put_contents($logFile, "GET params: " . print_r($_GET, true) . "\n", FILE_APPEND);

// Check if recipe ID is provided
if (!isset($_GET['id'])) {
    file_put_contents($logFile, "Error: Recipe ID is required\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['error' => 'Recipe ID is required']);
    exit;
}

$recipeId = $_GET['id'];

try {
    // Get recipe details
    $recipe = getRecipeDetails($recipeId);
    
    if (!$recipe) {
        throw new Exception('Recipe not found');
    }
    
    // Log found recipe
    file_put_contents($logFile, "Found recipe: " . $recipe['name'] . "\n", FILE_APPEND);
    
    // Return recipe as JSON
    echo json_encode($recipe);
    
} catch (Exception $e) {
    file_put_contents($logFile, "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(404);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

/**
 * Get recipe details by ID
 * 
 * @param string $recipeId Recipe ID
 * @return array|null Recipe details or null if not found
 */
function getRecipeDetails($recipeId) {
    // Include the same recipe database from find-recipes.php
    include_once 'find-recipes.php';
    
    // Get recipe database
    $recipeDatabase = getRecipeDatabase();
    
    // Find recipe by ID
    foreach ($recipeDatabase as $recipe) {
        if ($recipe['id'] === $recipeId) {
            return $recipe;
        }
    }
    
    return null;
}
?>
