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

  if (gsap && ScrollTrigger) {
    // Register ScrollTrigger plugin
    gsap.registerPlugin(ScrollTrigger)

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

    gsap.from(".hero-cta", {
      opacity: 0,
      y: 20,
      duration: 0.8,
      delay: 0.8,
      ease: "power3.out",
    })

    gsap.from(".scroll-indicator", {
      opacity: 0,
      duration: 0.5,
      delay: 1.2,
      ease: "power3.out",
      onComplete: () => {
        gsap.to(".scroll-indicator", {
          y: 10,
          repeat: -1,
          yoyo: true,
          duration: 1.5,
        })
      },
    })

    // Parallax effect for background
    gsap.to(".parallax-bg", {
      yPercent: -20,
      ease: "none",
      scrollTrigger: {
        trigger: "body",
        start: "top top",
        end: "bottom top",
        scrub: true,
      },
    })
  }

  // Load food items
  const foodData = {
    // Example food data
    appetizers: [
      {
        name: "Bruschetta",
        price: 5,
        description: "Tomato and basil bread",
        prepTime: 10,
        image: "images/bruschetta.jpg",
      },
    ],
    mains: [
      {
        name: "Spaghetti Carbonara",
        price: 15,
        description: "Classic Italian pasta dish",
        prepTime: 20,
        image: "images/spaghetti-carbonara.jpg",
      },
    ],
    desserts: [
      { name: "Tiramisu", price: 7, description: "Italian coffee dessert", prepTime: 15, image: "images/tiramisu.jpg" },
    ],
  } // Declare foodData variable

  loadFoodItems()

  // Smooth scrolling for navigation links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault()
      const target = document.querySelector(this.getAttribute("href"))
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })
})

// Scroll to content function
function scrollToContent() {
  window.scrollTo({
    top: window.innerHeight,
    behavior: "smooth",
  })
}

// Load food items function
function loadFoodItems() {
  const foodData = {
    // Example food data
    appetizers: [
      {
        name: "Bruschetta",
        price: 5,
        description: "Tomato and basil bread",
        prepTime: 10,
        image: "images/bruschetta.jpg",
      },
    ],
    mains: [
      {
        name: "Spaghetti Carbonara",
        price: 15,
        description: "Classic Italian pasta dish",
        prepTime: 20,
        image: "images/spaghetti-carbonara.jpg",
      },
    ],
    desserts: [
      { name: "Tiramisu", price: 7, description: "Italian coffee dessert", prepTime: 15, image: "images/tiramisu.jpg" },
    ],
  } // Declare foodData variable

  Object.keys(foodData).forEach((category) => {
    const grid = document.getElementById(`${category}-grid`)
    if (grid) {
      grid.innerHTML = ""
      foodData[category].forEach((item, index) => {
        const foodCard = createFoodCard(item, index)
        grid.appendChild(foodCard)
      })

      // Animate food cards
      const gsap = window.gsap // Declare gsap variable
      const ScrollTrigger = window.ScrollTrigger // Declare ScrollTrigger variable

      if (gsap && ScrollTrigger) {
        const cards = grid.querySelectorAll(".food-card")
        gsap.fromTo(
          cards,
          { opacity: 0, y: 50 },
          {
            opacity: 1,
            y: 0,
            stagger: 0.1,
            duration: 0.8,
            scrollTrigger: {
              trigger: grid.parentElement,
              start: "top 70%",
              end: "bottom 20%",
              toggleActions: "play none none reverse",
            },
          },
        )
      }
    }
  })
}

// Create food card function
function createFoodCard(item, index) {
  const card = document.createElement("div")
  card.className = "food-card"
  card.innerHTML = `
    <div class="food-image">
      <img src="${item.image}" alt="${item.name}" loading="lazy">
    </div>
    <div class="food-content">
      <div class="food-header">
        <h3 class="food-name">${item.name}</h3>
        <span class="food-price">$${item.price}</span>
      </div>
      <p class="food-description">${item.description}</p>
      <div class="food-footer">
        <span class="prep-time">${item.prepTime} min</span>
        <button class="order-btn" onclick="orderItem('${item.name}')">Order Now</button>
      </div>
    </div>
  `
  return card
}

// Order item function
function orderItem(itemName) {
  alert(`Thank you for your interest in ${itemName}! This feature will be available soon.`)
}
