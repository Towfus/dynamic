document.getElementById('viewAllStoriesBtn')?.addEventListener('click', function() {
        const hiddenStories = document.getElementById('hidden-stories');
        const btn = this;
        
        if (hiddenStories.classList.contains('d-none')) {
            hiddenStories.classList.remove('d-none');
            btn.innerHTML = 'Show Less <i class="fas fa-chevron-up ms-2"></i>';
        } else {
            hiddenStories.classList.add('d-none');
            btn.innerHTML = 'View All Stories <i class="fas fa-chevron-down ms-2"></i>';
        }
    });