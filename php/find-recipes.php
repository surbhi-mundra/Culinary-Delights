<?php
// Set headers for JSON response and allow CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log the request for debugging
$logFile = 'recipe_api_log.txt';
file_put_contents($logFile, "Request received: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    file_put_contents($logFile, "Error: Method not allowed\n", FILE_APPEND);
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get ingredients from POST data
if (!isset($_POST['ingredients'])) {
    file_put_contents($logFile, "Error: No ingredients provided\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['error' => 'No ingredients provided']);
    exit;
}

try {
    // Parse ingredients
    $ingredients = json_decode($_POST['ingredients'], true);
    
    // Log received ingredients
    file_put_contents($logFile, "Received ingredients: " . print_r($ingredients, true) . "\n", FILE_APPEND);
    
    if (!is_array($ingredients) || empty($ingredients)) {
        throw new Exception('Invalid ingredients format');
    }
    
    // Find recipes based on ingredients
    $recipes = findRecipesByIngredients($ingredients);
    
    // Log found recipes count
    file_put_contents($logFile, "Found " . count($recipes) . " recipes\n", FILE_APPEND);
    
    // Return recipes as JSON
    echo json_encode($recipes);
    
} catch (Exception $e) {
    file_put_contents($logFile, "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

/**
 * Find recipes based on available ingredients
 * 
 * @param array $availableIngredients List of available ingredients
 * @return array List of matching recipes
 */
function findRecipesByIngredients($availableIngredients) {
    // Get recipe database
    $recipeDatabase = getRecipeDatabase();
    
    // Filter and sort recipes
    $matchedRecipes = [];
    
    foreach ($recipeDatabase as $recipe) {
        $matchedIngredients = [];
        $missingIngredients = [];
        
        // Check each recipe ingredient
        foreach ($recipe['ingredients'] as $ingredient) {
            $matched = false;
            
            // Check if any available ingredient matches
            foreach ($availableIngredients as $available) {
                if (stripos($ingredient, $available) !== false || stripos($available, $ingredient) !== false) {
                    $matched = true;
                    break;
                }
            }
            
            if ($matched) {
                $matchedIngredients[] = $ingredient;
            } else {
                $missingIngredients[] = $ingredient;
            }
        }
        
        // Only include recipes with at least one matched ingredient
        if (count($matchedIngredients) > 0) {
            $recipe['matchedIngredients'] = $matchedIngredients;
            $recipe['missingIngredients'] = $missingIngredients;
            $matchedRecipes[] = $recipe;
        }
    }
    
    // Sort recipes by percentage of matched ingredients (descending)
    usort($matchedRecipes, function($a, $b) {
        $aPercentage = count($a['matchedIngredients']) / count($a['ingredients']);
        $bPercentage = count($b['matchedIngredients']) / count($b['ingredients']);
        
        if ($bPercentage != $aPercentage) {
            return $bPercentage <=> $aPercentage;
        }
        
        // If percentage is the same, sort by number of missing ingredients
        return count($a['missingIngredients']) <=> count($b['missingIngredients']);
    });
    
    // Return top 8 recipes
    return array_slice($matchedRecipes, 0, 8);
}

/**
 * Get recipe database with categorized recipes
 * 
 * @return array Array of recipes
 */
function getRecipeDatabase() {
    return [
        // Breakfast Recipes
        [
            'id' => '1',
            'name' => 'Avocado Toast with Eggs',
            'description' => 'Creamy avocado spread on toast topped with perfectly poached eggs.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 15,
            'difficulty' => 'Easy',
            'servings' => 1,
            'category' => 'Breakfast',
            'ingredients' => ['bread', 'avocado', 'eggs', 'salt', 'pepper', 'lemon juice', 'chili flakes'],
            'instructions' => [
                'Toast the bread until golden brown.',
                'Mash the avocado with lemon juice, salt, and pepper.',
                'Spread the avocado mixture on the toast.',
                'Poach or fry the eggs to your liking.',
                'Place the eggs on top of the avocado toast.',
                'Sprinkle with chili flakes and serve.'
            ]
        ],
        [
            'id' => '2',
            'name' => 'Banana Pancakes',
            'description' => 'Fluffy pancakes with sweet banana flavor, perfect for breakfast.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 20,
            'difficulty' => 'Easy',
            'servings' => 2,
            'category' => 'Breakfast',
            'ingredients' => ['flour', 'baking powder', 'salt', 'sugar', 'milk', 'eggs', 'bananas', 'butter', 'vanilla extract'],
            'instructions' => [
                'Mix flour, baking powder, salt, and sugar in a bowl.',
                'In another bowl, mash bananas and mix with milk, eggs, and vanilla.',
                'Combine wet and dry ingredients until just mixed.',
                'Heat butter in a pan over medium heat.',
                'Pour batter to form pancakes and cook until bubbles form.',
                'Flip and cook until golden brown.'
            ]
        ],
        [
            'id' => '3',
            'name' => 'Greek Yogurt Parfait',
            'description' => 'Layers of Greek yogurt, granola, fresh berries, and honey.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 10,
            'difficulty' => 'Easy',
            'servings' => 1,
            'category' => 'Breakfast',
            'ingredients' => ['greek yogurt', 'granola', 'strawberries', 'blueberries', 'honey'],
            'instructions' => [
                'Layer Greek yogurt in a glass or bowl.',
                'Add a layer of granola.',
                'Add fresh berries.',
                'Repeat layers as desired.',
                'Drizzle with honey and serve.'
            ]
        ],
        
        // Lunch Recipes
        [
            'id' => '4',
            'name' => 'Chicken Caesar Salad',
            'description' => 'Romaine lettuce, grilled chicken, parmesan cheese, croutons, and Caesar dressing.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 20,
            'difficulty' => 'Medium',
            'servings' => 2,
            'category' => 'Lunch',
            'ingredients' => ['chicken breast', 'romaine lettuce', 'parmesan cheese', 'croutons', 'caesar dressing', 'olive oil'],
            'instructions' => [
                'Season and grill chicken breast until cooked through.',
                'Chop romaine lettuce and place in a large bowl.',
                'Slice the grilled chicken.',
                'Add chicken, croutons, and parmesan to the lettuce.',
                'Toss with Caesar dressing and serve.'
            ]
        ],
        [
            'id' => '5',
            'name' => 'Quinoa Bowl',
            'description' => 'Quinoa with roasted vegetables, avocado, chickpeas, and tahini dressing.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 25,
            'difficulty' => 'Medium',
            'servings' => 2,
            'category' => 'Lunch',
            'ingredients' => ['quinoa', 'bell peppers', 'zucchini', 'chickpeas', 'avocado', 'tahini', 'lemon juice', 'olive oil'],
            'instructions' => [
                'Cook quinoa according to package instructions.',
                'Roast vegetables in the oven with olive oil.',
                'Prepare tahini dressing with lemon juice.',
                'Assemble bowl with quinoa, vegetables, chickpeas, and avocado.',
                'Drizzle with tahini dressing and serve.'
            ]
        ],
        
        // Dinner Recipes
        [
            'id' => '6',
            'name' => 'Grilled Salmon',
            'description' => 'Wild-caught salmon with lemon butter sauce, wild rice, and seasonal vegetables.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 30,
            'difficulty' => 'Medium',
            'servings' => 2,
            'category' => 'Dinner',
            'ingredients' => ['salmon fillet', 'rice', 'broccoli', 'lemon', 'butter', 'garlic', 'olive oil'],
            'instructions' => [
                'Cook rice according to package instructions.',
                'Season salmon with salt and pepper.',
                'Grill salmon for 4-5 minutes per side.',
                'Steam broccoli until tender.',
                'Make lemon butter sauce with garlic.',
                'Serve salmon over rice with vegetables and sauce.'
            ]
        ],
        [
            'id' => '7',
            'name' => 'Chicken Stir Fry',
            'description' => 'Quick and healthy stir fry with colorful vegetables and savory sauce.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 25,
            'difficulty' => 'Medium',
            'servings' => 3,
            'category' => 'Dinner',
            'ingredients' => ['chicken breast', 'rice', 'bell peppers', 'broccoli', 'carrots', 'onion', 'garlic', 'soy sauce', 'sesame oil', 'ginger'],
            'instructions' => [
                'Cook rice according to package instructions.',
                'Cut chicken into bite-sized pieces.',
                'Heat sesame oil in a wok or large pan.',
                'Add minced garlic and ginger, sauté until fragrant.',
                'Add chicken and cook until done.',
                'Add chopped vegetables and stir fry until tender-crisp.',
                'Pour in soy sauce and toss to coat.',
                'Serve over cooked rice.'
            ]
        ],
        
        // Italian Cuisine
        [
            'id' => '8',
            'name' => 'Spaghetti Aglio e Olio',
            'description' => 'A simple Italian pasta dish with garlic, olive oil, and chili flakes.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 20,
            'difficulty' => 'Easy',
            'servings' => 2,
            'category' => 'Italian',
            'ingredients' => ['spaghetti', 'garlic', 'olive oil', 'chili flakes', 'parsley', 'parmesan cheese'],
            'instructions' => [
                'Boil spaghetti according to package instructions.',
                'In a pan, sauté minced garlic in olive oil until fragrant.',
                'Add chili flakes and cook for 30 seconds.',
                'Toss in the cooked pasta with some pasta water.',
                'Garnish with chopped parsley and grated parmesan cheese.'
            ]
        ],
        [
            'id' => '9',
            'name' => 'Margherita Pizza',
            'description' => 'Thin crust pizza with tomato sauce, fresh mozzarella, and basil.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 25,
            'difficulty' => 'Medium',
            'servings' => 2,
            'category' => 'Italian',
            'ingredients' => ['pizza dough', 'tomato sauce', 'mozzarella cheese', 'basil', 'olive oil'],
            'instructions' => [
                'Preheat oven to 475°F (245°C).',
                'Roll out pizza dough on a floured surface.',
                'Spread tomato sauce evenly over the dough.',
                'Add torn mozzarella cheese.',
                'Bake for 12-15 minutes until crust is golden.',
                'Top with fresh basil and drizzle with olive oil.'
            ]
        ],
        
        // Indian Cuisine
        [
            'id' => '10',
            'name' => 'Butter Chicken',
            'description' => 'Tender chicken in a rich tomato and butter sauce, served with basmati rice.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 35,
            'difficulty' => 'Medium',
            'servings' => 4,
            'category' => 'Indian',
            'ingredients' => ['chicken breast', 'basmati rice', 'tomatoes', 'onion', 'garlic', 'ginger', 'butter', 'cream', 'garam masala', 'turmeric'],
            'instructions' => [
                'Cook basmati rice according to package instructions.',
                'Cut chicken into bite-sized pieces and season.',
                'Sauté onion, garlic, and ginger until fragrant.',
                'Add tomatoes and spices, cook until thick.',
                'Add chicken and cook until done.',
                'Stir in butter and cream.',
                'Serve over rice with fresh cilantro.'
            ]
        ],
        [
            'id' => '11',
            'name' => 'Vegetable Curry',
            'description' => 'Mixed vegetables in a fragrant curry sauce with coconut milk.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 30,
            'difficulty' => 'Medium',
            'servings' => 4,
            'category' => 'Indian',
            'ingredients' => ['potatoes', 'carrots', 'peas', 'onion', 'garlic', 'ginger', 'coconut milk', 'curry powder', 'turmeric', 'rice'],
            'instructions' => [
                'Cook rice according to package instructions.',
                'Chop all vegetables into bite-sized pieces.',
                'Sauté onion, garlic, and ginger until fragrant.',
                'Add curry powder and turmeric, cook for 1 minute.',
                'Add vegetables and coconut milk.',
                'Simmer until vegetables are tender.',
                'Serve over rice.'
            ]
        ],
        
        // Chinese Cuisine
        [
            'id' => '12',
            'name' => 'Fried Rice',
            'description' => 'Classic fried rice with eggs, vegetables, and soy sauce.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 20,
            'difficulty' => 'Easy',
            'servings' => 3,
            'category' => 'Chinese',
            'ingredients' => ['rice', 'eggs', 'carrots', 'peas', 'onion', 'garlic', 'soy sauce', 'sesame oil', 'green onions'],
            'instructions' => [
                'Cook rice and let it cool (preferably day-old rice).',
                'Scramble eggs and set aside.',
                'Heat oil in a wok or large pan.',
                'Add garlic and onion, stir fry until fragrant.',
                'Add carrots and peas, cook until tender.',
                'Add rice and break up any clumps.',
                'Add soy sauce and scrambled eggs.',
                'Garnish with green onions and serve.'
            ]
        ],
        [
            'id' => '13',
            'name' => 'Sweet and Sour Chicken',
            'description' => 'Crispy chicken pieces in a tangy sweet and sour sauce.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 30,
            'difficulty' => 'Medium',
            'servings' => 3,
            'category' => 'Chinese',
            'ingredients' => ['chicken breast', 'bell peppers', 'pineapple', 'onion', 'rice', 'vinegar', 'sugar', 'ketchup', 'soy sauce', 'cornstarch'],
            'instructions' => [
                'Cook rice according to package instructions.',
                'Cut chicken into bite-sized pieces and coat with cornstarch.',
                'Fry chicken until golden and crispy.',
                'Make sweet and sour sauce with vinegar, sugar, and ketchup.',
                'Stir fry vegetables until tender-crisp.',
                'Combine chicken, vegetables, and sauce.',
                'Serve over rice.'
            ]
        ],
        
        // Mexican Cuisine
        [
            'id' => '14',
            'name' => 'Chicken Quesadillas',
            'description' => 'Crispy tortillas filled with seasoned chicken, melted cheese, and vegetables.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 25,
            'difficulty' => 'Easy',
            'servings' => 2,
            'category' => 'Mexican',
            'ingredients' => ['tortillas', 'chicken breast', 'cheese', 'bell peppers', 'onion', 'cumin', 'paprika', 'salt', 'olive oil'],
            'instructions' => [
                'Season chicken with cumin, paprika, and salt.',
                'Cook chicken in olive oil until done, then shred.',
                'Sauté sliced bell peppers and onions until soft.',
                'Place a tortilla in a pan, add cheese, chicken, and vegetables.',
                'Top with another tortilla and cook until golden brown on both sides.',
                'Cut into wedges and serve with salsa and sour cream.'
            ]
        ],
        [
            'id' => '15',
            'name' => 'Black Bean Tacos',
            'description' => 'Vegetarian tacos with seasoned black beans, avocado, and fresh salsa.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 20,
            'difficulty' => 'Easy',
            'servings' => 3,
            'category' => 'Mexican',
            'ingredients' => ['black beans', 'tortillas', 'avocado', 'tomatoes', 'onion', 'cilantro', 'lime', 'cumin', 'chili powder', 'cheese'],
            'instructions' => [
                'Heat black beans with cumin and chili powder.',
                'Dice tomatoes, onion, and cilantro for salsa.',
                'Slice avocado and squeeze with lime juice.',
                'Warm tortillas in a dry pan.',
                'Fill tortillas with beans, avocado, cheese, and salsa.',
                'Serve with lime wedges.'
            ]
        ],
        
        // Desserts
        [
            'id' => '16',
            'name' => 'Chocolate Chip Cookies',
            'description' => 'Classic homemade cookies with gooey chocolate chips.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 25,
            'difficulty' => 'Easy',
            'servings' => 24,
            'category' => 'Dessert',
            'ingredients' => ['flour', 'baking soda', 'salt', 'butter', 'sugar', 'brown sugar', 'eggs', 'vanilla extract', 'chocolate chips'],
            'instructions' => [
                'Preheat oven to 375°F (190°C).',
                'Mix flour, baking soda, and salt in a bowl.',
                'Cream together butter, sugar, and brown sugar until fluffy.',
                'Beat in eggs and vanilla extract.',
                'Gradually add flour mixture, then fold in chocolate chips.',
                'Drop spoonfuls of dough onto baking sheets.',
                'Bake for 9-11 minutes until golden brown.'
            ]
        ],
        [
            'id' => '17',
            'name' => 'Fruit Smoothie',
            'description' => 'Refreshing blended drink with mixed fruits and yogurt.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 10,
            'difficulty' => 'Easy',
            'servings' => 2,
            'category' => 'Beverage',
            'ingredients' => ['banana', 'strawberries', 'blueberries', 'yogurt', 'honey', 'ice', 'milk'],
            'instructions' => [
                'Add chopped banana, strawberries, and blueberries to a blender.',
                'Add yogurt, honey, ice, and milk.',
                'Blend until smooth and creamy.',
                'Pour into glasses and serve immediately.'
            ]
        ],
        
        // More recipes for variety
        [
            'id' => '18',
            'name' => 'Greek Salad',
            'description' => 'A refreshing salad with cucumbers, tomatoes, olives, and feta cheese.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 15,
            'difficulty' => 'Easy',
            'servings' => 2,
            'category' => 'Lunch',
            'ingredients' => ['cucumber', 'tomatoes', 'red onion', 'olives', 'feta cheese', 'olive oil', 'lemon juice', 'oregano', 'salt', 'pepper'],
            'instructions' => [
                'Chop cucumber, tomatoes, and red onion.',
                'Combine vegetables in a bowl with olives.',
                'Crumble feta cheese over the top.',
                'Whisk together olive oil, lemon juice, oregano, salt, and pepper.',
                'Pour dressing over the salad and toss gently.'
            ]
        ],
        [
            'id' => '19',
            'name' => 'Mushroom Risotto',
            'description' => 'Creamy Italian rice dish with sautéed mushrooms and parmesan.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 40,
            'difficulty' => 'Medium',
            'servings' => 4,
            'category' => 'Italian',
            'ingredients' => ['arborio rice', 'mushrooms', 'onion', 'garlic', 'white wine', 'vegetable broth', 'parmesan cheese', 'butter', 'olive oil', 'thyme'],
            'instructions' => [
                'Sauté chopped onion and garlic in olive oil until translucent.',
                'Add sliced mushrooms and cook until browned.',
                'Add arborio rice and stir to coat with oil.',
                'Pour in white wine and simmer until absorbed.',
                'Gradually add hot vegetable broth, stirring frequently.',
                'Continue adding broth until rice is creamy and al dente.',
                'Stir in butter, parmesan cheese, and thyme.'
            ]
        ],
        [
            'id' => '20',
            'name' => 'Tomato Soup',
            'description' => 'A comforting homemade tomato soup with fresh herbs.',
            'image' => '/placeholder.svg?height=300&width=500',
            'cookTime' => 35,
            'difficulty' => 'Medium',
            'servings' => 4,
            'category' => 'Lunch',
            'ingredients' => ['tomatoes', 'onion', 'garlic', 'vegetable broth', 'olive oil', 'basil', 'cream', 'salt', 'pepper', 'sugar'],
            'instructions' => [
                'Sauté chopped onion and garlic in olive oil until soft.',
                'Add chopped tomatoes and cook for 5 minutes.',
                'Pour in vegetable broth and bring to a simmer.',
                'Add basil, salt, pepper, and a pinch of sugar.',
                'Simmer for 20 minutes, then blend until smooth.',
                'Stir in cream and serve hot.'
            ]
        ]
    ];
}
?>
