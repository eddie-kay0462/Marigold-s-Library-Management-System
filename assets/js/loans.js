document.addEventListener("DOMContentLoaded", () => {
    // DOM Elements
    const studentSearchInput = document.getElementById("borrow-student")
    const studentSearchResults = document.getElementById("student-search-results")
    const bookSearchInput = document.getElementById("borrow-book")
    const bookSearchResults = document.getElementById("book-search-results")
    const borrowForm = document.getElementById("borrow-form")
    const dueDateInput = document.getElementById("due-date")
    const confirmBorrowBtn = document.getElementById("confirm-borrow-btn")
    const activeLoansTable = document.getElementById("active-loans-table")
  
    // Set default due date (14 days from today)
    const defaultDueDate = new Date()
    defaultDueDate.setDate(defaultDueDate.getDate() + 14)
    dueDateInput.valueAsDate = defaultDueDate
  
    // Selected student and book data
    let selectedStudent = null
    let selectedBook = null
  
    // Load active loans when the page loads
    loadActiveLoans()
  
    // Student search functionality
    studentSearchInput.addEventListener(
      "input",
      debounce((e) => {
        const searchTerm = e.target.value.trim()
        if (searchTerm.length < 2) {
          studentSearchResults.style.display = "none"
          return
        }
  
        fetch(`loans/loan_handler.php?action=search_students&query=${encodeURIComponent(searchTerm)}`)
          .then((response) => response.json())
          .then((data) => {
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
          })
      }, 300),
    )
  
    // Book search functionality
    bookSearchInput.addEventListener(
      "input",
      debounce((e) => {
        const searchTerm = e.target.value.trim()
        if (searchTerm.length < 2) {
          bookSearchResults.style.display = "none"
          return
        }
  
        fetch(`loans/loan_handler.php?action=search_books&query=${encodeURIComponent(searchTerm)}`)
          .then((response) => response.json())
          .then((data) => {
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
          })
      }, 300),
    )
  
    // Hide search results when clicking outside
    document.addEventListener("click", (e) => {
      if (!studentSearchInput.contains(e.target) && !studentSearchResults.contains(e.target)) {
        studentSearchResults.style.display = "none"
      }
      if (!bookSearchInput.contains(e.target) && !bookSearchResults.contains(e.target)) {
        bookSearchResults.style.display = "none"
      }
    })
  
    // Form submission
    borrowForm.addEventListener("submit", (e) => {
      e.preventDefault()
  
      if (!selectedStudent) {
        showError("Please select a student")
        return
      }
  
      if (!selectedBook) {
        showError("Please select a book")
        return
      }
  
      if (!dueDateInput.value) {
        showError("Please select a due date")
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
  
      fetch("loans/loan_handler.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.status === "success") {
            showSuccess(data.message)
            resetBorrowForm()
            loadActiveLoans()
                        } else {
            showError(data.message)
          }
        })
        .catch((error) => {
          console.error("Error creating loan:", error)
          showError("An error occurred while processing your request")
        })
        .finally(() => {
          // Re-enable button
          confirmBorrowBtn.disabled = false
          confirmBorrowBtn.innerHTML = '<i class="fas fa-check"></i> Confirm Borrow'
        })
    })
  
    // Function to display student search results
    function displayStudentResults(students) {
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
        })
        studentSearchResults.appendChild(resultItem)
      })
      studentSearchResults.style.display = "block"
    }
  
    // Function to display book search results
    function displayBookResults(books) {
      bookSearchResults.innerHTML = ""
      books.forEach((book) => {
        // Only show books with available copies
        if (book.available_copies > 0) {
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
          })
          bookSearchResults.appendChild(resultItem)
        }
      })
  
      // If no books with available copies were found
      if (bookSearchResults.children.length === 0) {
        bookSearchResults.innerHTML = `<div class="search-result-item">No available books found</div>`
      }
  
      bookSearchResults.style.display = "block"
    }
  
    // Function to load active loans
