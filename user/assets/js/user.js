
const storyData = {};
const newsData = {};

// Toggle functionality for stories
document.addEventListener('DOMContentLoaded', function() {
    const viewAllBtn = document.getElementById('viewAllStoriesBtn');
    const additionalStories = document.getElementById('additionalStories');
    
    if (viewAllBtn && additionalStories) {
        let isExpanded = false;
        
        viewAllBtn.addEventListener('click', function() {
            if (!isExpanded) {
                // Show additional stories
                additionalStories.classList.remove('d-none');
                viewAllBtn.innerHTML = 'View Less <i class="fas fa-chevron-up ms-2"></i>';
                isExpanded = true;
                
                // Smooth scroll to reveal new content
                setTimeout(() => {
                    additionalStories.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start' 
                    });
                }, 100);
            } else {
                // Hide additional stories
                additionalStories.classList.add('d-none');
                viewAllBtn.innerHTML = 'View All Stories <i class="fas fa-chevron-down ms-2"></i>';
                isExpanded = false;
                
                // Scroll back to the initial stories
                setTimeout(() => {
                    document.getElementById('initialStories').scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start' 
                    });
                }, 100);
            }
        });
    }
});

// Function to open story modal with full details
function openStoryModal(storyId) {
    const story = storyData[storyId];
    if (!story) return;

    const modalTitle = document.getElementById('storyModalLabel');
    const storyContent = document.getElementById('storyContent');
    
    modalTitle.textContent = story.title;
    
    const categoryClass = getCategoryClass(story.category);
    const formattedDate = new Date(story.date_taken).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });

    storyContent.innerHTML = `
        <div class="text-center mb-4">
            <img src="${story.file_path}" 
                 alt="${story.title}" 
                 class="img-fluid rounded"
                 style="max-height: 400px; object-fit: cover;"
                 onerror="this.src='https://placehold.co/600x400?text=${encodeURIComponent(story.category)}';">
        </div>
        <div class="mb-3">
            <span class="badge ${categoryClass} me-2">${story.category}</span>
            <small class="text-muted"><i class="fas fa-calendar me-1"></i>${formattedDate}</small>
        </div>
        <div class="story-description">
            <p class="lead">${story.description}</p>
        </div>
        ${story.story_link ? `
            <div class="alert alert-info">
                <i class="fas fa-external-link-alt me-2"></i>
                <strong>Full Story Available:</strong> 
                <a href="${story.story_link}" target="_blank" class="alert-link">View complete story</a>
            </div>
        ` : ''}
        <hr>
        <small class="text-muted">
            <i class="fas fa-clock me-1"></i>Published on ${new Date(story.upload_date).toLocaleDateString()}
        </small>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('storyModal'));
    modal.show();
}

function getCategoryClass(category) {
    const categoryClasses = {
        'Events': 'bg-success',
        'Activities': 'bg-primary',
        'Awards': 'bg-warning',
        'Students': 'bg-info',
        'Teachers': 'bg-secondary',
        'Facilities': 'bg-dark'
    };
    return categoryClasses[category] || 'bg-light';
}

// Function to open news modal with full details
function openNewsModal(newsId) {
    const news = newsData[newsId];
    if (!news) return;

    const modalTitle = document.getElementById('newsModalLabel');
    const newsContent = document.getElementById('newsContent');
    
    modalTitle.textContent = news.title;
    
    const categoryClass = getNewsCategoryClass(news.category);
    const formattedDate = new Date(news.publish_date).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });

    newsContent.innerHTML = `
        <div class="text-center mb-4">
            <img src="${news.image_path}" 
                 alt="${news.title}" 
                 class="img-fluid rounded"
                 style="max-height: 400px; object-fit: cover;"
                 onerror="this.src='https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80';">
        </div>
        <div class="mb-3">
            <span class="badge ${categoryClass} me-2">${news.category}</span>
            <small class="text-muted"><i class="fas fa-calendar me-1"></i>${formattedDate}</small>
            ${news.author ? `<small class="text-muted"> • by ${news.author}</small>` : ''}
        </div>
        <div class="news-description">
            <p class="lead">${news.excerpt}</p>
        </div>
        ${news.news_link ? `
            <div class="alert alert-info">
                <i class="fas fa-external-link-alt me-2"></i>
                <strong>Full Article Available:</strong> 
                <a href="${news.news_link}" target="_blank" class="alert-link">Read complete article</a>
            </div>
        ` : ''}
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('newsModal'));
    modal.show();
}

function getNewsCategoryClass(category) {
    const categoryClasses = {
        'Partnership': 'bg-success',
        'Brigada Eskwela': 'bg-info',
        'Achievement': 'bg-warning text-dark',
        'Event': 'bg-primary',
        'Announcement': 'bg-secondary',
        'Other': 'bg-dark'
    };
    return categoryClasses[category] || 'bg-secondary';
}
