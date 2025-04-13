document.addEventListener('DOMContentLoaded', function() {
    // Load e-books data
    loadEbooks();
});

// Load all e-books
function loadEbooks() {
    fetch('../api/ebooks.php')
        .then(response => response.json())
        .then(data => {
            const categories = document.querySelectorAll('.category-section');
            
            if (data.length > 0) {
                // Group e-books by category
                const ebooksByCategory = {};
                
                data.forEach(ebook => {
                    if (!ebooksByCategory[ebook.category_id]) {
                        ebooksByCategory[ebook.category_id] = [];
                    }
                    ebooksByCategory[ebook.category_id].push(ebook);
                });
                
                // Update each category section
                categories.forEach(category => {
                    const categoryId = category.dataset.categoryId;
                    if (categoryId && ebooksByCategory[categoryId]) {
                        const ebooksGrid = category.querySelector('.ebooks-grid');
                        ebooksGrid.innerHTML = '';
                        
                        ebooksByCategory[categoryId].forEach(ebook => {
                            const ebookCard = document.createElement('a');
                            ebookCard.href = ebook.file_url;
                            ebookCard.target = "_blank";
                            ebookCard.className = "ebook-card";
                            
                            ebookCard.innerHTML = `
                                <div class="ebook-icon">
                                    <i class="fas ${ebook.icon || 'fa-book'}"></i>
                                </div>
                                <h3 class="ebook-title">${ebook.title}</h3>
                                <p class="ebook-description">${ebook.description || ''}</p>
                                <span class="ebook-link">Access Book <i class="fas fa-external-link-alt"></i></span>
                            `;
                            
                            ebooksGrid.appendChild(ebookCard);
                        });
                    } else {
                        // No e-books for this category or category ID not found
                        const ebooksGrid = category.querySelector('.ebooks-grid');
                        if (ebooksGrid) {
                            ebooksGrid.innerHTML = '<p>No e-books available in this category</p>';
                        }
                    }
                });
            } else {
                // No e-books found
                categories.forEach(category => {
                    const ebooksGrid = category.querySelector('.ebooks-grid');
                    if (ebooksGrid) {
                        ebooksGrid.innerHTML = '<p>No e-books available</p>';
                    }
                });
            }
        })
        .catch(error => console.error('Error loading e-books:', error));
}