// Student search functionality
class StudentSearch {
    constructor() {
        this.searchInput = document.getElementById('student-search');
        this.tbody = document.querySelector('#students table tbody');
        this.setupEventListeners();
        this.setupStyles();
    }

    setupEventListeners() {
        if (this.searchInput) {
            let searchTimeout;
            this.searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => this.filterStudents(), 300);
            });
        }
    }

    filterStudents() {
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

            const studentId = cells[0].textContent.toLowerCase();
            const name = cells[1].textContent.toLowerCase();

            const showRow = this.shouldShowRow(searchTerm, {
                studentId, name
            });

            if (showRow) {
                row.style.display = '';
                found = true;
                this.highlightText(cells[1], searchTerm); // Name
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
            return Object.values(fields).some(field => field.includes(searchTerm));
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
        if (cells[1]) {
            cells[1].innerHTML = cells[1].textContent;
        }
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
            <td colspan="4" class="text-center">
                <div class="no-results-message">
                    <i class="fas fa-search" style="margin-right: 10px;"></i>
                    No students found matching "${searchTerm}"
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
            #student-search {
                width: 100%;
                padding: 10px;
                border: 2px solid #ddd;
                border-radius: 5px;
                font-size: 16px;
                transition: border-color 0.3s ease;
            }
            #student-search:focus {
                border-color: #4CAF50;
                outline: none;
            }
        `;
        document.head.appendChild(style);
    }
}

// Initialize search when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new StudentSearch();
}); 