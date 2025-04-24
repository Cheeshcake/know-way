<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Exemple de données pour les cours (dans une application réelle, ces données viendraient d'une base de données)
$featuredCourses = [
    [
        'id' => 1,
        'title' => 'Introduction to Web Development',
        'description' => 'Learn the basics of HTML, CSS, and JavaScript to build your first website.',
        'instructor' => 'Sophie Martin',
        'rating' => 4.8,
        'students' => 1245,
        'image' => 'placeholder-course.png',
        'price' => 'FREE',
        'category' => 'Development'
    ],
    [
        'id' => 2,
        'title' => 'Advanced Digital Marketing',
        'description' => 'Master the latest digital marketing strategies and tools to grow your business online.',
        'instructor' => 'Thomas Dubois',
        'rating' => 4.6,
        'students' => 987,
        'image' => 'placeholder-course.png',
        'price' => 'FREE',
        'category' => 'Marketing'
    ],
    [
        'id' => 3,
        'title' => 'Photography for Beginners',
        'description' => 'Learn the fundamentals of photography and take stunning photos with any camera.',
        'instructor' => 'Emma Leroy',
        'rating' => 4.9,
        'students' => 1532,
        'image' => 'placeholder-course.png',
        'price' => 'FREE',
        'category' => 'Photography'
    ]
];

$allCourses = [
    [
        'id' => 4,
        'title' => 'Data Science Fundamentals',
        'description' => 'Learn the basics of data analysis, visualization, and machine learning.',
        'instructor' => 'Ahmed Benali',
        'rating' => 4.7,
        'students' => 1876,
        'image' => 'placeholder-course.png',
        'price' => 'FREE',
        'category' => 'Data Science'
    ],
    [
        'id' => 5,
        'title' => 'Graphic Design Masterclass',
        'description' => 'Master the principles of design and create stunning visuals with professional tools.',
        'instructor' => 'Léa Moreau',
        'rating' => 4.5,
        'students' => 1123,
        'image' => 'placeholder-course.png',
        'price' => 'FREE',
        'category' => 'Design'
    ],
    [
        'id' => 6,
        'title' => 'Business Management Essentials',
        'description' => 'Learn the core principles of business management and leadership.',
        'instructor' => 'Nicolas Petit',
        'rating' => 4.4,
        'students' => 945,
        'image' => 'placeholder-course.png',
        'price' => 'FREE',
        'category' => 'Business'
    ],
    [
        'id' => 1,
        'title' => 'Introduction to Web Development',
        'description' => 'Learn the basics of HTML, CSS, and JavaScript to build your first website.',
        'instructor' => 'Sophie Martin',
        'rating' => 4.8,
        'students' => 1245,
        'image' => 'placeholder-course.png',
        'price' => 'FREE',
        'category' => 'Development'
    ],
    [
        'id' => 2,
        'title' => 'Advanced Digital Marketing',
        'description' => 'Master the latest digital marketing strategies and tools to grow your business online.',
        'instructor' => 'Thomas Dubois',
        'rating' => 4.6,
        'students' => 987,
        'image' => 'placeholder-course.png',
        'price' => 'FREE',
        'category' => 'Marketing'
    ],
    [
        'id' => 3,
        'title' => 'Photography for Beginners',
        'description' => 'Learn the fundamentals of photography and take stunning photos with any camera.',
        'instructor' => 'Emma Leroy',
        'rating' => 4.9,
        'students' => 1532,
        'image' => 'placeholder-course.png',
        'price' => 'FREE',
        'category' => 'Photography'
    ]
];

// Catégories de cours
$categories = [
    'All',
    'Development',
    'Business',
    'Marketing',
    'Design',
    'Photography',
    'Music',
    'Data Science',
    'Personal Development'
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KnowWay - Learn Anything, Anytime</title>
    <link rel="stylesheet" href="interface.css">
    <link rel="stylesheet" href="chatbot.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo-container">
                    <h1 class="logo">KnowWay</h1>
                </div>
                
                <div class="search-container">
                    <form class="search-form">
                        <input type="text" placeholder="Search for courses..." class="search-input">
                        <button type="submit" class="search-button">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                
                <div class="nav-buttons">
                    <a href="index.php" class="btn btn-outline">Sign In</a>
                    <a href="signup.php" class="btn btn-primary">Sign Up</a>
                </div>
            </div>
        </div>
    </header>
    
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h2 class="hero-title">Expand Your Knowledge with KnowWay</h2>
                <p class="hero-subtitle">Access thousands of courses taught by expert instructors</p>
                <div class="hero-cta">
                    <a href="signup.php" class="btn btn-primary btn-lg">Get Started</a>
                    <a href="#featured-courses" class="btn btn-outline btn-lg">Explore Courses</a>
                </div>
            </div>
        </div>
    </section>
    
    <section class="features-section">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h3 class="feature-title">Learn Anywhere</h3>
                    <p class="feature-description">Access your courses from anywhere, on any device, at any time.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3 class="feature-title">Certified Courses</h3>
                    <p class="feature-description">Earn certificates upon completion to showcase your skills.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="feature-title">Expert Instructors</h3>
                    <p class="feature-description">Learn from industry experts with real-world experience.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="feature-title">Self-Paced Learning</h3>
                    <p class="feature-description">Learn at your own pace with lifetime access to courses.</p>
                </div>
            </div>
        </div>
    </section>
    
    <section id="featured-courses" class="courses-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Featured Courses</h2>
                <p class="section-subtitle">Discover our most popular courses</p>
            </div>
            
            <div class="courses-grid">
                <?php foreach ($featuredCourses as $course): ?>
                <div class="course-card">
                    <div class="course-image">
                    <img src="placeholder-course.png" alt="Introduction to JavaScript">
                        <div class="course-category"><?php echo htmlspecialchars($course['category']); ?></div>
                    </div>
                    <div class="course-content">
                        <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p class="course-instructor">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($course['instructor']); ?>
                        </p>
                        <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
                        <div class="course-meta">
                            <div class="course-rating">
                                <i class="fas fa-star"></i>
                                <span><?php echo htmlspecialchars($course['rating']); ?></span>
                            </div>
                            <div class="course-students">
                                <i class="fas fa-user-graduate"></i>
                                <span><?php echo htmlspecialchars($course['students']); ?> students</span>
                            </div>
                        </div>
                        <div class="course-footer">
                            <div class="course-price"><?php echo $course['price'] === 'FREE' ? 'FREE' : '$' . htmlspecialchars($course['price']); ?></div>
                            <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-outline btn-sm">View Course</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="view-all-container">
                <a href="#all-courses" class="btn btn-outline">View All Courses</a>
            </div>
        </div>
    </section>
    
    <section class="categories-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Browse Categories</h2>
                <p class="section-subtitle">Find the perfect course by category</p>
            </div>
            
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                <a href="courses.php?category=<?php echo urlencode($category); ?>" class="category-card">
                    <div class="category-icon">
                        <?php
                        $icon = 'fas fa-book';
                        switch ($category) {
                            case 'Development':
                                $icon = 'fas fa-code';
                                break;
                            case 'Business':
                                $icon = 'fas fa-briefcase';
                                break;
                            case 'Marketing':
                                $icon = 'fas fa-bullhorn';
                                break;
                            case 'Design':
                                $icon = 'fas fa-palette';
                                break;
                            case 'Photography':
                                $icon = 'fas fa-camera';
                                break;
                            case 'Music':
                                $icon = 'fas fa-music';
                                break;
                            case 'Data Science':
                                $icon = 'fas fa-chart-bar';
                                break;
                            case 'Personal Development':
                                $icon = 'fas fa-brain';
                                break;
                        }
                        ?>
                        <i class="<?php echo $icon; ?>"></i>
                    </div>
                    <h3 class="category-title"><?php echo htmlspecialchars($category); ?></h3>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <section id="all-courses" class="courses-section all-courses-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">All Courses</h2>
                <p class="section-subtitle">Explore our complete course library</p>
            </div>
            
            <div class="filter-container">
                <div class="filter-group">
                    <label for="category-filter">Category:</label>
                    <select id="category-filter" class="filter-select">
                        <option value="all">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <?php if ($category !== 'All'): ?>
                            <option value="<?php echo strtolower($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="sort-filter">Sort By:</label>
                    <select id="sort-filter" class="filter-select">
                        <option value="popular">Most Popular</option>
                        <option value="newest">Newest</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="rating">Highest Rated</option>
                    </select>
                </div>
            </div>
            
            <div class="courses-grid">
                <?php foreach ($allCourses as $course): ?>
                <div class="course-card">
                    <div class="course-image">
                    <img src="placeholder-course.png" alt="Introduction to JavaScript">
                        <div class="course-category"><?php echo htmlspecialchars($course['category']); ?></div>
                    </div>
                    <div class="course-content">
                        <h3 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p class="course-instructor">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($course['instructor']); ?>
                        </p>
                        <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
                        <div class="course-meta">
                            <div class="course-rating">
                                <i class="fas fa-star"></i>
                                <span><?php echo htmlspecialchars($course['rating']); ?></span>
                            </div>
                            <div class="course-students">
                                <i class="fas fa-user-graduate"></i>
                                <span><?php echo htmlspecialchars($course['students']); ?> students</span>
                            </div>
                        </div>
                        <div class="course-footer">
                            <div class="course-price"><?php echo $course['price'] === 'FREE' ? 'FREE' : '$' . htmlspecialchars($course['price']); ?></div>
                            <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-outline btn-sm">View Course</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="pagination">
                <a href="#" class="pagination-item active">1</a>
                <a href="#" class="pagination-item">2</a>
                <a href="#" class="pagination-item">3</a>
                <a href="#" class="pagination-item">4</a>
                <a href="#" class="pagination-item">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
    </section>
    
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">Ready to Start Learning?</h2>
                <p class="cta-description">Join thousands of students already learning on KnowWay</p>
                <a href="signup.php" class="btn btn-primary btn-lg">Sign Up Now</a>
            </div>
        </div>
    </section>
    
    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h3 class="footer-title">KnowWay</h3>
                    <p class="footer-description">
                        KnowWay is a leading online learning platform that helps anyone learn business, software, technology, and creative skills to achieve personal and professional goals.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3 class="footer-title">Explore</h3>
                    <ul class="footer-links">
                        <li><a href="#">Home</a></li>
                        <li><a href="#">Courses</a></li>
                        <li><a href="#">Instructors</a></li>
                        <li><a href="#">Resources</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3 class="footer-title">Categories</h3>
                    <ul class="footer-links">
                        <li><a href="#">Development</a></li>
                        <li><a href="#">Business</a></li>
                        <li><a href="#">Marketing</a></li>
                        <li><a href="#">Design</a></li>
                        <li><a href="#">Photography</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3 class="footer-title">Support</h3>
                    <ul class="footer-links">
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p class="copyright">© 2025 KnowWay. All rights reserved.</p>
                <div class="language-selector">
                    <select class="language-select">
                        <option value="en">English</option>
                        <option value="fr">Français</option>
                        <option value="es">Español</option>
                        <option value="de">Deutsch</option>
                        <option value="ar">العربية</option>
                    </select>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Chatbot Component -->
    <div class="chatbot-container">
        <div class="chatbot-toggle" id="chatbotToggle">
            <i class="fas fa-comment-dots"></i>
        </div>
        
        <div class="chatbot-window" id="chatbotWindow">
            <div class="chatbot-header">
                <div class="chatbot-title">
                    <div class="chatbot-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3>KnowWay Assistant</h3>
                </div>
                <button class="chatbot-close" id="chatbotClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="chatbot-messages" id="chatbotMessages">
                <div class="chatbot-message bot">
                    Hi there! I'm KnowWay's learning assistant. How can I help you today?
                </div>
            </div>
            
            <div class="chatbot-input">
                <input type="text" id="chatbotInput" placeholder="Type your message here..." autocomplete="off">
                <button id="chatbotSend" type="button">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 80,
                            behavior: 'smooth'
                        });
                    }
                });
            });
            
            // Filter functionality
            const categoryFilter = document.getElementById('category-filter');
            const sortFilter = document.getElementById('sort-filter');
            const courseCards = document.querySelectorAll('.all-courses-section .course-card');
            
            if (categoryFilter && sortFilter) {
                categoryFilter.addEventListener('change', filterCourses);
                sortFilter.addEventListener('change', filterCourses);
                
                function filterCourses() {
                    const categoryValue = categoryFilter.value;
                    
                    courseCards.forEach(card => {
                        const category = card.querySelector('.course-category').textContent.toLowerCase();
                        
                        if (categoryValue === 'all' || category === categoryValue) {
                            card.style.display = 'flex';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                }
            }
            
            // Sticky header on scroll
            const header = document.querySelector('.main-header');
            let lastScrollTop = 0;
            
            window.addEventListener('scroll', function() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop > 100) {
                    header.classList.add('sticky');
                    
                    if (scrollTop > lastScrollTop) {
                        // Scrolling down
                        header.classList.add('hide');
                    } else {
                        // Scrolling up
                        header.classList.remove('hide');
                    }
                } else {
                    header.classList.remove('sticky');
                    header.classList.remove('hide');
                }
                
                lastScrollTop = scrollTop;
            });
            
            // Chatbot functionality
            const chatbotToggle = document.getElementById('chatbotToggle');
            const chatbotWindow = document.getElementById('chatbotWindow');
            const chatbotClose = document.getElementById('chatbotClose');
            const chatbotMessages = document.getElementById('chatbotMessages');
            const chatbotInput = document.getElementById('chatbotInput');
            const chatbotSend = document.getElementById('chatbotSend');
            
            // Toggle chatbot window
            chatbotToggle.addEventListener('click', function() {
                chatbotWindow.classList.toggle('active');
                chatbotToggle.classList.toggle('active');
                
                // Focus input when opening
                if (chatbotWindow.classList.contains('active')) {
                    chatbotInput.focus();
                    // Scroll to bottom of messages
                    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
                }
            });
            
            // Close chatbot window
            chatbotClose.addEventListener('click', function() {
                chatbotWindow.classList.remove('active');
                chatbotToggle.classList.remove('active');
            });
            
            // Send message on button click
            chatbotSend.addEventListener('click', sendMessage);
            
            // Send message on Enter key
            chatbotInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
            
            // Enable/disable send button based on input
            chatbotInput.addEventListener('input', function() {
                chatbotSend.disabled = !chatbotInput.value.trim();
            });
            
            // Initialize send button state
            chatbotSend.disabled = !chatbotInput.value.trim();
            
            // Function to send message to chatbot
            function sendMessage() {
                const message = chatbotInput.value.trim();
                if (!message) return;
                
                // Add user message to chat
                addMessage(message, 'user');
                
                // Clear input
                chatbotInput.value = '';
                chatbotSend.disabled = true;
                
                // Show typing indicator
                const typingIndicator = document.createElement('div');
                typingIndicator.className = 'chatbot-typing';
                typingIndicator.innerHTML = '<span></span><span></span><span></span>';
                chatbotMessages.appendChild(typingIndicator);
                scrollToBottom();
                
                // Send message to server
                fetch('chatbot.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ message: message })
                })
                .then(response => response.json())
                .then(data => {
                    // Remove typing indicator
                    if (typingIndicator.parentNode) {
                        typingIndicator.parentNode.removeChild(typingIndicator);
                    }
                    
                    // Add bot response
                    setTimeout(() => {
                        addMessage(data.reply, 'bot');
                    }, 500); // Small delay for natural feel
                })
                .catch(error => {
                    // Remove typing indicator
                    if (typingIndicator.parentNode) {
                        typingIndicator.parentNode.removeChild(typingIndicator);
                    }
                    
                    // Add error message
                    addMessage("Sorry, I'm having trouble connecting right now. Please try again later.", 'bot');
                    console.error('Chatbot error:', error);
                });
            }
            
            // Function to add message to chat
            function addMessage(text, sender) {
                const messageElement = document.createElement('div');
                messageElement.className = `chatbot-message ${sender}`;
                messageElement.textContent = text;
                chatbotMessages.appendChild(messageElement);
                scrollToBottom();
            }
            
            // Function to scroll chat to bottom
            function scrollToBottom() {
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }
        });
    </script>
</body>
</html>
