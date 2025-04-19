document.addEventListener("DOMContentLoaded", () => {
  loadBooks()

  // Search functionality
  const searchInput = document.querySelector("#searchBooks")
  if (searchInput) {
    searchInput.addEventListener("input", (e) => {
      const searchTerm = e.target.value.toLowerCase()
      filterBooks(searchTerm)
    })
  }
})

function loadBooks() {
  fetch("books/load_books.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayBooks(data.data)
      } else {
        showErrorMessage(data.error || "Error loading books")
      }
    })
    .catch((error) => {
      showErrorMessage("Failed to load books. Please try again later.")
      console.error("Error:", error)
    })
}

function displayBooks(books) {
  const tableBody = document.getElementById("books-table-body")
  if (!tableBody) return

  tableBody.innerHTML = ""

  if (books.length === 0) {
    tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center">No books found</td>
            </tr>
        `
    return
  }

  books.forEach((book) => {
    const row = document.createElement("tr")
    row.setAttribute("data-book-id", book.book_id)
    row.innerHTML = `
            <td>${book.book_id}</td>
            <td>${book.isbn}</td>
            <td>${book.title}</td>
            <td>${book.author}</td>
            <td>${book.category_name}</td>
            <td>${book.available_copies}</td>
            <td>${book.total_copies}</td>
            <td>
                <button type="button" class="btn btn-secondary" onclick="editBook('${book.book_id}')">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-danger" onclick="deleteBook('${book.book_id}')">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <button type="button" class="btn btn-info" onclick="viewBook('${book.book_id}')">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        `
    tableBody.appendChild(row)
  })
}

function filterBooks(searchTerm) {
  const rows = document.querySelectorAll("#booksTable tbody tr")

  rows.forEach((row) => {
    const text = row.textContent.toLowerCase()
    row.style.display = text.includes(searchTerm) ? "" : "none"
  })
}

