<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project ISSHED Timeline</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #800000;
            --secondary-color: #a52a2a;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 4rem 0;
            margin-bottom: 3rem;
            text-align: center;
        }
        
        .timeline-container {
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .timeline-container::after {
            content: '';
            position: absolute;
            width: 6px;
            background-color: var(--primary-color);
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -3px;
        }
        
        .timeline-event {
            position: relative;
            width: 50%;
            margin-bottom: 3rem;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        
        .timeline-event.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .timeline-event:nth-child(odd) {
            left: 0;
            padding-right: 40px;
        }
        
        .timeline-event:nth-child(even) {
            left: 50%;
            padding-left: 40px;
        }
        
        .timeline-content {
            position: relative;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .timeline-content:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }
        
        .timeline-date {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .timeline-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .timeline-title {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .timeline-description {
            color: #6c757d;
            margin-bottom: 1rem;
        }
        
        .status-badge {
            font-size: 0.8rem;
            padding: 0.35rem 0.65rem;
        }
        
        .timeline-event::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background-color: white;
            border: 4px solid var(--primary-color);
            border-radius: 50%;
            top: 20px;
            z-index: 1;
        }
        
        .timeline-event:nth-child(odd)::after {
            right: -10px;
        }
        
        .timeline-event:nth-child(even)::after {
            left: -10px;
        }
        
        .event-status {
            display: flex;
            align-items: center;
            margin-top: 1rem;
        }
        
        .filter-container {
            background-color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .no-events {
            text-align: center;
            padding: 3rem;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        @media screen and (max-width: 768px) {
            .timeline-container::after {
                left: 31px;
            }
            
            .timeline-event {
                width: 100%;
                padding-left: 70px;
                padding-right: 25px;
            }
            
            .timeline-event:nth-child(even) {
                left: 0;
            }
            
            .timeline-event::after {
                left: 21px;
            }
            
            .timeline-event:nth-child(odd)::after {
                right: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header class="page-header">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Project ISSHED Timeline</h1>
            <p class="lead">Follow our journey and milestones as we progress through this initiative</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mb-5">
        <!-- Filter Section -->
        <div class="filter-container mb-4">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h5 class="mb-0">Filter Events</h5>
                </div>
                <div class="col-md-6">
                    <select class="form-select" id="statusFilter">
                        <option value="all">All Events</option>
                        <option value="completed">Completed</option>
                        <option value="in-progress">In Progress</option>
                        <option value="planned">Planned</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Timeline Section -->
        <div class="timeline-container" id="timelineContainer">
            <!-- Timeline events will be loaded here via JavaScript -->
        </div>

        <!-- No Events Message (Hidden by default) -->
        <div class="no-events d-none" id="noEvents">
            <i class="bi bi-calendar-x display-1 text-muted mb-3"></i>
            <h3 class="text-muted">No Timeline Events Available</h3>
            <p class="text-muted">Check back later for updates on our project timeline.</p>
        </div>

        <!-- Loading Spinner -->
        <div class="text-center py-5" id="loadingSpinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading timeline events...</p>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Project ISSHED. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timelineContainer = document.getElementById('timelineContainer');
            const noEvents = document.getElementById('noEvents');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const statusFilter = document.getElementById('statusFilter');
            
            let allEvents = [];
            
            // Fetch timeline events from the server
            async function fetchTimelineEvents() {
                try {
                    const response = await fetch('get_timeline_events.php');
                    const events = await response.json();
                    
                    // Hide loading spinner
                    loadingSpinner.classList.add('d-none');
                    
                    if (events.length > 0) {
                        allEvents = events;
                        renderTimelineEvents(events);
                    } else {
                        noEvents.classList.remove('d-none');
                    }
                } catch (error) {
                    console.error('Error fetching timeline events:', error);
                    loadingSpinner.classList.add('d-none');
                    noEvents.classList.remove('d-none');
                    noEvents.querySelector('h3').textContent = 'Error Loading Timeline';
                    noEvents.querySelector('p').textContent = 'Unable to load timeline events. Please try again later.';
                }
            }
            
            // Render timeline events
            function renderTimelineEvents(events) {
                timelineContainer.innerHTML = '';
                
                if (events.length === 0) {
                    noEvents.classList.remove('d-none');
                    return;
                }
                
                noEvents.classList.add('d-none');
                
                // Sort events by date
                events.sort((a, b) => new Date(a.event_date) - new Date(b.event_date));
                
                events.forEach((event, index) => {
                    const eventElement = createEventElement(event, index);
                    timelineContainer.appendChild(eventElement);
                    
                    // Add animation with delay
                    setTimeout(() => {
                        eventElement.classList.add('visible');
                    }, 100 * index);
                });
            }
            
            // Create individual event element
            function createEventElement(event, index) {
                const eventDiv = document.createElement('div');
                eventDiv.className = 'timeline-event';
                
                // Format date
                const eventDate = new Date(event.event_date);
                const formattedDate = eventDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
                
                // Status badge class
                let statusClass = '';
                switch(event.status) {
                    case 'completed':
                        statusClass = 'bg-success';
                        break;
                    case 'in-progress':
                        statusClass = 'bg-warning text-dark';
                        break;
                    case 'planned':
                        statusClass = 'bg-primary';
                        break;
                    default:
                        statusClass = 'bg-secondary';
                }
                
                eventDiv.innerHTML = `
                    <div class="timeline-content">
                        <span class="timeline-date">${formattedDate}</span>
                        ${event.image_path ? `<img src="${event.image_path}" alt="${event.title}" class="timeline-image">` : ''}
                        <h3 class="timeline-title">${event.title}</h3>
                        <p class="timeline-description">${event.description}</p>
                        <div class="event-status">
                            <span class="badge ${statusClass} status-badge me-2">${event.status.replace('-', ' ')}</span>
                            ${event.is_active ? '<span class="badge bg-info status-badge">Active</span>' : '<span class="badge bg-secondary status-badge">Inactive</span>'}
                        </div>
                    </div>
                `;
                
                return eventDiv;
            }
            
            // Filter events based on status
            function filterEvents() {
                const status = statusFilter.value;
                
                if (status === 'all') {
                    renderTimelineEvents(allEvents);
                    return;
                }
                
                const filteredEvents = allEvents.filter(event => event.status === status);
                renderTimelineEvents(filteredEvents);
            }
            
            // Event listeners
            statusFilter.addEventListener('change', filterEvents);
            
            // Initialize
            fetchTimelineEvents();
            
            // Add scroll animation
            function checkVisibility() {
                const events = document.querySelectorAll('.timeline-event');
                
                events.forEach(event => {
                    const position = event.getBoundingClientRect();
                    
                    // If event is in viewport
                    if(position.top < window.innerHeight && position.bottom >= 0) {
                        event.classList.add('visible');
                    }
                });
            }
            
            window.addEventListener('scroll', checkVisibility);
            // Initial check
            checkVisibility();
        });
    </script>
</body>
</html>