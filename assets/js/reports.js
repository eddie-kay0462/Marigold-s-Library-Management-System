document.addEventListener("DOMContentLoaded", () => {
  // DOM Elements
  const loanStatsContainer = document.getElementById("loan-stats-container")
  const overdueChartCanvas = document.getElementById("overdue-chart")
  const monthlyLoansChartCanvas = document.getElementById("monthly-loans-chart")
  const popularBooksChartCanvas = document.getElementById("popular-books-chart")
  const reportsTable = document.getElementById("reports-table")
  const reportsTableBody = document.getElementById("reports-table-body")
  const filterForm = document.getElementById("reports-filter-form")
  const exportBtn = document.getElementById("export-reports-btn")

  // Chart instances
  let overdueChart = null
  let monthlyLoansChart = null
  let popularBooksChart = null

  // Load all report data
  loadLoanStats()
  loadOverdueStats()
  loadMonthlyLoans()
  loadPopularBooks()
  loadLoanReports()

  // Add event listener for filter form
  if (filterForm) {
    filterForm.addEventListener("submit", (e) => {
      e.preventDefault()
      loadLoanReports()
    })
  }

  // Add event listener for export button
  if (exportBtn) {
    exportBtn.addEventListener("click", exportReportsToCSV)
  }

  // Function to load loan statistics
  function loadLoanStats() {
    fetch("../pages/reports/report_handler.php?action=get_loan_stats")
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          displayLoanStats(data.data)
        } else {
          console.error("Error loading loan stats:", data.message)
        }
      })
      .catch((error) => {
        console.error("Error loading loan stats:", error)
      })
  }

  // Function to load overdue statistics
  function loadOverdueStats() {
    fetch("../pages/reports/report_handler.php?action=get_overdue_stats")
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          displayOverdueChart(data.data)
        } else {
          console.error("Error loading overdue stats:", data.message)
        }
      })
      .catch((error) => {
        console.error("Error loading overdue stats:", error)
      })
  }

  // Function to load monthly loans data
  function loadMonthlyLoans() {
    fetch("../pages/reports/report_handler.php?action=get_monthly_loans")
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          displayMonthlyLoansChart(data.data)
        } else {
          console.error("Error loading monthly loans:", data.message)
        }
      })
      .catch((error) => {
        console.error("Error loading monthly loans:", error)
      })
  }

  // Function to load popular books
  function loadPopularBooks() {
    fetch("../pages/reports/report_handler.php?action=get_popular_books")
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          displayPopularBooksChart(data.data)
        } else {
          console.error("Error loading popular books:", data.message)
        }
      })
      .catch((error) => {
        console.error("Error loading popular books:", error)
      })
  }

  // Function to load loan reports with filtering
  function loadLoanReports() {
    // Get filter values
    const status = document.getElementById("filter-status")?.value || "all"
    const startDate = document.getElementById("filter-start-date")?.value || ""
    const endDate = document.getElementById("filter-end-date")?.value || ""
    const studentId = document.getElementById("filter-student")?.value || ""

    // Build query string
    let queryString = `action=get_loan_reports&status=${status}`
    if (startDate) queryString += `&start_date=${startDate}`
    if (endDate) queryString += `&end_date=${endDate}`
    if (studentId) queryString += `&student_id=${studentId}`

    fetch(`../pages/reports/report_handler.php?${queryString}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          displayLoanReports(data.data)
        } else {
          console.error("Error loading loan reports:", data.message)
        }
      })
      .catch((error) => {
        console.error("Error loading loan reports:", error)
      })
  }

  // Function to display loan statistics
  function displayLoanStats(stats) {
    if (!loanStatsContainer) return

    loanStatsContainer.innerHTML = `
      <div class="row">
        <div class="col-md-3 col-sm-6 col-12">
          <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-book-reader"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Loans</span>
              <span class="info-box-number">${stats.total_loans}</span>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
          <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-book"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Active Loans</span>
              <span class="info-box-number">${stats.active_loans}</span>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
          <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Overdue Loans</span>
              <span class="info-box-number">${stats.overdue_loans}</span>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 col-12">
          <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-undo-alt"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Returned Loans</span>
              <span class="info-box-number">${stats.returned_loans}</span>
            </div>
          </div>
        </div>
      </div>
    `
  }

  // Function to display overdue chart
  function displayOverdueChart(data) {
    if (!overdueChartCanvas) return

    // Prepare data for chart
    const labels = []
    const values = []
    const backgroundColors = [
      "rgba(255, 206, 86, 0.7)", // Yellow for 1-7 days
      "rgba(255, 159, 64, 0.7)", // Orange for 8-14 days
      "rgba(255, 99, 132, 0.7)", // Red for 15-30 days
      "rgba(153, 102, 255, 0.7)", // Purple for over 30 days
    ]

    // Default categories in case some are missing
    const defaultCategories = ["1-7 days", "8-14 days", "15-30 days", "Over 30 days"]
    const dataMap = new Map()

    // Initialize with zeros
    defaultCategories.forEach((category) => {
      dataMap.set(category, 0)
    })

    // Fill in actual data
    data.forEach((item) => {
      dataMap.set(item.overdue_range, Number.parseInt(item.count))
    })

    // Convert map to arrays for Chart.js
    dataMap.forEach((value, key) => {
      labels.push(key)
      values.push(value)
    })

    // Destroy previous chart if it exists
    if (overdueChart) {
      overdueChart.destroy()
    }

    // Create new chart
    overdueChart = new Chart(overdueChartCanvas, {
      type: "pie",
      data: {
        labels: labels,
        datasets: [
          {
            data: values,
            backgroundColor: backgroundColors,
            borderColor: backgroundColors.map((color) => color.replace("0.7", "1")),
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: "right",
          },
          title: {
            display: true,
            text: "Overdue Loans by Days",
          },
        },
      },
    })
  }

  // Function to display monthly loans chart
  function displayMonthlyLoansChart(data) {
    if (!monthlyLoansChartCanvas) return

    // Prepare data for chart
    const labels = data.map((item) => item.month)
    const values = data.map((item) => item.loan_count)

    // Destroy previous chart if it exists
    if (monthlyLoansChart) {
      monthlyLoansChart.destroy()
    }

    // Create new chart
    monthlyLoansChart = new Chart(monthlyLoansChartCanvas, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Number of Loans",
            data: values,
            backgroundColor: "rgba(54, 162, 235, 0.7)",
            borderColor: "rgba(54, 162, 235, 1)",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0,
            },
          },
        },
        plugins: {
          title: {
            display: true,
            text: "Monthly Loans This Year",
          },
        },
      },
    })
  }

  // Function to display popular books chart
  function displayPopularBooksChart(data) {
    if (!popularBooksChartCanvas) return

    // Prepare data for chart
    const labels = data.map((item) => truncateTitle(item.title, 20))
    const values = data.map((item) => item.borrow_count)

    // Destroy previous chart if it exists
    if (popularBooksChart) {
      popularBooksChart.destroy()
    }

    // Create new chart
    popularBooksChart = new Chart(popularBooksChartCanvas, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Times Borrowed",
            data: values,
            backgroundColor: "rgba(75, 192, 192, 0.7)",
            borderColor: "rgba(75, 192, 192, 1)",
            borderWidth: 1,
          },
        ],
      },
      options: {
        indexAxis: "y",
        responsive: true,
        scales: {
          x: {
            beginAtZero: true,
            ticks: {
              precision: 0,
            },
          },
        },
        plugins: {
          title: {
            display: true,
            text: "Most Popular Books",
          },
          tooltip: {
            callbacks: {
              title: (tooltipItems) => {
                // Get the original title from data
                const index = tooltipItems[0].dataIndex
                return data[index].title
              },
            },
          },
        },
      },
    })
  }

  // Function to display loan reports in table
  function displayLoanReports(reports) {
    if (!reportsTableBody) return

    reportsTableBody.innerHTML = ""

    if (reports.length === 0) {
      reportsTableBody.innerHTML = `
        <tr>
          <td colspan="6" class="text-center">No reports found</td>
        </tr>
      `
      return
    }

    reports.forEach((report) => {
      const row = document.createElement("tr")

      // Calculate if the loan is overdue
      const dueDate = new Date(report.due_date)
      const today = new Date()
      const isOverdue = today > dueDate && report.status === "Active"

      // Format dates
      const loanDateFormatted = formatDate(report.loan_date)
      const dueDateFormatted = formatDate(report.due_date)
      const returnedDateFormatted = report.returned_date ? formatDate(report.returned_date) : "-"

      row.innerHTML = `
        <td>${report.title}</td>
        <td>${report.student_number}</td>
        <td>${loanDateFormatted}</td>
        <td>${dueDateFormatted}</td>
        <td>${returnedDateFormatted}</td>
        <td>
          ${
            isOverdue
              ? '<span class="badge bg-danger">Overdue</span>'
              : report.status === "Active"
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-info">Returned</span>'
          }
        </td>
      `

      reportsTableBody.appendChild(row)
    })
  }

  // Function to export reports to CSV
  function exportReportsToCSV() {
    // Get filter values
    const status = document.getElementById("filter-status")?.value || "all"
    const startDate = document.getElementById("filter-start-date")?.value || ""
    const endDate = document.getElementById("filter-end-date")?.value || ""
    const studentId = document.getElementById("filter-student")?.value || ""

    // Build query string
    let queryString = `action=get_loan_reports&status=${status}`
    if (startDate) queryString += `&start_date=${startDate}`
    if (endDate) queryString += `&end_date=${endDate}`
    if (studentId) queryString += `&student_id=${studentId}`

    fetch(`../pages/reports/report_handler.php?${queryString}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.status === "success") {
          // Generate CSV
          const csvContent = generateCSV(data.data)

          // Create download link
          const encodedUri = encodeURI(csvContent)
          const link = document.createElement("a")
          link.setAttribute("href", "data:text/csv;charset=utf-8," + encodedUri)
          link.setAttribute("download", "loan_reports.csv")
          document.body.appendChild(link)

          // Trigger download
          link.click()
          document.body.removeChild(link)
        } else {
          console.error("Error exporting reports:", data.message)
          alert("Error exporting reports")
        }
      })
      .catch((error) => {
        console.error("Error exporting reports:", error)
        alert("Error exporting reports")
      })
  }

  // Function to generate CSV content
  function generateCSV(data) {
    // CSV header
    let csv = "Title,Student Number,Loan Date,Due Date,Returned Date,Status\n"

    // Add rows
    data.forEach((report) => {
      const loanDateFormatted = formatDate(report.loan_date)
      const dueDateFormatted = formatDate(report.due_date)
      const returnedDateFormatted = report.returned_date ? formatDate(report.returned_date) : "-"

      // Escape fields that might contain commas
      const escapedTitle = `"${report.title.replace(/"/g, '""')}"`

      csv += `${escapedTitle},${report.student_number},${loanDateFormatted},${dueDateFormatted},${returnedDateFormatted},${report.status}\n`
    })

    return csv
  }

  // Utility function to truncate book titles
  function truncateTitle(title, maxLength) {
    if (title.length <= maxLength) return title
    return title.substring(0, maxLength) + "..."
  }

  // Utility function for formatting dates
  function formatDate(dateString) {
    const options = { year: "numeric", month: "long", day: "numeric" }
    return new Date(dateString).toLocaleDateString(undefined, options)
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
})