function viewBook(bookId) {
  fetch(`../pages/books/book_handler.php?action=get&book_id=${bookId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        const book = data.data

        // Create modal if it doesn't exist
        let viewModal = document.getElementById("view-book-modal")
        if (!viewModal) {
          viewModal = document.createElement("div")
          viewModal.id = "view-book-modal"
          viewModal.className = "modal"
          document.body.appendChild(viewModal)
        }

        // Update modal content
        viewModal.innerHTML = `
                    <div class="modal-content" style="background: white; padding: 20px; border-radius: 8px; width: 500px; max-width: 90%; position: relative; margin: 10% auto;">
                        <span class="close-modal" onclick="closeBookViewModal()" style="position: absolute; right: 20px; top: 10px; font-size: 24px; cursor: pointer; color: #666;">&times;</span>
                        <div class="book-details">
                            <h2 style="color: #2C3E50; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-book" style="color: #FF8303;"></i> 
                                Book Details
                            </h2>
                            <div class="detail-row" style="display: flex; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                                <label style="font-weight: 600; width: 150px; color: #2C3E50;">ISBN:</label>
                                <span style="color: #2C3E50;">${book.isbn}</span>
                            </div>
                            <div class="detail-row" style="display: flex; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                                <label style="font-weight: 600; width: 150px; color: #2C3E50;">Title:</label>
                                <span style="color: #2C3E50;">${book.title}</span>
                            </div>
                            <div class="detail-row" style="display: flex; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                                <label style="font-weight: 600; width: 150px; color: #2C3E50;">Author:</label>
                                <span style="color: #2C3E50;">${book.author}</span>
                            </div>
                            <div class="detail-row" style="display: flex; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                                <label style="font-weight: 600; width: 150px; color: #2C3E50;">Category:</label>
                                <span style="color: #2C3E50;">${book.category_name}</span>
                            </div>
                            <div class="detail-row" style="display: flex; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                                <label style="font-weight: 600; width: 150px; color: #2C3E50;">Available Copies:</label>
                                <span style="color: #2C3E50;">${book.available_copies}</span>
                            </div>
                            <div class="detail-row" style="display: flex; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                                <label style="font-weight: 600; width: 150px; color: #2C3E50;">Total Copies:</label>
                                <span style="color: #2C3E50;">${book.total_copies}</span>
                            </div>
                            <div class="detail-row" style="display: flex; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; transition: all 0.3s ease;">
                                <label style="font-weight: 600; width: 150px; color: #2C3E50;">Status:</label>
                                <span style="color: #2C3E50;">${
                                  book.available_copies > 0
                                    ? '<span class="status-badge status-active">Available</span>'
                                    : '<span class="status-badge status-overdue">Not Available</span>'
                                }</span>
                            </div>
                        </div>
                    </div>
                `

        // Add modal styles if not already present
        if (!document.getElementById("modal-styles")) {
          const style = document.createElement("style")
          style.id = "modal-styles"
          style.textContent = `
                        .modal {
                            display: none;
                            position: fixed;
                            z-index: 1000;
                            left: 0;
                            top: 0;
                            width: 100%;
                            height: 100%;
                            background-color: rgba(0, 0, 0, 0.5);
                            animation: fadeIn 0.3s ease;
                        }

                        @keyframes fadeIn {
                            from { opacity: 0; }
                            to { opacity: 1; }
                        }

                        .modal-content {
                            animation: slideIn 0.3s ease;
                        }

                        @keyframes slideIn {
                            from {
                                transform: translateY(-20px);
                                opacity: 0;
                            }
                            to {
                                transform: translateY(0);
                                opacity: 1;
                            }
                        }

                        .detail-row:hover {
                            background: #fff !important;
                            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                        }

                        .close-modal:hover {
                            color: #ff0000 !important;
                        }

                        .status-badge {
                            display: inline-block;
                            padding: 4px 8px;
                            border-radius: 4px;
                            font-size: 0.8rem;
                            font-weight: 500;
                        }

                        .status-active {
                            background-color: #e8f5e9;
                            color: #388e3c;
                        }

                        .status-overdue {
                            background-color: #ffebee;
                            color: #d32f2f;
                        }
                    `
          document.head.appendChild(style)
        }

        // Show modal
        viewModal.style.display = "block"
      } else {
        showErrorMessage(data.message)
      }
    })
    .catch((error) => {
      showErrorMessage("Error loading book details")
      console.error("Error:", error)
    })
}

function closeBookViewModal() {
  const viewModal = document.getElementById("view-book-modal")
  if (viewModal) {
    viewModal.style.display = "none"
  }
}

window.addEventListener("click", (event) => {
  const viewModal = document.getElementById("view-book-modal")
  if (event.target === viewModal) {
    closeBookViewModal()
  }
})

// Fix the editBook function to properly fetch categories and show the edit form
function editBook(bookId) {
  console.log("Editing book with ID:", bookId)

  // First, fetch the book details - use the correct path
  fetch(`../pages/books/book_handler.php?action=get&book_id=${bookId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        // Then fetch all categories to populate the dropdown
        fetch("../pages/books/get_categories.php")
          .then((response) => response.json())
          .then((categoryData) => {
            if (categoryData.success) {
              showEditForm(data.data, categoryData.data)
            } else {
              showErrorMessage(categoryData.error || "Error loading categories")
            }
          })
          .catch((error) => {
            showErrorMessage("Failed to load categories")
            console.error("Error:", error)
          })
      } else {
        showErrorMessage(data.message || "Error loading book details")
      }
    })
    .catch((error) => {
      showErrorMessage("Failed to load book details")
      console.error("Error:", error)
    })
}

