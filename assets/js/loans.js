// Function to load active loans
function loadActiveLoans() {
  // Add console log for debugging
  console.log("Loading active loans...")

  fetch("../pages/loans/loan_handler.php?action=get_active_loans")
    .then((response) => {
      console.log("Response received:", response)
      return response.json()
    })
    .then((data) => {
      console.log("Active loans data:", data)
      if (data.status === "success") {
        displayActiveLoans(data.data)
      } else {
        console.error("Error loading active loans:", data.message)
        showErrorMessage(data.message || "Error loading active loans")
      }
    })
    .catch((error) => {
      console.error("Error loading active loans:", error)
      showErrorMessage("Failed to load active loans. Please try again.")
    })
}

// Modify the displayActiveLoans function to ensure it properly shows overdue status
function displayActiveLoans(loans) {
  const activeLoansTable = document.getElementById("active-loans-table")
  if (!activeLoansTable) {
    console.error("Active loans table not found")
    return
  }

  activeLoansTable.innerHTML = ""

  if (!loans || loans.length === 0) {
    activeLoansTable.innerHTML = `
      <tr>
        <td colspan="7" class="text-center">No active loans found</td>
      </tr>
    `
    return
  }

  loans.forEach((loan) => {
    const row = document.createElement("tr")

    // Calculate if the loan is overdue
    const dueDate = new Date(loan.due_date)
    const today = new Date()
    // Set hours to 0 to compare just the dates
    today.setHours(0, 0, 0, 0)
    dueDate.setHours(0, 0, 0, 0)
    const isOverdue = today >= dueDate || loan.status === "Overdue"

    // Format dates
    const loanDateFormatted = formatDate(loan.loan_date)
    const dueDateFormatted = formatDate(loan.due_date)

    row.innerHTML = `
      <td>${loan.book_id}</td>
      <td>${loan.title}</td>
      <td>${loan.first_name} ${loan.last_name} (${loan.student_number})</td>
      <td>${loanDateFormatted}</td>
      <td>${dueDateFormatted}</td>
      <td>
        ${
          isOverdue
            ? '<span class="status-badge status-overdue">Overdue</span>'
            : '<span class="status-badge status-active">Active</span>'
        }
      </td>
      <td>
        <button type="button" class="btn btn-primary" onclick="returnBook(${loan.loan_id})">
          <i class="fas fa-undo-alt"></i> Return
        </button>
      </td>
    `
    activeLoansTable.appendChild(row)
  })
}

// Global variables for student and book search
let studentSearchInput
let studentSearchResults
let bookSearchInput
let bookSearchResults
let selectedStudent = null
let selectedBook = null

// Declare showErrorMessage and showSuccessMessage
// const showErrorMessage = (message) => {
//   alert("Error: " + message)
// }

// const showSuccessMessage = (message) => {
//   alert("Success: " + message)
// }