function loadActiveLoans() {
      fetch("loans/loan_handler.php?action=get_active_loans")
        .then((response) => response.json())
        .then((data) => {
          if (data.status === "success") {
            displayActiveLoans(data.data)
                } else {
            console.error("Error loading active loans:", data.message)
          }
        })
        .catch((error) => {
          console.error("Error loading active loans:", error)
        })
    }
  
    // Function to display active loans
    function displayActiveLoans(loans) {
      if (!activeLoansTable) return
  
      activeLoansTable.innerHTML = ""
  
      if (loans.length === 0) {
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
        const isOverdue = today > dueDate && loan.status !== "Returned"
  
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
  
    // Function to reset the borrow form
    function resetBorrowForm() {
      selectedStudent = null
      selectedBook = null
      studentSearchInput.value = ""
      bookSearchInput.value = ""
      studentSearchInput.classList.remove("selected-item")
      bookSearchInput.classList.remove("selected-item")
  
      // Reset due date to default (14 days from today)
      const defaultDueDate = new Date()
      defaultDueDate.setDate(defaultDueDate.getDate() + 14)
      dueDateInput.valueAsDate = defaultDueDate
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
  
  // Function to return a book
  function returnBook(loanId) {
    if (!confirm("Are you sure you want to return this book?")) {
      return
    }
  
    const formData = new FormData()
    formData.append("action", "return_book")
    formData.append("loan_id", loanId)
  
    fetch("loans/loan_handler.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          showSuccess(data.message)
          // Reload active loans to update the table
          setTimeout(() => {
            window.location.reload()
          }, 1500)
        } else {
          showError(data.message)
        }
      })
      .catch((error) => {
        console.error("Error returning book:", error)
        showError("An error occurred while processing your request")
      })
  }
  
  // Utility function for formatting dates
  function formatDate(dateString) {
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
  
  // Function to show error messages
  function showError(message) {
    // Create error message container if it doesn't exist
    let errorContainer = document.querySelector(".error-message-container")
    if (!errorContainer) {
      errorContainer = document.createElement("div")
      errorContainer.className = "error-message-container"
      document.body.appendChild(errorContainer)
    }
  
    // Create the error message element
    const errorDiv = document.createElement("div")
    errorDiv.className = "error-message"
    errorDiv.innerHTML = `
      <i class="fas fa-exclamation-circle"></i>
      <span>${message}</span>
    `
  
    // Add the message to the container
    errorContainer.appendChild(errorDiv)
  
    // Add animation class
    setTimeout(() => {
      errorDiv.classList.add("show")
    }, 10)
  
    // Remove the message after 3 seconds
    setTimeout(() => {
      errorDiv.classList.add("hide")
      setTimeout(() => {
        errorDiv.remove()
        // Remove container if no more messages
        if (errorContainer.children.length === 0) {
          errorContainer.remove()
        }
      }, 300) // Wait for fade out animation
    }, 3000)
  }
  
  // Function to show success messages
  function showSuccess(message) {
    // Create success message container if it doesn't exist
    let successContainer = document.querySelector(".success-message-container")
    if (!successContainer) {
      successContainer = document.createElement("div")
      successContainer.className = "success-message-container"
      document.body.appendChild(successContainer)
    }
  
    // Create the success message element
    const successDiv = document.createElement("div")
    successDiv.className = "success-message"
    successDiv.innerHTML = `
      <i class="fas fa-check-circle"></i>
      <span>${message}</span>
    `
  
    // Add the message to the container
    successContainer.appendChild(successDiv)
  
    // Add animation class
    setTimeout(() => {
      successDiv.classList.add("show")
    }, 10)
  
    // Remove the message after 3 seconds
    setTimeout(() => {
      successDiv.classList.add("hide")
      setTimeout(() => {
        successDiv.remove()
        // Remove container if no more messages
        if (successContainer.children.length === 0) {
          successContainer.remove()
        }
      }, 300) // Wait for fade out animation
    }, 3000)
  }
  