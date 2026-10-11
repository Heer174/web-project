/**
 * Campus Lost and Found System - JavaScript interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    // ----------------------------------------------------
    // Live Search and Filtering on index.php
    // ----------------------------------------------------
    const searchInput = document.getElementById('searchQuery');
    const categoryFilter = document.getElementById('categoryFilter');
    const pillButtons = document.querySelectorAll('.pill-btn');
    const itemCards = document.querySelectorAll('.item-card');
    const emptyState = document.getElementById('noResultsMessage');

    let currentTypeFilter = 'all';

    function filterItems() {
        if (!itemCards.length) return;

        const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
        const selectedCategory = categoryFilter ? categoryFilter.value.toLowerCase() : 'all';

        let visibleCount = 0;

        itemCards.forEach(card => {
            const cardType = (card.getAttribute('data-type') || '').toLowerCase();
            const cardCategory = (card.getAttribute('data-category') || '').toLowerCase();
            const cardTitle = (card.getAttribute('data-title') || '').toLowerCase();
            const cardLocation = (card.getAttribute('data-location') || '').toLowerCase();
            const cardDesc = (card.getAttribute('data-desc') || '').toLowerCase();

            const matchesType = (currentTypeFilter === 'all' || cardType === currentTypeFilter);
            const matchesCategory = (selectedCategory === 'all' || cardCategory === selectedCategory);
            const matchesQuery = !query || 
                cardTitle.includes(query) || 
                cardLocation.includes(query) || 
                cardDesc.includes(query);

            if (matchesType && matchesCategory && matchesQuery) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (emptyState) {
            emptyState.style.display = (visibleCount === 0) ? 'block' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterItems);
    }

    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterItems);
    }

    if (pillButtons.length) {
        pillButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                pillButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentTypeFilter = btn.getAttribute('data-type') || 'all';
                filterItems();
            });
        });
    }

    // ----------------------------------------------------
    // Image Preview for post-item.php
    // ----------------------------------------------------
    const imageInput = document.getElementById('itemImageInput');
    const imagePreview = document.getElementById('imagePreview');

    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File is too large! Maximum image size allowed is 5MB.');
                    imageInput.value = '';
                    imagePreview.style.display = 'none';
                    return;
                }
                const reader = new FileReader();
                reader.onload = (event) => {
                    imagePreview.src = event.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
            }
        });
    }

    // ----------------------------------------------------
    // Modal controls (Claim Item Modal)
    // ----------------------------------------------------
    const openClaimBtn = document.getElementById('openClaimModalBtn');
    const closeClaimBtn = document.getElementById('closeClaimModalBtn');
    const claimModal = document.getElementById('claimModal');

    if (openClaimBtn && claimModal) {
        openClaimBtn.addEventListener('click', () => {
            claimModal.style.display = 'flex';
        });
    }

    if (closeClaimBtn && claimModal) {
        closeClaimBtn.addEventListener('click', () => {
            claimModal.style.display = 'none';
        });
    }

    // Close modal on background click
    window.addEventListener('click', (e) => {
        if (e.target === claimModal) {
            claimModal.style.display = 'none';
        }
    });

    // ----------------------------------------------------
    // Auto-dismiss Flash Alerts
    // ----------------------------------------------------
    const flashAlerts = document.querySelectorAll('.alert');
    if (flashAlerts.length) {
        setTimeout(() => {
            flashAlerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 4000);
    }
});