// Initialize the loan system
document.addEventListener("DOMContentLoaded", () => {
  console.log("Loans module initializing...")

  // DOM Elements
  studentSearchInput = document.getElementById("borrow-student")
  studentSearchResults = document.getElementById("student-search-results")
  bookSearchInput = document.getElementById("borrow-book")
  bookSearchResults = document.getElementById("book-search-results")
  const borrowForm = document.getElementById("borrow-form")
  const dueDateInput = document.getElementById("due-date")
  const confirmBorrowBtn = document.getElementById("confirm-borrow-btn")

  console.log("Student search input:", studentSearchInput)
  console.log("Book search input:", bookSearchInput)
  console.log("Borrow form:", borrowForm)

  // Set default due date (14 days from today)
  if (dueDateInput) {
    const defaultDueDate = new Date()
    defaultDueDate.setDate(defaultDueDate.getDate() + 14)
    dueDateInput.valueAsDate = defaultDueDate
  }

  // Load active loans when the page loads
  loadActiveLoans()

  // Student search functionality
  if (studentSearchInput && studentSearchResults) {
    studentSearchInput.addEventListener(
      "input",
      debounce((e) => {
        const searchTerm = e.target.value.trim()
        if (searchTerm.length < 2) {
          studentSearchResults.style.display = "none"
          return
        }

        console.log("Searching for student:", searchTerm)

        fetch(`../pages/loans/loan_handler.php?action=search_students&query=${encodeURIComponent(searchTerm)}`)
          .then((response) => response.json())
          .then((data) => {
            console.log("Student search results:", data)
            if (data.status === "success" && data.data.length > 0) {
              displayStudentResults(data.data)
            } else {
              studentSearchResults.innerHTML = `<div class="search-result-item">No students found</div>`
              studentSearchResults.style.display = "block"
            }
          })
          .catch((error) => {
            console.error("Error searching students:", error)
            studentSearchResults.innerHTML = `<div class="search-result-item">Error searching students</div>`
            studentSearchResults.style.display = "block"
            showErrorMessage("Error searching students")
          })
      }, 300),
    )
  }

  // Book search functionality
  if (bookSearchInput && bookSearchResults) {
    bookSearchInput.addEventListener(
      "input",
      debounce((e) => {
        const searchTerm = e.target.value.trim()
        if (searchTerm.length < 2) {
          bookSearchResults.style.display = "none"
          return
        }

        console.log("Searching for book:", searchTerm)

        fetch(`../pages/loans/loan_handler.php?action=search_books&query=${encodeURIComponent(searchTerm)}`)
          .then((response) => response.json())
          .then((data) => {
            console.log("Book search results:", data)
            if (data.status === "success" && data.data.length > 0) {
              displayBookResults(data.data)
            } else {
              bookSearchResults.innerHTML = `<div class="search-result-item">No books found</div>`
              bookSearchResults.style.display = "block"
            }
          })
          .catch((error) => {
            console.error("Error searching books:", error)
            bookSearchResults.innerHTML = `<div class="search-result-item">Error searching books</div>`
            bookSearchResults.style.display = "block"
            showErrorMessage("Error searching books")
          })
      }, 300),
    )
  }

  // Hide search results when clicking outside
  if (studentSearchInput && studentSearchResults && bookSearchInput && bookSearchResults) {
    document.addEventListener("click", (e) => {
      if (!studentSearchInput.contains(e.target) && !studentSearchResults.contains(e.target)) {
        studentSearchResults.style.display = "none"
      }
      if (!bookSearchInput.contains(e.target) && !bookSearchResults.contains(e.target)) {
        bookSearchResults.style.display = "none"
      }
    })
  }

  // Form submission
  if (borrowForm) {
    borrowForm.addEventListener("submit", (e) => {
      e.preventDefault()
      console.log("Borrow form submitted")

      if (!selectedStudent) {
        showErrorMessage("Please select a student")
        return
      }

      if (!selectedBook) {
        showErrorMessage("Please select a book")
        return
      }

      if (!dueDateInput.value) {
        showErrorMessage("Please select a due date")
        return
      }

      // Disable button to prevent multiple submissions
      confirmBorrowBtn.disabled = true
      confirmBorrowBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...'

      const formData = new FormData()
      formData.append("action", "create_loan")
      formData.append("student_id", selectedStudent.student_id)
      formData.append("book_id", selectedBook.book_id)
      formData.append("due_date", dueDateInput.value)

      console.log("Creating loan with:", {
        student_id: selectedStudent.student_id,
        book_id: selectedBook.book_id,
        due_date: dueDateInput.value,
      })

      fetch("../pages/loans/loan_handler.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          console.log("Loan creation response:", data)
          if (data.status === "success") {
            showSuccessMessage(data.message)
            resetBorrowForm()
            loadActiveLoans()
          } else {
            showErrorMessage(data.message || "Failed to create loan")
          }
        })
        .catch((error) => {
          console.error("Error creating loan:", error)
          showErrorMessage("An error occurred while processing your request")
        })
        .finally(() => {
          // Re-enable button
          confirmBorrowBtn.disabled = false
          confirmBorrowBtn.innerHTML = '<i class="fas fa-check"></i> Confirm Borrow'
        })
    })
  }

  // Add styles for the search results and selected items
  const style = document.createElement("style")
  style.textContent = `
    .search-container {
        position: relative;
    }

    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 0 0 8px 8px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 100;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .search-result-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        transition: background-color 0.2s ease;
    }

    .search-result-item:last-child {
        border-bottom: none;
    }

    .search-result-item:hover {
        background-color: #f5f5f5;
    }

    .selected-item {
      border-color: #4CAF50 !important;
      background-color: #f1f8e9 !important;
    }

    .availability-badge {
      float: right;
      background-color: #e8f5e9;
      color: #388e3c;
      padding: 2px 6px;
      border-radius: 4px;
      font-size: 0.8rem;
      font-weight: 500;
    }
  `
  document.head.appendChild(style)
})

