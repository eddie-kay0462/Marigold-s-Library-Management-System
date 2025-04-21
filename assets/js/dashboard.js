// Update current date
document.addEventListener("DOMContentLoaded", () => {
    const currentDate = new Date()
    const options = { weekday: "long", year: "numeric", month: "long", day: "numeric" }
    document.getElementById("current-date").textContent = currentDate.toLocaleDateString("en-US", options)
  })
  
  // Sidebar navigation
  document.addEventListener("DOMContentLoaded", () => {
    const menuItems = document.querySelectorAll(".sidebar-menu a")
    const sections = document.querySelectorAll(".dashboard-section")
  
    // Set the initial active link based on hash or default to overview
    const setInitialActiveLink = () => {
      const hash = window.location.hash || "#overview"
      const targetLink = document.querySelector(`.sidebar-menu a[href="${hash}"]`)
  
      if (targetLink) {
        // Remove active class from all menu items
        menuItems.forEach((i) => i.classList.remove("active"))
  
        // Add active class to the target link
        targetLink.classList.add("active")
  
        // Show the corresponding section
        sections.forEach((section) => section.classList.remove("active"))
        const targetSection = document.getElementById(hash.substring(1))
        if (targetSection) {
          targetSection.classList.add("active")
        }
      }
    }
  
    // Call this function on page load
    setInitialActiveLink()
  
    menuItems.forEach((item) => {
      item.addEventListener("click", function (e) {
        e.preventDefault()
  
        // Remove active class from all menu items
        menuItems.forEach((i) => i.classList.remove("active"))
  
        // Add active class to clicked menu item
        this.classList.add("active")
  
        // Hide all sections
        sections.forEach((section) => section.classList.remove("active"))
  
        // Show the corresponding section
        const targetId = this.getAttribute("href").substring(1)
        const targetSection = document.getElementById(targetId)
  
        // Update URL hash without scrolling
        history.pushState(null, null, `#${targetId}`)
  
        // Check if the target section exists before trying to access its classList
        if (targetSection) {
          targetSection.classList.add("active")
        }
      })
    })
  
    // Handle browser back/forward navigation
    window.addEventListener("hashchange", setInitialActiveLink)
  })
  
  // Modal functionality
  function openModal(modalId) {
    document.getElementById(modalId).style.display = "block"
  }
  
  function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none"
  }
  
  // Close modal when clicking outside
  window.onclick = (event) => {
    if (event.target.classList.contains("modal")) {
      event.target.style.display = "none"
    }
  }
  
  // Search functionality
  document.addEventListener("DOMContentLoaded", () => {
    // Only select search inputs that are meant for filtering tables
    const searchInputs = document.querySelectorAll('input[type="text"].search-input')
  
    searchInputs.forEach((input) => {
      input.addEventListener("input", function () {
        const searchTerm = this.value.toLowerCase()
        const card = this.closest(".card")
        if (!card) return
  
        const table = card.querySelector("table")
        if (!table) return
  
        const rows = table.querySelectorAll("tbody tr")
        if (!rows.length) return
  
        rows.forEach((row) => {
          const text = row.textContent.toLowerCase()
          row.style.display = text.includes(searchTerm) ? "" : "none"
        })
      })
    })
  })
  
  // Book management functions
  function addBook() {
    openModal("add-book-modal")
  }
  
  function editBook(id) {
    openModal("edit-book-modal")
    // Add logic to populate form with book data
  }
  
  function deleteBook(id) {
    if (confirm("Are you sure you want to delete this book?")) {
      // Add logic to delete book
      console.log("Deleting book:", id)
    }
  }
  
  function viewBook(id) {
    openModal("view-book-modal")
    // Add logic to show book details
  }
  
  // Student management functions
  function addStudent() {
    openModal("add-student-modal")
  }
  
  function editStudent(id) {
    openModal("edit-student-modal")
    // Add logic to populate form with student data
  }
  
  function deleteStudent(id) {
    if (confirm("Are you sure you want to delete this student?")) {
      // Add logic to delete student
      console.log("Deleting student:", id)
    }
  }
  
  function viewStudent(id) {
    openModal("view-student-modal")
    // Add logic to show student details
  }
  
  // User management functions
  function addUser() {
    openModal("add-user-modal")
  }
  
  function editUser(id) {
    openModal("edit-user-modal")
    // Add logic to populate form with user data
  }
  
  function deleteUser(id) {
    if (confirm("Are you sure you want to delete this user?")) {
      // Add logic to delete user
      console.log("Deleting user:", id)
    }
  }
  
  function viewUser(id) {
    openModal("view-user-modal")
    // Add logic to show user details
  }
  
  // Loan management functions
  function returnBook(id) {
    if (confirm("Are you sure you want to return this book?")) {
      // Add logic to return book
      console.log("Returning book:", id)
    }
  }
  
  // Form submission handlers
  document.addEventListener("DOMContentLoaded", () => {
    const forms = document.querySelectorAll("form")
  
    forms.forEach((form) => {
      form.addEventListener("submit", function (e) {
        e.preventDefault()
        // Add logic to handle form submission
        console.log("Form submitted:", this.id)
        // Close the modal after submission
        const modal = this.closest(".modal")
        if (modal) {
          closeModal(modal.id)
        }
      })
    })
  })
  