document.addEventListener('DOMContentLoaded', function() {
    const viewAllBtn = document.getElementById('viewAllStoriesBtn');
    const hiddenStories = document.getElementById('hidden-stories');
    
    if (viewAllBtn && hiddenStories) {
        viewAllBtn.addEventListener('click', function() {
            // Toggle the display of hidden stories
            hiddenStories.classList.toggle('d-none');
            
            // Change button text and icon based on state
            if (hiddenStories.classList.contains('d-none')) {
                this.innerHTML = 'View All Stories <i class="fas fa-chevron-down ms-2"></i>';
            } else {
                this.innerHTML = 'View Less Stories <i class="fas fa-chevron-up ms-2"></i>';
            }
        });
    }
});