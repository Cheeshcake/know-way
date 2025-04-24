<?php
// messages.php - User Messages Page

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// For demonstration purposes, set admin status
// In a real application, this would come from database
$_SESSION['role'] = true;

// User data retrieval (replace with your database code)
$user = [
    'id' => 1,
    'name' => 'Nourane abdella',
    'email' => 'Nourane.abdella@example.com',
    'avatar' => null,
    'contacts' => [
        [
            'id' => 2,
            'name' => 'Sarah Johnson',
            'avatar' => null,
            'role' => 'instructor',
            'status' => 'online',
            'last_active' => 'now'
        ],
        [
            'id' => 3,
            'name' => 'Michael Chen',
            'avatar' => null,
            'role' => 'instructor',
            'status' => 'offline',
            'last_active' => '2 hours ago'
        ],
        [
            'id' => 4,
            'name' => 'Emma Rodriguez',
            'avatar' => null,
            'role' => 'student',
            'status' => 'online',
            'last_active' => 'now'
        ],
        [
            'id' => 5,
            'name' => 'Ahmed Hassan',
            'avatar' => null,
            'role' => 'student',
            'status' => 'offline',
            'last_active' => '1 day ago'
        ],
        [
            'id' => 6,
            'name' => 'Lisa Wang',
            'avatar' => null,
            'role' => 'student',
            'status' => 'online',
            'last_active' => 'now'
        ]
    ],
    'conversations' => [
        [
            'id' => 1,
            'type' => 'private',
            'with_user' => 2,
            'name' => 'Sarah Johnson',
            'avatar' => null,
            'unread' => 2,
            'last_message' => 'Could you please explain the CSS Grid concept again?',
            'last_time' => '10:45 AM',
            'messages' => [
                [
                    'id' => 101,
                    'sender_id' => 1,
                    'sender_name' => 'Nourane abdella',
                    'content' => 'Hello Sarah, I have a question about the CSS Grid layout.',
                    'time' => '10:30 AM',
                    'date' => 'Today',
                    'status' => 'read'
                ],
                [
                    'id' => 102,
                    'sender_id' => 2,
                    'sender_name' => 'Sarah Johnson',
                    'content' => 'Hi Nourane, sure! What would you like to know?',
                    'time' => '10:32 AM',
                    'date' => 'Today',
                    'status' => 'read'
                ],
                [
                    'id' => 103,
                    'sender_id' => 1,
                    'sender_name' => 'Nourane abdella',
                    'content' => 'I\'m having trouble understanding how to create a responsive grid layout.',
                    'time' => '10:35 AM',
                    'date' => 'Today',
                    'status' => 'read'
                ],
                [
                    'id' => 104,
                    'sender_id' => 2,
                    'sender_name' => 'Sarah Johnson',
                    'content' => 'For responsive grid layouts, you should use the grid-template-columns property with fr units or the repeat() function with auto-fit or auto-fill. Would you like me to share some examples?',
                    'time' => '10:40 AM',
                    'date' => 'Today',
                    'status' => 'read'
                ],
                [
                    'id' => 105,
                    'sender_id' => 1,
                    'sender_name' => 'Nourane abdella',
                    'content' => 'Yes, that would be very helpful. I\'m still confused about how to make it adapt to different screen sizes.',
                    'time' => '10:42 AM',
                    'date' => 'Today',
                    'status' => 'read'
                ],
                [
                    'id' => 106,
                    'sender_id' => 2,
                    'sender_name' => 'Sarah Johnson',
                    'content' => 'Could you please explain the CSS Grid concept again?',
                    'time' => '10:45 AM',
                    'date' => 'Today',
                    'status' => 'unread'
                ]
            ]
        ],
        [
            'id' => 2,
            'type' => 'private',
            'with_user' => 4,
            'name' => 'Emma Rodriguez',
            'avatar' => null,
            'unread' => 0,
            'last_message' => 'Thanks for sharing your notes from the photography class!',
            'last_time' => 'Yesterday',
            'messages' => []
        ],
        [
            'id' => 3,
            'type' => 'group',
            'name' => 'Web Development Study Group',
            'avatar' => null,
            'members' => [1, 3, 4, 5],
            'unread' => 5,
            'last_message' => 'Has anyone completed the JavaScript assignment yet?',
            'last_time' => '2 days ago',
            'messages' => []
        ],
        [
            'id' => 4,
            'type' => 'forum',
            'category' => 'General Discussion',
            'name' => 'Course Recommendations',
            'unread' => 0,
            'last_message' => 'I highly recommend the Advanced CSS course by Sarah Johnson.',
            'last_time' => '3 days ago',
            'messages' => []
        ]
    ],
    'forums' => [
        [
            'id' => 1,
            'name' => 'General Discussion',
            'description' => 'Open discussions about learning, education, and career development.',
            'topics' => [
                [
                    'id' => 101,
                    'title' => 'Course Recommendations',
                    'author' => 'Ahmed Hassan',
                    'replies' => 12,
                    'views' => 45,
                    'last_post' => '3 days ago'
                ],
                [
                    'id' => 102,
                    'title' => 'Study Tips and Techniques',
                    'author' => 'Emma Rodriguez',
                    'replies' => 8,
                    'views' => 32,
                    'last_post' => '5 days ago'
                ]
            ]
        ],
        [
            'id' => 2,
            'name' => 'Course Reviews',
            'description' => 'Share your experiences and reviews about courses you\'ve taken.',
            'topics' => [
                [
                    'id' => 201,
                    'title' => 'Review: Introduction to Web Development',
                    'author' => 'Lisa Wang',
                    'replies' => 5,
                    'views' => 28,
                    'last_post' => '1 week ago'
                ],
                [
                    'id' => 202,
                    'title' => 'Review: Advanced Digital Marketing',
                    'author' => 'Nourane abdella',
                    'replies' => 3,
                    'views' => 19,
                    'last_post' => '2 weeks ago'
                ]
            ]
        ],
        [
            'id' => 3,
            'name' => 'Technical Help',
            'description' => 'Get help with technical issues related to courses or the platform.',
            'topics' => [
                [
                    'id' => 301,
                    'title' => 'Having trouble with CSS Grid layout',
                    'author' => 'Nourane abdella',
                    'replies' => 7,
                    'views' => 22,
                    'last_post' => '4 days ago'
                ]
            ]
        ]
    ],
    'reviews' => [
        [
            'id' => 1,
            'course_id' => 1,
            'course_title' => 'Introduction to Web Development',
            'author' => 'Lisa Wang',
            'rating' => 5,
            'content' => 'This course was excellent! The instructor explained everything clearly and the exercises were very helpful.',
            'date' => '1 week ago',
            'likes' => 12,
            'comments' => [
                [
                    'author' => 'Emma Rodriguez',
                    'content' => 'I agree! I learned so much from this course.',
                    'date' => '6 days ago'
                ],
                [
                    'author' => 'Ahmed Hassan',
                    'content' => 'Did you find the final project difficult?',
                    'date' => '5 days ago'
                ]
            ]
        ],
        [
            'id' => 2,
            'course_id' => 2,
            'course_title' => 'Advanced Digital Marketing',
            'author' => 'Nourane abdella',
            'rating' => 4,
            'content' => 'Great course with lots of practical examples. The social media section was particularly useful for my work.',
            'date' => '2 weeks ago',
            'likes' => 8,
            'comments' => [
                [
                    'author' => 'Michael Chen',
                    'content' => 'Thanks for the review! I\'m glad you found the social media section helpful.',
                    'date' => '13 days ago'
                ]
            ]
        ],
        [
            'id' => 3,
            'course_id' => 3,
            'course_title' => 'Photography for Beginners',
            'author' => 'Ahmed Hassan',
            'rating' => 5,
            'content' => 'As someone with no prior experience in photography, this course was perfect. The instructor breaks down complex concepts into easy-to-understand lessons.',
            'date' => '3 weeks ago',
            'likes' => 15,
            'comments' => [
                [
                    'author' => 'Emma Rodriguez',
                    'content' => 'I\'m thinking of taking this course. Did it require any special equipment?',
                    'date' => '2 weeks ago'
                ],
                [
                    'author' => 'Ahmed Hassan',
                    'content' => 'You can start with just a smartphone camera, but having a DSLR would be better for the later lessons.',
                    'date' => '2 weeks ago'
                ]
            ]
        ]
    ]
];

