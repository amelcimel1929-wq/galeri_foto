document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const searchDropdown = document.getElementById('searchDropdown');
    const searchForm = document.getElementById('searchForm');
    const recentGrid = document.getElementById('recentSearchesGrid');

    const userIdInput = document.getElementById('userId');
    const userId = (userIdInput && userIdInput.value) ? userIdInput.value : 'guest';
    const STORAGE_KEY = `recent_searches_${userId}`;

    function getRecentSearches() {
        return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
    }

    function renderRecentSearches() {
        if (!recentGrid) return;
        const searches = getRecentSearches();
        recentGrid.innerHTML = '';

        if (searches.length === 0) {
            recentGrid.innerHTML = '<p style="font-size: 12px; color: #888; margin: 0;">Belum ada riwayat</p>';
            return;
        }

        searches.forEach((query, index) => {
            const card = document.createElement('div');
            card.className = 'search-card';
            card.innerHTML = `
                <div class="card-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                <span style="cursor:pointer; flex: 1;" onclick="executeSearch('${query}')">${query}</span>
                <button type="button" class="btn-delete-search" onclick="deleteSearch(event, ${index})">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
            recentGrid.appendChild(card);
        });
    }

    if (searchInput) {
        searchInput.addEventListener('focus', () => {
            renderRecentSearches();
            if (searchDropdown) searchDropdown.classList.add('active');
        });
    }

    document.addEventListener('click', (e) => {
        if (searchDropdown && !e.target.closest('.search-box')) {
            searchDropdown.classList.remove('active');
        }
    });

    if (searchForm) {
        searchForm.addEventListener('submit', () => {
            if (searchInput) {
                const query = searchInput.value.trim();
                if (query !== '') {
                    let searches = getRecentSearches();
                    searches = searches.filter(item => item.toLowerCase() !== query.toLowerCase());
                    searches.unshift(query);
                    if (searches.length > 6) searches.pop();
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(searches));
                }
            }
        });
    }

    window.executeSearch = (query) => {
        if (searchInput && searchForm) {
            searchInput.value = query;
            searchForm.submit();
        }
    };

    window.deleteSearch = (event, index) => {
        event.stopPropagation();
        let searches = getRecentSearches();
        searches.splice(index, 1);
        localStorage.setItem(STORAGE_KEY, JSON.stringify(searches));
        renderRecentSearches();
    };
});