function showEditForm(book, categories) {
  const modal = document.createElement("div")
  modal.className = "modal"
  modal.style.display = "block"

  // Create category options for the dropdown
  let categoryOptions = ""
  categories.forEach((category) => {
    const selected = category.category_id == book.category_id ? "selected" : ""
    categoryOptions += `<option value="${category.category_id}" ${selected}>${category.category_name}</option>`
  })

  modal.innerHTML = `
        <div class="modal-content">
            <span class="close-modal" onclick="this.parentElement.parentElement.remove()">&times;</span>
            <h2>Edit Book</h2>
            <form id="edit-book-form" onsubmit="updateBook(event, '${book.book_id}')">
                <div class="form-group">
                    <label for="isbn">ISBN</label>
                    <input type="text" id="isbn" name="isbn" value="${book.isbn}" required>
                </div>
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="${book.title}" required>
                </div>
                <div class="form-group">
                    <label for="author">Author</label>
                    <input type="text" id="author" name="author" value="${book.author}" required>
                </div>
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" required>
                        ${categoryOptions}
                    </select>
                </div>
                <div class="form-group">
                    <label for="available_copies">Available Copies</label>
                    <input type="number" id="available_copies" name="available_copies" value="${book.available_copies}" required min="0">
                </div>
                <div class="form-group">
                    <label for="total_copies">Total Copies</label>
                    <input type="number" id="total_copies" name="total_copies" value="${book.total_copies}" required min="0">
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    `

  document.body.appendChild(modal)
}

function showSuccessMessage(message) {
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

// Add this CSS to the page
const style = document.createElement("style")
style.textContent = `
    .success-message-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }

    .success-message {
        background: linear-gradient(135deg, #4CAF50, #45a049);
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        transform: translateX(120%);
        transition: transform 0.3s ease-out;
        opacity: 0;
    }

    .success-message i {
        font-size: 1.2rem;
    }

    .success-message.show {
        transform: translateX(0);
        opacity: 1;
    }

    .success-message.hide {
        transform: translateX(120%);
        opacity: 0;
    }
`
document.head.appendChild(style)

// Fix the updateBook function to use the correct path for update_book.php
function updateBook(event, bookId) {
  event.preventDefault()
  const form = event.target
  const formData = new FormData(form)
  formData.append("book_id", bookId)
  formData.append("action", "edit") // Add action parameter for book_handler.php

  // Show loading state
  const submitButton = form.querySelector('button[type="submit"]')
  const originalButtonText = submitButton.innerHTML
  submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...'
  submitButton.disabled = true

  fetch("../pages/books/book_handler.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        // Update the UI with the data returned from the server
        loadBooks() // Reload all books to refresh the table

        // Close modal and show success message
        form.closest(".modal").remove()
        showSuccessMessage("Book updated successfully")
      } else {
        showErrorMessage(data.message || "Error updating book")
      }
    })
    .catch((error) => {
      showErrorMessage("Failed to update book")
      console.error("Error:", error)
    })
    .finally(() => {
      // Reset button state
      submitButton.innerHTML = originalButtonText
      submitButton.disabled = false
    })
}

function deleteBook(bookId) {
  if (!confirm("Are you sure you want to delete this book?")) {
    return
  }

  const formData = new FormData()
  formData.append("book_id", bookId)

  fetch("books/delete_book.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showSuccessMessage("Book deleted successfully")
        // Remove the row directly instead of reloading
        const row = document.querySelector(`tr[data-book-id="${bookId}"]`)
        if (row) {
          row.remove()
        }
      } else {
        showErrorMessage(data.error || "Error deleting book")
      }
    })
    .catch((error) => {
      showErrorMessage("Failed to delete book")
      console.error("Error:", error)
    })
}

function showErrorMessage(message) {
  const errorDiv = document.createElement("div")
  errorDiv.className = "alert alert-danger"
  errorDiv.textContent = message

  const container = document.querySelector(".main-content")
  if (container) {
    container.insertBefore(errorDiv, container.firstChild)

    setTimeout(() => errorDiv.remove(), 3000)
  }
}
