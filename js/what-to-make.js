document.addEventListener("DOMContentLoaded", () => {
  // Set current year in footer
  document.getElementById("year").textContent = new Date().getFullYear()

  // Mobile menu toggle
  const menuToggle = document.querySelector(".menu-toggle")
  const navMenu = document.querySelector(".nav-menu")

  if (menuToggle) {
    menuToggle.addEventListener("click", function () {
      navMenu.classList.toggle("active")
      const icon = this.querySelector("i")
      if (icon.classList.contains("fa-bars")) {
        icon.classList.remove("fa-bars")
        icon.classList.add("fa-times")
      } else {
        icon.classList.remove("fa-times")
        icon.classList.add("fa-bars")
      }
    })
  }

  // Close mobile menu when clicking on a nav link
  const navLinks = document.querySelectorAll(".nav-link")
  navLinks.forEach((link) => {
    link.addEventListener("click", () => {
      if (navMenu.classList.contains("active")) {
        navMenu.classList.remove("active")
        const icon = menuToggle.querySelector("i")
        icon.classList.remove("fa-times")
        icon.classList.add("fa-bars")
      }
    })
  })

  // Sticky navigation
  window.addEventListener("scroll", () => {
    const nav = document.getElementById("main-nav")
    if (window.scrollY > 100) {
      nav.style.padding = "10px 0"
      nav.style.backgroundColor = "rgba(10, 10, 10, 0.95)"
    } else {
      nav.style.padding = "15px 0"
      nav.style.backgroundColor = "rgba(10, 10, 10, 0.9)"
    }
  })

  // Initialize GSAP animations
  const gsap = window.gsap // Declare gsap variable
  const ScrollTrigger = window.ScrollTrigger // Declare ScrollTrigger variable

  if (gsap) {
    // Register ScrollTrigger plugin
    if (ScrollTrigger) {
      gsap.registerPlugin(ScrollTrigger)
    }

    // Hero section animations
    gsap.from(".hero-title", {
      opacity: 0,
      y: 50,
      duration: 1.2,
      ease: "power3.out",
    })

    gsap.from(".hero-subtitle", {
      opacity: 0,
      duration: 1,
      delay: 0.4,
      ease: "power3.out",
    })
  }

  // Ingredient search functionality
  const ingredientInput = document.getElementById("ingredient-input")
  const addIngredientBtn = document.getElementById("add-ingredient")
  const ingredientsList = document.getElementById("ingredients-list")
  const errorMessage = document.getElementById("error-message")
  const findRecipesBtn = document.getElementById("find-recipes")
  const loadingIndicator = document.getElementById("loading")
  const recipeResults = document.getElementById("recipe-results")
  const recipesGrid = document.getElementById("recipes-grid")

  let ingredients = []

  // Add ingredient function
  function addIngredient() {
    const ingredient = ingredientInput.value.trim().toLowerCase()

    if (!ingredient) {
      showError("Please enter an ingredient")
      return
    }

    if (ingredients.includes(ingredient)) {
      showError("This ingredient is already in your list")
      return
    }

    // Add to ingredients array
    ingredients.push(ingredient)

    // Create ingredient tag
    const tagElement = document.createElement("div")
    tagElement.className = "ingredient-tag"
    tagElement.innerHTML = `
      <span>${ingredient}</span>
      <button type="button" data-ingredient="${ingredient}">
        <i class="fas fa-times"></i>
      </button>
    `

    // Add remove event listener
    const removeBtn = tagElement.querySelector("button")
    removeBtn.addEventListener("click", function () {
      const ingredientToRemove = this.getAttribute("data-ingredient")
      removeIngredient(ingredientToRemove, tagElement)
    })

    // Add to DOM
    ingredientsList.appendChild(tagElement)

    // Clear input and error
    ingredientInput.value = ""
    errorMessage.style.display = "none"

    // Log for debugging
    console.log("Added ingredient:", ingredient)
    console.log("Current ingredients:", ingredients)
  }

  // Remove ingredient function
  function removeIngredient(ingredient, element) {
    ingredients = ingredients.filter((item) => item !== ingredient)
    element.remove()

    // Log for debugging
    console.log("Removed ingredient:", ingredient)
    console.log("Current ingredients:", ingredients)
  }

  // Show error message
  function showError(message) {
    errorMessage.textContent = message
    errorMessage.style.display = "block"

    // Auto-hide error after 3 seconds
    setTimeout(() => {
      errorMessage.style.display = "none"
    }, 3000)
  }

  // Find recipes function
  async function findRecipes() {
    if (ingredients.length === 0) {
      showError("Please add at least one ingredient")
      return
    }

    // Show loading indicator
    loadingIndicator.style.display = "flex"
    errorMessage.style.display = "none"
    recipeResults.style.display = "none"

    try {
      console.log("Sending ingredients to API:", ingredients)

      // Make AJAX request to PHP backend
      const formData = new FormData()
      formData.append("ingredients", JSON.stringify(ingredients))

      const response = await fetch("php/find-recipes.php", {
        method: "POST",
        body: formData,
      })

      console.log("Response status:", response.status)

      if (!response.ok) {
        throw new Error(`Server responded with status: ${response.status}`)
      }

      const data = await response.json()
      console.log("Received data:", data)

      // Display recipes
      displayRecipes(data)
    } catch (error) {
      console.error("Error:", error)
      showError("Failed to find recipes. Please try again.")
    } finally {
      // Hide loading indicator
      loadingIndicator.style.display = "none"
    }
  }

  // Display recipes function
  function displayRecipes(recipes) {
    // Clear previous results
    recipesGrid.innerHTML = ""

    if (!Array.isArray(recipes) || recipes.length === 0) {
      recipesGrid.innerHTML =
        '<p class="no-results">No recipes found with your ingredients. Try adding more ingredients.</p>'
      recipeResults.style.display = "block"
      return
    }

    // Create recipe cards
    recipes.forEach((recipe) => {
      const recipeCard = document.createElement("div")
      recipeCard.className = "recipe-card"

      recipeCard.innerHTML = `
        <div class="recipe-image">
          <img src="${recipe.image || "/placeholder.svg?height=300&width=500"}" alt="${recipe.name}">
        </div>
        <div class="recipe-content">
          <div class="recipe-category">${recipe.category}</div>
          <h3 class="recipe-title">${recipe.name}</h3>
          <p class="recipe-description">${recipe.description}</p>
          
          <div class="recipe-meta">
            <div class="meta-item">
              <i class="fas fa-clock"></i>
              <span>${recipe.cookTime} min</span>
            </div>
            <div class="meta-item">
              <i class="fas fa-utensils"></i>
              <span>${recipe.difficulty}</span>
            </div>
            <div class="meta-item">
              <i class="fas fa-user"></i>
              <span>${recipe.servings} servings</span>
            </div>
          </div>
          
          <div class="ingredients-section">
            <h4>Ingredients you have:</h4>
            <div class="ingredients-tags">
              ${recipe.matchedIngredients
                .map((ingredient) => `<span class="ingredient-match">${ingredient}</span>`)
                .join("")}
            </div>
          </div>
          
          ${
            recipe.missingIngredients.length > 0
              ? `
              <div class="ingredients-section">
                <h4>You'll also need:</h4>
                <div class="ingredients-tags">
                  ${recipe.missingIngredients
                    .map((ingredient) => `<span class="ingredient-missing">${ingredient}</span>`)
                    .join("")}
                </div>
              </div>
            `
              : ""
          }
          
          <button class="view-recipe" data-id="${recipe.id}">View Recipe</button>
        </div>
      `

      // Add to DOM
      recipesGrid.appendChild(recipeCard)

      // Add view recipe event listener
      const viewRecipeBtn = recipeCard.querySelector(".view-recipe")
      viewRecipeBtn.addEventListener("click", function () {
        const recipeId = this.getAttribute("data-id")
        viewRecipe(recipeId)
      })
    })

    // Show results section
    recipeResults.style.display = "block"

    // Scroll to results
    recipeResults.scrollIntoView({ behavior: "smooth" })

    // Animate recipe cards
    if (gsap) {
      gsap.from(".recipe-card", {
        opacity: 0,
        y: 50,
        duration: 0.8,
        stagger: 0.1,
        ease: "power3.out",
      })
    }
  }

  // View recipe function
  function viewRecipe(recipeId) {
    // In a real application, this would navigate to a recipe detail page
    // For now, we'll fetch the recipe details and show them in an alert
    fetch(`php/recipe-details.php?id=${recipeId}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error("Failed to fetch recipe details")
        }
        return response.json()
      })
      .then((recipe) => {
        // Create a formatted string of instructions
        const instructions = recipe.instructions.map((step, index) => `${index + 1}. ${step}`).join("\n")

        alert(`
Recipe: ${recipe.name}

${recipe.description}

Cooking Time: ${recipe.cookTime} minutes
Difficulty: ${recipe.difficulty}
Servings: ${recipe.servings}

Instructions:
${instructions}
        `)
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Failed to load recipe details. Please try again.")
      })
  }

  // Event listeners
  if (addIngredientBtn) {
    addIngredientBtn.addEventListener("click", addIngredient)
    console.log("Add ingredient button listener added")
  }

  if (ingredientInput) {
    ingredientInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        e.preventDefault()
        addIngredient()
      }
    })
    console.log("Input keypress listener added")
  }

  if (findRecipesBtn) {
    findRecipesBtn.addEventListener("click", findRecipes)
    console.log("Find recipes button listener added")
  }

  // Add some test ingredients for debugging
  console.log("What to Make JS loaded successfully")
})