// Function to display student search results
function displayStudentResults(students) {
  if (!studentSearchResults) return

  studentSearchResults.innerHTML = ""
  students.forEach((student) => {
    const resultItem = document.createElement("div")
    resultItem.className = "search-result-item"
    resultItem.innerHTML = `
      <strong>${student.student_number}</strong> - ${student.first_name} ${student.last_name}
    `
    resultItem.addEventListener("click", () => {
      selectedStudent = student
      studentSearchInput.value = `${student.student_number} - ${student.first_name} ${student.last_name}`
      studentSearchResults.style.display = "none"

      // Add visual indication that student is selected
      studentSearchInput.classList.add("selected-item")

      console.log("Selected student:", selectedStudent)
    })
    studentSearchResults.appendChild(resultItem)
  })
  studentSearchResults.style.display = "block"
}

// Function to display book search results
function displayBookResults(books) {
  if (!bookSearchResults) return

  bookSearchResults.innerHTML = ""
  let availableBooksFound = false

  books.forEach((book) => {
    // Only show books with available copies
    if (book.available_copies > 0) {
      availableBooksFound = true
      const resultItem = document.createElement("div")
      resultItem.className = "search-result-item"
      resultItem.innerHTML = `
        <strong>${book.title}</strong> - ${book.author} (ISBN: ${book.isbn})
        <span class="availability-badge">Available: ${book.available_copies}</span>
      `
      resultItem.addEventListener("click", () => {
        selectedBook = book
        bookSearchInput.value = `${book.title} - ${book.author}`
        bookSearchResults.style.display = "none"

        // Add visual indication that book is selected
        bookSearchInput.classList.add("selected-item")

        console.log("Selected book:", selectedBook)
      })
      bookSearchResults.appendChild(resultItem)
    }
  })

  // If no books with available copies were found
  if (!availableBooksFound) {
    bookSearchResults.innerHTML = `<div class="search-result-item">No available books found</div>`
  }

  bookSearchResults.style.display = "block"
}

// Function to reset the borrow form
function resetBorrowForm() {
  if (!studentSearchInput || !bookSearchInput) return

  selectedStudent = null
  selectedBook = null
  studentSearchInput.value = ""
  bookSearchInput.value = ""
  studentSearchInput.classList.remove("selected-item")
  bookSearchInput.classList.remove("selected-item")

  // Reset due date to default (14 days from today)
  const dueDateInput = document.getElementById("due-date")
  if (dueDateInput) {
    const defaultDueDate = new Date()
    defaultDueDate.setDate(defaultDueDate.getDate() + 14)
    dueDateInput.valueAsDate = defaultDueDate
  }

  console.log("Borrow form reset")
}

// Function to return a book
window.returnBook = (loanId) => {
  if (!confirm("Are you sure you want to return this book?")) {
    return
  }

  console.log("Returning book with loan ID:", loanId)

  if (!loanId) {
    showErrorMessage("Loan ID is missing")
    return
  }

  const formData = new FormData()
  formData.append("action", "return_book")
  formData.append("loan_id", loanId)

  // Debug output to console
  console.log("Form data being sent:", {
    action: "return_book",
    loan_id: loanId,
  })

  fetch("../pages/loans/loan_handler.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      console.log("Raw response:", response)
      return response.json()
    })
    .then((data) => {
      console.log("Return book response:", data)
      if (data.status === "success") {
        showSuccessMessage(data.message)
        loadActiveLoans()
      } else {
        showErrorMessage(data.message || "Failed to return book")
      }
    })
    .catch((error) => {
      console.error("Error returning book:", error)
      showErrorMessage("An error occurred while processing your request")
    })
}

// Utility function for formatting dates
function formatDate(dateString) {
  if (!dateString) return "N/A"
  const options = { year: "numeric", month: "long", day: "numeric" }
  return new Date(dateString).toLocaleDateString(undefined, options)
}

// Utility function for debouncing
function debounce(func, wait) {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

// Use our custom notification system if available, otherwise fallback to alerts
function showSuccessMessage(message) {
  if (typeof window.showSuccessMessage === "function" && window.showSuccessMessage !== showSuccessMessage) {
    window.showSuccessMessage(message)
  } else {
    alert("Success: " + message)
  }
}

function showErrorMessage(message) {
  if (typeof window.showErrorMessage === "function" && window.showErrorMessage !== showErrorMessage) {
    window.showErrorMessage(message)
  } else {
    alert("Error: " + message)
  }
}

// Initialize immediately if document is already loaded
if (document.readyState === "complete" || document.readyState === "interactive") {
  console.log("Document already loaded, initializing loans module...")
  const event = new Event("DOMContentLoaded")
  document.dispatchEvent(event)
}
