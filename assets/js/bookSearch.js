// Book search functionality
class BookSearch {
    constructor() {
        this.searchInput = document.getElementById('book-search');
        this.tbody = document.querySelector('#books table tbody');
        this.setupEventListeners();
        this.setupStyles();
    }

    setupEventListeners() {
        if (this.searchInput) {
            let searchTimeout;
            this.searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => this.filterBooks(), 300);
            });
        }
    }

    filterBooks() {
        if (!this.tbody || !this.searchInput) return;

        const searchTerm = this.searchInput.value.toLowerCase().trim();
        const rows = this.tbody.getElementsByTagName('tr');
        let found = false;

        // Remove existing "no results" message
        this.removeNoResultsMessage();

        // Show all rows if search is empty
        if (searchTerm === '') {
            Array.from(rows).forEach(row => {
                if (!row.classList.contains('no-results')) {
                    row.style.display = '';
                }
            });
            return;
        }

        // Filter rows
        Array.from(rows).forEach(row => {
            if (row.classList.contains('no-results')) return;

            const cells = row.getElementsByTagName('td');
            if (cells.length === 0) return;

            const bookId = cells[0].textContent.toLowerCase();
            const isbn = cells[1].textContent.toLowerCase();
            const title = cells[2].textContent.toLowerCase();
            const author = cells[3].textContent.toLowerCase();
            const category = cells[4].textContent.toLowerCase();

            const showRow = this.shouldShowRow(searchTerm, {
                bookId, isbn, title, author, category
            });

            if (showRow) {
                row.style.display = '';
                found = true;
                this.highlightText(cells[2], searchTerm); // Title
                this.highlightText(cells[3], searchTerm); // Author
                this.highlightText(cells[4], searchTerm); // Category
            } else {
                row.style.display = 'none';
                this.removeHighlights(cells);
            }
        });

        if (!found && searchTerm !== '') {
            this.showNoResultsMessage(searchTerm);
        }
    }

    shouldShowRow(searchTerm, fields) {
        // Basic search for 2+ characters
        if (searchTerm.length >= 2) {
            if (Object.values(fields).some(field => field.includes(searchTerm))) {
                return true;
            }
        }

        // Advanced search for 3+ characters
        if (searchTerm.length >= 3) {
            const { title, author, category } = fields;
            return [title, author, category].some(field => 
                field.split(' ').some(word => word.startsWith(searchTerm))
            );
        }

        return false;
    }

    highlightText(cell, searchTerm) {
        if (!cell || !searchTerm) return;
        
        const text = cell.textContent;
        const searchTermLower = searchTerm.toLowerCase();
        const textLower = text.toLowerCase();
        
        if (textLower.includes(searchTermLower)) {
            const startIndex = textLower.indexOf(searchTermLower);
            const endIndex = startIndex + searchTerm.length;
            
            cell.innerHTML = 
                text.substring(0, startIndex) +
                '<span class="highlight">' +
                text.substring(startIndex, endIndex) +
                '</span>' +
                text.substring(endIndex);
        }
    }

    removeHighlights(cells) {
        [2, 3, 4].forEach(index => {
            if (cells[index]) {
                cells[index].innerHTML = cells[index].textContent;
            }
        });
    }

    removeNoResultsMessage() {
        const noResults = this.tbody.querySelector('.no-results');
        if (noResults) {
            noResults.remove();
        }
    }

    showNoResultsMessage(searchTerm) {
        const noResultsRow = document.createElement('tr');
        noResultsRow.className = 'no-results';
        noResultsRow.innerHTML = `
            <td colspan="7" class="text-center">
                <div class="no-results-message">
                    <i class="fas fa-search" style="margin-right: 10px;"></i>
                    No books found matching "${searchTerm}"
                </div>
            </td>`;
        this.tbody.appendChild(noResultsRow);
    }

    setupStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .highlight {
                background-color: #ffd700;
                padding: 2px;
                border-radius: 3px;
            }
            .no-results-message {
                padding: 20px;
                color: #666;
                font-size: 1.1em;
            }
            #book-search {
                width: 100%;
                padding: 10px;
                border: 2px solid #ddd;
                border-radius: 5px;
                font-size: 16px;
                transition: border-color 0.3s ease;
            }
            #book-search:focus {
                border-color: #4CAF50;
                outline: none;
            }
        `;
        document.head.appendChild(style);
    }
}

// Initialize search when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new BookSearch();
}); 