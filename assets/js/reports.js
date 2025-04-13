document.addEventListener('DOMContentLoaded', function() {
    // Load reports data
    loadReportsData();
});

// Load reports data
function loadReportsData() {
    // Load books by category chart
    loadBooksByCategoryChart();
    
    // Load monthly loans chart
    loadMonthlyLoansChart();
    
    // Load popular books
    loadPopularBooks();
}

// Load books by category chart
function loadBooksByCategoryChart() {
    fetch('../api/reports.php?report=books_by_category')
        .then(response => response.json())
        .then(data => {
            // Implement chart rendering using your preferred library
            // For example, you can use Chart.js
            console.log('Books by category data:', data);
            
            // Placeholder for chart implementation
            const chartContainer = document.querySelector('#books-by-category-chart');
            if (chartContainer) {
                chartContainer.innerHTML = 'Chart would be rendered here using the data';
            }
        })
        .catch(error => console.error('Error loading books by category chart:', error));
}

// Load monthly loans chart
function loadMonthlyLoansChart() {
    fetch('../api/reports.php?report=monthly_loans')
        .then(response => response.json())
        .then(data => {
            // Implement chart rendering using your preferred library
            console.log('Monthly loans data:', data);
            
            // Placeholder for chart implementation
            const chartContainer = document.querySelector('#monthly-loans-chart');
            if (chartContainer) {
                chartContainer.innerHTML = 'Chart would be rendered here using the data';
            }
        })
        .catch(error => console.error('Error loading monthly loans chart:', error));
}

// Load popular books
function loadPopularBooks() {
    fetch('../api/reports.php?report=popular_books')
        .then(response => response.json())
        .then(data => {
            const popularBooksTable = document.querySelector('#popular-books-table tbody');
            if (!popularBooksTable) return;
            
            popularBooksTable.innerHTML = '';
            
            if (data.length > 0) {
                data.forEach((book, index) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${book.title}</td>
                        <td>${book.author}</td>
                        <td>${book.borrow_count}</td>
                    `;
                    popularBooksTable.appendChild(row);
                });
            } else {
                popularBooksTable.innerHTML = `<tr><td colspan="4">No data available</td></tr>`;
            }
        })
        .catch(error => console.error('Error loading popular books:', error));
}