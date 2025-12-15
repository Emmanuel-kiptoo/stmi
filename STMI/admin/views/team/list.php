<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Management - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .team-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .team-member-card {
            border: 1px solid #eee;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .team-member-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .member-header {
            display: flex;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .member-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
            border: 3px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .member-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .member-info {
            flex: 1;
        }
        
        .member-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: #333;
        }
        
        .member-position {
            color: #0e0c5e;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .member-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .member-body {
            padding: 15px 20px;
        }
        
        .member-bio {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 15px;
            max-height: 100px;
            overflow: hidden;
        }
        
        .member-contact {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 0.9rem;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #555;
        }
        
        .contact-item i {
            width: 20px;
            color: #0e0c5e;
        }
        
        .social-links {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .social-link {
            color: #666;
            font-size: 1rem;
            transition: color 0.3s;
        }
        
        .social-link:hover {
            color: #0e0c5e;
        }
        
        .member-footer {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .member-order {
            color: #666;
            font-size: 0.8rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
            grid-column: 1 / -1;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .drag-handle {
            cursor: move;
            color: #999;
            padding: 5px;
        }
        
        .dragging {
            opacity: 0.5;
            background: #f0f0f0;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Team Members Management</h1>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="window.location.href='team.php?action=add'">
                    <i class="fas fa-user-plus"></i> Add Team Member
                </button>
            </div>
        </div>
        
        <div class="team-container">
            <?php if (empty($members)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No Team Members Yet</h3>
                    <p>Add your first team member to get started.</p>
                    <button class="btn btn-primary" onclick="window.location.href='team.php?action=add'">
                        <i class="fas fa-user-plus"></i> Add First Member
                    </button>
                </div>
            <?php else: ?>
                <div class="team-grid" id="teamGrid">
                    <?php foreach ($members as $member): ?>
                        <div class="team-member-card" data-id="<?php echo $member['id']; ?>">
                            <div class="member-header">
                                <div class="member-photo">
                                    <?php if ($member['photo']): ?>
                                        <img src="../<?php echo htmlspecialchars($member['photo']); ?>" 
                                             alt="<?php echo htmlspecialchars($member['name']); ?>">
                                    <?php else: ?>
                                        <div style="width:100%;height:100%;background:#ddd;display:flex;align-items:center;justify-content:center;color:#666;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="member-info">
                                    <h3 class="member-name"><?php echo htmlspecialchars($member['name']); ?></h3>
                                    <p class="member-position"><?php echo htmlspecialchars($member['position']); ?></p>
                                    <span class="member-status status-<?php echo $member['status']; ?>">
                                        <?php echo ucfirst($member['status']); ?>
                                    </span>
                                </div>
                                
                                <div class="drag-handle">
                                    <i class="fas fa-grip-vertical"></i>
                                </div>
                            </div>
                            
                            <div class="member-body">
                                <?php if ($member['bio']): ?>
                                    <p class="member-bio"><?php echo nl2br(htmlspecialchars(substr($member['bio'], 0, 150))); ?>...</p>
                                <?php endif; ?>
                                
                                <div class="member-contact">
                                    <?php if ($member['email']): ?>
                                        <div class="contact-item">
                                            <i class="fas fa-envelope"></i>
                                            <span><?php echo htmlspecialchars($member['email']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($member['phone']): ?>
                                        <div class="contact-item">
                                            <i class="fas fa-phone"></i>
                                            <span><?php echo htmlspecialchars($member['phone']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    $social = json_decode($member['social_links'] ?? '{}', true);
                                    if (!empty($social)): ?>
                                        <div class="social-links">
                                            <?php if (!empty($social['linkedin'])): ?>
                                                <a href="<?php echo htmlspecialchars($social['linkedin']); ?>" 
                                                   target="_blank" class="social-link">
                                                    <i class="fab fa-linkedin"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($social['twitter'])): ?>
                                                <a href="<?php echo htmlspecialchars($social['twitter']); ?>" 
                                                   target="_blank" class="social-link">
                                                    <i class="fab fa-twitter"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($social['facebook'])): ?>
                                                <a href="<?php echo htmlspecialchars($social['facebook']); ?>" 
                                                   target="_blank" class="social-link">
                                                    <i class="fab fa-facebook"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="member-footer">
                                <div class="member-order">
                                    Order: <?php echo $member['display_order']; ?>
                                </div>
                                
                                <div class="action-buttons">
                                    <button class="btn-small btn-edit" 
                                            onclick="window.location.href='team.php?action=edit&id=<?php echo $member['id']; ?>'">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn-small btn-delete" 
                                            onclick="deleteMember(<?php echo $member['id']; ?>)">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <button class="btn btn-secondary" onclick="saveOrder()">
                        <i class="fas fa-save"></i> Save Display Order
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        // Drag and drop reordering
        const teamGrid = document.getElementById('teamGrid');
        let draggedItem = null;
        
        if (teamGrid) {
            // Make items draggable
            teamGrid.querySelectorAll('.team-member-card').forEach(item => {
                item.setAttribute('draggable', 'true');
                
                item.addEventListener('dragstart', function(e) {
                    draggedItem = this;
                    this.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', this.dataset.id);
                });
                
                item.addEventListener('dragend', function() {
                    this.classList.remove('dragging');
                    draggedItem = null;
                });
            });
            
            // Handle drag over
            teamGrid.addEventListener('dragover', function(e) {
                e.preventDefault();
                const afterElement = getDragAfterElement(teamGrid, e.clientY);
                if (draggedItem) {
                    if (afterElement) {
                        teamGrid.insertBefore(draggedItem, afterElement);
                    } else {
                        teamGrid.appendChild(draggedItem);
                    }
                }
            });
            
            function getDragAfterElement(container, y) {
                const draggableElements = [...container.querySelectorAll('.team-member-card:not(.dragging)')];
                
                return draggableElements.reduce((closest, child) => {
                    const box = child.getBoundingClientRect();
                    const offset = y - box.top - box.height / 2;
                    
                    if (offset < 0 && offset > closest.offset) {
                        return { offset: offset, element: child };
                    } else {
                        return closest;
                    }
                }, { offset: Number.NEGATIVE_INFINITY }).element;
            }
        }
        
        function saveOrder() {
            const items = teamGrid.querySelectorAll('.team-member-card');
            const order = [];
            
            items.forEach((item, index) => {
                order.push({
                    id: item.dataset.id,
                    order: index + 1
                });
            });
            
            fetch('handlers/save_team_order.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ order: order })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order saved successfully!');
                    location.reload();
                } else {
                    alert('Error saving order');
                }
            });
        }
        
        function deleteMember(id) {
            if (confirm('Are you sure you want to delete this team member?')) {
                window.location.href = 'team.php?action=delete&id=' + id;
            }
        }
    </script>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>