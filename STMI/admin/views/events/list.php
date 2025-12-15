<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Management - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .events-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .events-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .filter-controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 15px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .filter-btn.active {
            background: #0e0c5e;
            color: white;
            border-color: #0e0c5e;
        }
        
        .filter-btn:hover {
            background: #f8f9fa;
        }
        
        .search-box {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 400px;
        }
        
        .search-box input {
            flex: 1;
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .search-box button {
            background: #0e0c5e;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .events-table {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 1px solid #eee;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .event-image {
            width: 80px;
            height: 60px;
            border-radius: 5px;
            object-fit: cover;
        }
        
        .event-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .event-date {
            color: #666;
            font-size: 0.9rem;
        }
        
        .event-location {
            color: #666;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .category-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .category-upcoming {
            background: #d4edda;
            color: #155724;
        }
        
        .category-ongoing {
            background: #fff3cd;
            color: #856404;
        }
        
        .category-past {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-published {
            background: #d4edda;
            color: #155724;
        }
        
        .status-draft {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn-action {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-edit { background: #3498db; color: white; }
        .btn-delete { background: #e74c3c; color: white; }
        .btn-view { background: #57cc99; color: white; }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .upcoming-badge {
            background: #0e0c5e;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .today-badge {
            background: #ff9d0b;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Events Management</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="window.location.href='events.php?action=add'">
                    <i class="fas fa-plus"></i> Add New Event
                </button>
            </div>
        </div>
        
        <!-- Filter Controls -->
        <div class="events-header">
            <div class="filter-controls">
                <button class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>" 
                        onclick="window.location.href='events.php'">
                    All Events
                </button>
                <button class="filter-btn <?php echo $filter === 'upcoming' ? 'active' : ''; ?>" 
                        onclick="window.location.href='events.php?filter=upcoming'">
                    Upcoming
                </button>
                <button class="filter-btn <?php echo $filter === 'past' ? 'active' : ''; ?>" 
                        onclick="window.location.href='events.php?filter=past'">
                    Past Events
                </button>
                <button class="filter-btn <?php echo $filter === 'ongoing' ? 'active' : ''; ?>" 
                        onclick="window.location.href='events.php?filter=ongoing'">
                    Ongoing
                </button>
            </div>
            
            <div class="search-box">
                <form method="GET" action="" style="display: flex; gap: 10px; width: 100%;">
                    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                    <input type="text" name="search" placeholder="Search events..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Events Table -->
        <div class="events-container">
            <?php if (empty($events)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>No Events Found</h3>
                    <p><?php echo !empty($search) ? 'Try a different search term.' : 'Create your first event to get started.'; ?></p>
                    <?php if (empty($search)): ?>
                        <button class="btn btn-primary" onclick="window.location.href='events.php?action=add'">
                            <i class="fas fa-plus"></i> Add First Event
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="events-table">
                    <table>
                        <thead>
                            <tr>
                                <th width="100">Image</th>
                                <th>Event Details</th>
                                <th>Date & Time</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): 
                                $isToday = date('Y-m-d') == $event['event_date'];
                                $isUpcoming = $event['event_date'] > date('Y-m-d');
                            ?>
                                <tr>
                                    <td>
                                        <?php if ($event['featured_image']): ?>
                                            <img src="../<?php echo htmlspecialchars($event['featured_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                                 class="event-image">
                                        <?php else: ?>
                                            <div class="event-image" style="background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#666;">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="event-title">
                                            <?php echo htmlspecialchars($event['title']); ?>
                                            <?php if ($isToday): ?>
                                                <span class="today-badge">Today</span>
                                            <?php elseif ($isUpcoming && $event['category'] === 'upcoming'): ?>
                                                <span class="upcoming-badge">Upcoming</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="event-location">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php echo htmlspecialchars($event['location']); ?>
                                        </div>
                                        <?php if (!empty($event['description'])): ?>
                                            <p style="font-size: 0.9rem; color: #666; margin-top: 5px;">
                                                <?php echo substr(htmlspecialchars($event['description']), 0, 80); ?>...
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="event-date">
                                            <strong><?php echo date('F d, Y', strtotime($event['event_date'])); ?></strong>
                                            <?php if ($event['start_time']): ?>
                                                <br>
                                                <small><?php echo date('g:i A', strtotime($event['start_time'])); ?> 
                                                <?php if ($event['end_time']): ?>
                                                    - <?php echo date('g:i A', strtotime($event['end_time'])); ?>
                                                <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-badge category-<?php echo $event['category']; ?>">
                                            <?php echo ucfirst($event['category']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $event['status']; ?>">
                                            <?php echo ucfirst($event['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-view" 
                                                    onclick="window.open('../event-details.php?id=<?php echo $event['id']; ?>', '_blank')">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="btn-action btn-edit" 
                                                    onclick="window.location.href='events.php?action=edit&id=<?php echo $event['id']; ?>'">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn-action btn-delete" 
                                                    onclick="deleteEvent(<?php echo $event['id']; ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page-1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" 
                               class="page-btn">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" 
                               class="page-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page+1; ?>&filter=<?php echo $filter; ?>&search=<?php echo urlencode($search); ?>" 
                               class="page-btn">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        function deleteEvent(id) {
            if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
                window.location.href = 'events.php?action=delete&id=' + id;
            }
        }
        
        // Toggle event status
        function toggleEventStatus(id, currentStatus) {
            const newStatus = currentStatus === 'published' ? 'draft' : 'published';
            if (confirm('Change event status to ' + newStatus + '?')) {
                fetch('handlers/update_event_status.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        event_id: id,
                        status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
        
        // Export events
        function exportEvents() {
            const filter = '<?php echo $filter; ?>';
            const search = '<?php echo urlencode($search); ?>';
            window.open('handlers/export_events.php?filter=' + filter + '&search=' + search, '_blank');
        }
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>