// Get active conversation from URL parameter
$activeConversationId = isset($_GET['conversation']) ? (int)$_GET['conversation'] : null;
$activeForumId = isset($_GET['forum']) ? (int)$_GET['forum'] : null;
$activeTopicId = isset($_GET['topic']) ? (int)$_GET['topic'] : null;
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'messages';

// Get initials for avatar placeholder
$initials = '';
$name_parts = explode(' ', $user['name']);
foreach ($name_parts as $part) {
    $initials .= substr($part, 0, 1);
}

// Find active conversation
$activeConversation = null;
if ($activeConversationId) {
    foreach ($user['conversations'] as $conversation) {
        if ($conversation['id'] === $activeConversationId) {
            $activeConversation = $conversation;
            break;
        }
    }
}

// Find active forum and topic
$activeForum = null;
$activeTopic = null;
if ($activeForumId) {
    foreach ($user['forums'] as $forum) {
        if ($forum['id'] === $activeForumId) {
            $activeForum = $forum;
            if ($activeTopicId) {
                foreach ($forum['topics'] as $topic) {
                    if ($topic['id'] === $activeTopicId) {
                        $activeTopic = $topic;
                        break;
                    }
                }
            }
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay - Messages</title>
    <link rel="stylesheet" href="settings.css">
    <link rel="stylesheet" href="messages.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container" id="adminContainer">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h1 class="logo">KnowWay</h1>
                <p class="admin-label">Student Dashboard</p>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-th-large"></i>Dashboard</a></li>
                    <li><a href="my-courses.php"><i class="fas fa-book"></i>My Courses</a></li>
                    <li class="active"><a href="messages.php"><i class="fas fa-envelope"></i>Messages</a></li>
                    <li><a href="quiz.php"><i class="fas fa-question-circle"></i>Quiz</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i>Settings</a></li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <li><a href="admin.php"><i class="fas fa-user-shield"></i>Admin Panel</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <div class="header-left">
                    <button class="menu-toggle" id="menuToggle">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                    <h2>Messages</h2>
                </div>
                
                <div class="header-right">
                    <div class="user-profile">
                        <div class="user-avatar">
                            <?php if ($user['avatar']): ?>
                                <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar">
                            <?php else: ?>
                                <?php echo htmlspecialchars($initials); ?>
                            <?php endif; ?>
                        </div>
                        <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <!-- Messages Tabs -->
                <div class="messages-tabs">
                    <a href="?tab=messages" class="tab-item <?php echo $activeTab === 'messages' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope"></i> Messages
                    </a>
                    <a href="?tab=forums" class="tab-item <?php echo $activeTab === 'forums' ? 'active' : ''; ?>">
                        <i class="fas fa-comments"></i> Forums
                    </a>
                    <a href="?tab=reviews" class="tab-item <?php echo $activeTab === 'reviews' ? 'active' : ''; ?>">
                        <i class="fas fa-star"></i> Reviews
                    </a>
                </div>
                
                <?php if ($activeTab === 'messages'): ?>
                <!-- Messages Section -->
                <div class="messages-container">
                    <div class="conversations-sidebar">
                        <div class="conversations-header">
                            <h3>Conversations</h3>
                            <button class="new-message-btn"><i class="fas fa-plus"></i></button>
                        </div>
                        
                        <div class="search-container">
                            <input type="text" placeholder="Search messages..." class="search-input">
                            <button class="search-btn"><i class="fas fa-search"></i></button>
                        </div>
                        
                        <div class="conversations-list">
                            <?php foreach ($user['conversations'] as $conversation): ?>
                                <a href="?tab=messages&conversation=<?php echo $conversation['id']; ?>" class="conversation-item <?php echo ($activeConversationId === $conversation['id']) ? 'active' : ''; ?>">
                                    <div class="conversation-avatar">
                                        <?php if (isset($conversation['avatar']) && $conversation['avatar']): ?>
                                            <img src="<?php echo htmlspecialchars($conversation['avatar']); ?>" alt="Avatar">
                                        <?php else: ?>
                                            <?php 
                                            $conv_initials = '';
                                            if ($conversation['type'] === 'private') {
                                                $name_parts = explode(' ', $conversation['name']);
                                                foreach ($name_parts as $part) {
                                                    $conv_initials .= substr($part, 0, 1);
                                                }
                                            } else {
                                                $conv_initials = substr($conversation['name'], 0, 1);
                                            }
                                            echo htmlspecialchars($conv_initials);
                                            ?>
                                        <?php endif; ?>
                                        
                                        <?php if ($conversation['type'] === 'private'): ?>
                                            <?php 
                                            $contact = null;
                                            foreach ($user['contacts'] as $c) {
                                                if ($c['id'] === $conversation['with_user']) {
                                                    $contact = $c;
                                                    break;
                                                }
                                            }
                                            if ($contact && $contact['status'] === 'online'): 
                                            ?>
                                                <span class="status-indicator online"></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="conversation-content">
                                        <div class="conversation-header">
                                            <h4 class="conversation-name"><?php echo htmlspecialchars($conversation['name']); ?></h4>
                                            <span class="conversation-time"><?php echo htmlspecialchars($conversation['last_time']); ?></span>
                                        </div>
                                        <p class="conversation-preview"><?php echo htmlspecialchars($conversation['last_message']); ?></p>
                                    </div>
                                    
                                    <?php if ($conversation['unread'] > 0): ?>
                                        <div class="unread-badge"><?php echo $conversation['unread']; ?></div>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="message-content">
                        <?php if ($activeConversation): ?>
                            <div class="message-header">
                                <div class="message-header-user">
                                    <div class="conversation-avatar large">
                                        <?php if (isset($activeConversation['avatar']) && $activeConversation['avatar']): ?>
                                            <img src="<?php echo htmlspecialchars($activeConversation['avatar']); ?>" alt="Avatar">
                                        <?php else: ?>
                                            <?php 
                                            $conv_initials = '';
                                            if ($activeConversation['type'] === 'private') {
                                                $name_parts = explode(' ', $activeConversation['name']);
                                                foreach ($name_parts as $part) {
                                                    $conv_initials .= substr($part, 0, 1);
                                                }
                                            } else {
                                                $conv_initials = substr($activeConversation['name'], 0, 1);
                                            }
                                            echo htmlspecialchars($conv_initials);
                                            ?>
                                        <?php endif; ?>
                                        
                                        <?php if ($activeConversation['type'] === 'private'): ?>
                                            <?php 
                                            $contact = null;
                                            foreach ($user['contacts'] as $c) {
                                                if ($c['id'] === $activeConversation['with_user']) {
                                                    $contact = $c;
                                                    break;
                                                }
                                            }
                                            if ($contact && $contact['status'] === 'online'): 
                                            ?>
                                                <span class="status-indicator online"></span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="message-header-info">
                                        <h3 class="message-header-name"><?php echo htmlspecialchars($activeConversation['name']); ?></h3>
                                        <?php if ($activeConversation['type'] === 'private' && $contact): ?>
                                            <p class="message-header-status">
                                                <?php if ($contact['status'] === 'online'): ?>
                                                    <span class="online-text">Online</span>
                                                <?php else: ?>
                                                    <span class="last-seen">Last seen <?php echo htmlspecialchars($contact['last_active']); ?></span>
                                                <?php endif; ?>
                                            </p>
                                        <?php elseif ($activeConversation['type'] === 'group'): ?>
                                            <p class="message-header-status">
                                                <?php echo count($activeConversation['members']); ?> members
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="message-header-actions">
                                    <button class="action-btn"><i class="fas fa-phone"></i></button>
                                    <button class="action-btn"><i class="fas fa-video"></i></button>
                                    <button class="action-btn"><i class="fas fa-info-circle"></i></button>
                                </div>
                            </div>
                            
                            <div class="messages-list">
                                <?php if (isset($activeConversation['messages']) && count($activeConversation['messages']) > 0): ?>
                                    <?php 
                                    $current_date = null;
                                    foreach ($activeConversation['messages'] as $message): 
                                        // Display date separator if date changes
                                        if ($current_date !== $message['date']):
                                            $current_date = $message['date'];
                                    ?>
                                        <div class="date-separator">
                                            <span><?php echo htmlspecialchars($current_date); ?></span>
                                        </div>
                                    <?php endif; ?>
                                        
                                        <div class="message-item <?php echo ($message['sender_id'] === $user['id']) ? 'outgoing' : 'incoming'; ?>">
                                            <?php if ($message['sender_id'] !== $user['id']): ?>
                                                <div class="message-avatar">
                                                    <?php 
                                                    $sender_initials = '';
                                                    $sender_name_parts = explode(' ', $message['sender_name']);
                                                    foreach ($sender_name_parts as $part) {
                                                        $sender_initials .= substr($part, 0, 1);
                                                    }
                                                    echo htmlspecialchars($sender_initials);
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="message-bubble">
                                                <div class="message-content">
                                                    <?php echo htmlspecialchars($message['content']); ?>
                                                </div>
                                                <div class="message-meta">
                                                    <span class="message-time"><?php echo htmlspecialchars($message['time']); ?></span>
                                                    <?php if ($message['sender_id'] === $user['id']): ?>
                                                        <span class="message-status">
                                                            <?php if ($message['status'] === 'read'): ?>
                                                                <i class="fas fa-check-double"></i>
                                                            <?php else: ?>
                                                                <i class="fas fa-check"></i>
                                                            <?php endif; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="no-messages">
                                        <div class="empty-state">
                                            <i class="fas fa-comments"></i>
                                            <h3>No messages yet</h3>
                                            <p>Start the conversation by sending a message below.</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="message-input-container">
                                <button class="attachment-btn"><i class="fas fa-paperclip"></i></button>
                                <div class="message-input-wrapper">
                                    <textarea class="message-input" placeholder="Type a message..."></textarea>
                                    <div class="input-actions">
                                        <button class="emoji-btn"><i class="far fa-smile"></i></button>
                                    </div>
                                </div>
                                <button class="send-btn"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        <?php else: ?>
                            <div class="no-conversation-selected">
                                <div class="empty-state">
                                    <i class="fas fa-comments"></i>
                                    <h3>Select a Conversation</h3>
                                    <p>Choose a conversation from the list or start a new one.</p>
                                    <button class="btn btn-primary new-conversation-btn">
                                        <i class="fas fa-plus"></i> New Conversation
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php elseif ($activeTab === 'forums'): ?>
                <!-- Forums Section -->
                <div class="forums-container">
                    <div class="forums-sidebar">
                        <div class="forums-header">
                            <h3>Categories</h3>
                        </div>
                        
                        <div class="forums-list">
                            <?php foreach ($user['forums'] as $forum): ?>
                                <a href="?tab=forums&forum=<?php echo $forum['id']; ?>" class="forum-item <?php echo ($activeForumId === $forum['id']) ? 'active' : ''; ?>">
                                    <div class="forum-icon">
                                        <?php if ($forum['name'] === 'General Discussion'): ?>
                                            <i class="fas fa-comments"></i>
                                        <?php elseif ($forum['name'] === 'Course Reviews'): ?>
                                            <i class="fas fa-star"></i>
                                        <?php elseif ($forum['name'] === 'Technical Help'): ?>
                                            <i class="fas fa-question-circle"></i>
                                        <?php else: ?>
                                            <i class="fas fa-folder"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="forum-info">
                                        <h4 class="forum-name"><?php echo htmlspecialchars($forum['name']); ?></h4>
                                        <p class="forum-description"><?php echo htmlspecialchars($forum['description']); ?></p>
                                    </div>
                                    <div class="forum-count">
                                        <span><?php echo count($forum['topics']); ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="forum-content">
                        <?php if ($activeForum): ?>
                            <div class="forum-header">
                                <h3><?php echo htmlspecialchars($activeForum['name']); ?></h3>
                                <button class="btn btn-primary new-topic-btn">
                                    <i class="fas fa-plus"></i> New Topic
                                </button>
                            </div>
                            
                            <?php if ($activeTopic): ?>
                                <!-- Topic View -->
                                <div class="topic-container">
                                    <div class="topic-header">
                                        <div class="topic-title-container">
                                            <h4 class="topic-title"><?php echo htmlspecialchars($activeTopic['title']); ?></h4>
                                            <div class="topic-meta">
                                                <span class="topic-author">Started by <?php echo htmlspecialchars($activeTopic['author']); ?></span>
                                                <span class="topic-stats">
                                                    <i class="fas fa-eye"></i> <?php echo $activeTopic['views']; ?> views
                                                    <i class="fas fa-reply"></i> <?php echo $activeTopic['replies']; ?> replies
                                                </span>
                                            </div>
                                        </div>
                                        <a href="?tab=forums&forum=<?php echo $activeForumId; ?>" class="btn btn-outline back-btn">
                                            <i class="fas fa-arrow-left"></i> Back to Topics
                                        </a>
                                    </div>
                                    
                                    <!-- Placeholder for topic posts -->
                                    <div class="topic-posts">
                                        <div class="post-item original">
                                            <div class="post-author">
                                                <div class="author-avatar">
                                                    <?php 
                                                    $author_initials = substr($activeTopic['author'], 0, 1);
                                                    echo htmlspecialchars($author_initials);
                                                    ?>
                                                </div>
                                                <div class="author-info">
                                                    <h4 class="author-name"><?php echo htmlspecialchars($activeTopic['author']); ?></h4>
                                                    <span class="author-role">Student</span>
                                                </div>
                                            </div>
                                            <div class="post-content">
                                                <p>This is the original post content. In a real application, this would be loaded from the database.</p>
                                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam auctor, nisl eget ultricies tincidunt, nisl nisl aliquam nisl, eget ultricies nisl nisl eget nisl.</p>
                                            </div>
                                            <div class="post-footer">
                                                <span class="post-date">Posted <?php echo htmlspecialchars($activeTopic['last_post']); ?></span>
                                                <div class="post-actions">
                                                    <button class="action-btn"><i class="fas fa-reply"></i> Reply</button>
                                                    <button class="action-btn"><i class="fas fa-flag"></i> Report</button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Sample replies -->
                                        <div class="post-item reply">
                                            <div class="post-author">
                                                <div class="author-avatar">E</div>
                                                <div class="author-info">
                                                    <h4 class="author-name">Emma Rodriguez</h4>
                                                    <span class="author-role">Student</span>
                                                </div>
                                            </div>
                                            <div class="post-content">
                                                <p>This is a sample reply to the topic.</p>
                                                <p>In a real application, all replies would be loaded from the database.</p>
                                            </div>
                                            <div class="post-footer">
                                                <span class="post-date">Posted 3 days ago</span>
                                                <div class="post-actions">
                                                    <button class="action-btn"><i class="fas fa-reply"></i> Reply</button>
                                                    <button class="action-btn"><i class="fas fa-flag"></i> Report</button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="post-item reply">
                                            <div class="post-author">
                                                <div class="author-avatar">M</div>
                                                <div class="author-info">
                                                    <h4 class="author-name">Michael Chen</h4>
                                                    <span class="author-role">Instructor</span>
                                                </div>
                                            </div>
                                            <div class="post-content">
                                                <p>Here's another sample reply with some technical information.</p>
                                                <p>When working with CSS Grid, remember that you can use the <code>minmax()</code> function to create responsive layouts without media queries.</p>
                                            </div>
                                            <div class="post-footer">
                                                <span class="post-date">Posted 2 days ago</span>
                                                <div class="post-actions">
                                                    <button class="action-btn"><i class="fas fa-reply"></i> Reply</button>
                                                    <button class="action-btn"><i class="fas fa-flag"></i> Report</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Reply form -->
                                    <div class="reply-form">
                                        <h4>Post a Reply</h4>
                                        <textarea placeholder="Write your reply here..."></textarea>
                                        <div class="form-actions">
                                            <button class="btn btn-outline">Cancel</button>
                                            <button class="btn btn-primary">Post Reply</button>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Topics List View -->
                                <div class="topics-container">
                                    <div class="topics-header">
                                        <div class="topics-header-left">
                                            <h4>Topic</h4>
                                        </div>
                                        <div class="topics-header-right">
                                            <span class="header-replies">Replies</span>
                                            <span class="header-views">Views</span>
                                            <span class="header-activity">Last Post</span>
                                        </div>
                                    </div>
                                    
                                    <div class="topics-list">
                                        <?php foreach ($activeForum['topics'] as $topic): ?>
                                            <a href="?tab=forums&forum=<?php echo $activeForumId; ?>&topic=<?php echo $topic['id']; ?>" class="topic-item">
                                                <div class="topic-info">
                                                    <h4 class="topic-title"><?php echo htmlspecialchars($topic['title']); ?></h4>
                                                    <span class="topic-author">Started by <?php echo htmlspecialchars($topic['author']); ?></span>
                                                </div>
                                                <div class="topic-stats">
                                                    <span class="topic-replies"><?php echo $topic['replies']; ?></span>
                                                    <span class="topic-views"><?php echo $topic['views']; ?></span>
                                                    <span class="topic-last-post"><?php echo htmlspecialchars($topic['last_post']); ?></span>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="no-forum-selected">
                                <div class="empty-state">
                                    <i class="fas fa-comments"></i>
                                    <h3>Select a Forum</h3>
                                    <p>Choose a forum category from the list to view topics.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php elseif ($activeTab === 'reviews'): ?>
                <!-- Reviews Section -->
                <div class="reviews-container">
                    <div class="reviews-header">
                        <h3>Course Reviews</h3>
                        <div class="reviews-filter">
                            <select class="filter-select">
                                <option value="all">All Courses</option>
                                <option value="1">Introduction to Web Development</option>
                                <option value="2">Advanced Digital Marketing</option>
                                <option value="3">Photography for Beginners</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="reviews-list">
                        <?php foreach ($user['reviews'] as $review): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <div class="reviewer-avatar">
                                            <?php 
                                            $reviewer_initial = substr($review['author'], 0, 1);
                                            echo htmlspecialchars($reviewer_initial);
                                            ?>
                                        </div>
                                        <div class="reviewer-details">
                                            <h4 class="reviewer-name"><?php echo htmlspecialchars($review['author']); ?></h4>
                                            <span class="review-date"><?php echo htmlspecialchars($review['date']); ?></span>
                                        </div>
                                    </div>
                                    <div class="review-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo ($i <= $review['rating']) ? 'filled' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <div class="review-course">
                                    <span class="course-label">Course:</span>
                                    <a href="course-details.php?id=<?php echo $review['course_id']; ?>" class="course-title">
                                        <?php echo htmlspecialchars($review['course_title']); ?>
                                    </a>
                                </div>
                                
                                <div class="review-content">
                                    <p><?php echo htmlspecialchars($review['content']); ?></p>
                                </div>
                                
                                <div class="review-actions">
                                    <button class="like-btn <?php echo ($review['likes'] > 0) ? 'liked' : ''; ?>">
                                        <i class="fas fa-thumbs-up"></i>
                                        <span class="like-count"><?php echo $review['likes']; ?></span>
                                    </button>
                                    <button class="comment-btn">
                                        <i class="fas fa-comment"></i>
                                        <span class="comment-count"><?php echo count($review['comments']); ?></span>
                                    </button>
                                </div>
                                
                                <?php if (count($review['comments']) > 0): ?>
                                    <div class="review-comments">
                                        <?php foreach ($review['comments'] as $comment): ?>
                                            <div class="comment-item">
                                                <div class="comment-header">
                                                    <span class="comment-author"><?php echo htmlspecialchars($comment['author']); ?></span>
                                                    <span class="comment-date"><?php echo htmlspecialchars($comment['date']); ?></span>
                                                </div>
                                                <div class="comment-content">
                                                    <p><?php echo htmlspecialchars($comment['content']); ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="comment-form">
                                    <textarea placeholder="Write a comment..."></textarea>
                                    <button class="btn btn-primary">Post</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar
            const menuToggle = document.getElementById('menuToggle');
            const adminContainer = document.getElementById('adminContainer');
            const sidebar = document.getElementById('sidebar');
            
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                adminContainer.classList.toggle('sidebar-collapsed');
            });
            
            // New conversation button
            const newConversationBtn = document.querySelector('.new-conversation-btn');
            if (newConversationBtn) {
                newConversationBtn.addEventListener('click', function() {
                    // In a real app, this would open a modal to select contacts
                    alert('This would open a new conversation dialog in a real application.');
                });
            }
            
            // New message button
            const newMessageBtn = document.querySelector('.new-message-btn');
            if (newMessageBtn) {
                newMessageBtn.addEventListener('click', function() {
                    // In a real app, this would open a modal to select contacts
                    alert('This would open a new message dialog in a real application.');
                });
            }
            
            // New topic button
            const newTopicBtn = document.querySelector('.new-topic-btn');
            if (newTopicBtn) {
                newTopicBtn.addEventListener('click', function() {
                    // In a real app, this would open a form to create a new topic
                    alert('This would open a new topic form in a real application.');
                });
            }
            
            // Message input handling
            const messageInput = document.querySelector('.message-input');
            const sendBtn = document.querySelector('.send-btn');
            
            if (messageInput && sendBtn) {
                // Auto-resize textarea as user types
                messageInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
                
                // Send message on button click
                sendBtn.addEventListener('click', function() {
                    const message = messageInput.value.trim();
                    if (message) {
                        // In a real app, this would send the message to the server
                        alert('Message would be sent in a real application: ' + message);
                        messageInput.value = '';
                        messageInput.style.height = 'auto';
                    }
                });
                
                // Send message on Enter key (but allow Shift+Enter for new line)
                messageInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        sendBtn.click();
                    }
                });
            }
            
            // Like buttons
            const likeButtons = document.querySelectorAll('.like-btn');
            likeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    this.classList.toggle('liked');
                    const likeCount = this.querySelector('.like-count');
                    const currentCount = parseInt(likeCount.textContent);
                    
                    if (this.classList.contains('liked')) {
                        likeCount.textContent = currentCount + 1;
                    } else {
                        likeCount.textContent = currentCount - 1;
                    }
                });
            });
        });
    </script>
</body>
</html>
