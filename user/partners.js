document.getElementById('view-all-btn')?.addEventListener('click', function() {
    const morePartners = document.getElementById('morePartners');
    if (morePartners.classList.contains('d-none')) {
        morePartners.classList.remove('d-none');
        this.innerHTML = 'View Less <i class="fas fa-chevron-up ms-2"></i>';
    } else {
        morePartners.classList.add('d-none');
        this.innerHTML = 'View All <i class="fas fa-chevron-down ms-2"></i>';
    }
});