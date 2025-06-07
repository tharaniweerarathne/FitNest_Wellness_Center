<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitnestwellness";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, name, description, image_path, category, price, schedule FROM services";
$result = $conn->query($sql);

$services = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Convert schedule times to AM/PM format
        $schedule = $row["schedule"];
        $formattedSchedule = "";
        
        if ($schedule && is_string($schedule)) {
            $scheduleData = json_decode($schedule, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                foreach ($scheduleData as $day => $times) {
                    if (!empty($times['start']) && !empty($times['end'])) {
                        $startTime = date("g:i A", strtotime($times['start']));
                        $endTime = date("g:i A", strtotime($times['end']));
                        $formattedSchedule .= "$day: $startTime - $endTime<br>";
                    }
                }
                $schedule = $formattedSchedule ?: "Not scheduled";
            } else {
                $schedule = preg_replace_callback('/(\d{1,2}:\d{2})/', function($matches) {
                    return date("g:i A", strtotime($matches[0]));
                }, $schedule);
            }
        }
        
        $row['formatted_schedule'] = $schedule;
        $services[] = $row;
    }
}

// Fetch trainers from the database - use the same connection
$trainer_sql = "SELECT name, description, image_path, category FROM trainer";
$trainer_result = $conn->query($trainer_sql);

$trainers = [];
if ($trainer_result->num_rows > 0) {
    while ($row = $trainer_result->fetch_assoc()) {
        $trainers[] = $row;
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitNest Wellness Center</title>
    <link rel="stylesheet" href="indexPage_Css.css"> 
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav>
        <div class="nav-container">
            <img src="Images/logo.png" alt="FitNest  Logo" class="logo">
            
            <input type="checkbox" id="menu-checkbox">
            <label for="menu-checkbox" class="menu-toggle">
                <div></div>
                <div></div>
                <div></div>
            </label>

            <div class="navbar-links">
                <a href="#home">Home</a>
                <a href="#Aboutus">About Us</a>
                <a href="#trainer">Our Team</a>
                <a href="#Classes">Our Classes</a>
                <a href="#membership">Membership</a>
                <a href="#review">Reviews</a>
                <a href="#blog">Blog</a>
                <a href="#ContactUs">Contact Us</a>
                <button class="navbar-button pulse" onclick="document.location='login.php'" >Join Us</button>
            </div>
        </div>
    </nav>

    <header id="home">
        <div class="intro-container">
            <img src="Images/home5.png" alt="FitNest  Image" class="intro-image">
            <div class="intro-text">
                <h1><span>TRANSFORM YOUR FITNESS JOURNEY AT FITNEST WELLNESS CENTER!</span></h1>
                <p>Dive into a world of fitness with countless workouts, nourishing recipes, and professional insights for a balanced body and mind.</p>
                <div class="cta-buttons">
                    <button class="primary-btn"  onclick="document.location='login.php'">Get Started</button>
                    <button class="secondary-btn" onclick="document.location='#Aboutus'">Learn More</button>
                </div>
            </div>
        </div>
    </header>

    <section id="Aboutus" class="about-section">
        <div class="container">
            <div class="section-header">
                <h2>Discover your path to wellness</h2>
                <p>About FITNEST Wellness Center</p>
            </div>
            
            <div class="about-content">
                <div class="about-image">
                    <img src="Images/aboutus1.jpg" alt="FITNEST Gym Facility">
                </div>
                
                <div class="about-text">
                    <h3>Welcome to FITNEST WELLNESS Center, where fitness meets excellence</h3>
                    <p>FITNEST Wellness Center is designed to welcome individuals of all fitness levels, from newcomers to professional athletes.</p>
                    <p>We are dedicated to helping our members succeed with tailored workout programs, advanced equipment, and a supportive environment that keeps you motivated.</p>
                    
                    
                    
                    
                    <a href="AboutUS.html" class="btn">Read More</a>
                </div>
            </div>
        </div>
    </section>


    <section id="trainer" class="trainer-section">
    <div class="trainer-container">
        <div class="trainer-header">
            <h2>MEET OUR TRAINERS</h2>
        </div>
        
        <div class="trainer-grid">
            <?php if (!empty($trainers)): ?>
                <?php foreach ($trainers as $trainer): ?>
                    <div class="trainer-card">
                        <div class="trainer-image-container">
                            <?php if (!empty($trainer['image_path'])): ?>
                                <img src="<?= htmlspecialchars($trainer['image_path']) ?>" alt="<?= htmlspecialchars($trainer['name']) ?>" class="trainer-image">
                            <?php else: ?>
                                <div class="trainer-image-placeholder">
                                    <i class="ri-user-3-fill"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="trainer-details">
                            <h3 class="trainer-name"><?= htmlspecialchars($trainer['name']) ?></h3>
                            <span class="trainer-category <?= htmlspecialchars($trainer['category']) ?>">
                                <?= ucfirst(htmlspecialchars($trainer['category'])) ?>
                            </span>
                            <p class="trainer-description"><?= htmlspecialchars($trainer['description']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-trainers">No trainers available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

    <section id="Classes" class="class-section">
    <h2>OUR SERVICES</h2>
    <h1>TRAINING PROGRAMS</h1>
    <div class="class-grid">
        <?php if (!empty($services)): ?>
            <?php foreach ($services as $service): ?>
                <div class="class-card">
                    <img src="<?= htmlspecialchars($service['image_path']) ?>" alt="<?= htmlspecialchars($service['name']) ?>" class="class-image">
                    <div class="class-details">
                        <h3 class="class-name"><?= htmlspecialchars($service['name']) ?></h3>
                        <span class="class-category"><?= htmlspecialchars($service['category']) ?></span>
                        <div class="class-schedule"><?= $service['formatted_schedule'] ?></div>
                        <p class="class-paragraph"><?= htmlspecialchars($service['description']) ?></p>
                        <div class="class-price">Rs <?= number_format($service['price'], 2) ?></div>
                        <div class="class-actions">
                            <a href="login.php?service_id=<?= $service['id'] ?>" class="book-btn">Book Now</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1 / -1; text-align: center;">No services found</p>
        <?php endif; ?>
    </div>
</section>

    <section id="membership" class="membership-section">
        <div class="membership-container">
            <div class="membership-header">
                <h2>FITNEST WELLNESS Center Membership</h2>
            </div>
            
            <div class="membership-plans">

                <div class="membership-plan">
                    <h3>Basic Membership</h3>
                    <div class="membership-price">Rs. 6000<span>/month</span></div>
                    <ul class="membership-features">
                        <li>Access to Gym Equipment</li>
                        <li>One Group Class Weekly</li>
                        <li>Locker Facility</li>
                    </ul>
                    <button class="membership-cta" onclick="document.location='login.php'">Join Now</button>
                </div>
                
                <div class="membership-plan">
                    <h3>Premium Membership</h3>
                    <div class="membership-price">Rs. 10,000<span>/month</span></div>
                    <ul class="membership-features">
                        <li>Unlimited Gym Access</li>
                        <li>Personal Trainer Sessions</li>
                        <li>Sauna & Steam Room</li>
                    </ul>
                    <button class="membership-cta" onclick="document.location='login.php'">Join Now</button>
                </div>
                
                <div class="membership-plan">
                    <h3>Elite Package</h3>
                    <div class="membership-price">Rs. 15,000<span>/month</span></div>
                    <ul class="membership-features">
                        <li>All Premium Benefits</li>
                        <li>Customized Diet Plan</li>
                        <li>24/7 Support & Consultations</li>
                    </ul>
                    <button class="membership-cta" onclick="document.location='login.php'">Join Now</button>
                </div>
                
                
            </div>
        </div>
    </section>



    <section id="review" class="reviews-section">
    <div class="review-container">
        <div class="review-header">
            <h2>WHAT OUR CUSTOMERS SAY</h2>
        </div>
        
        <div class="review-carousel">
            <button class="carousel-btn prev-btn" id="prevBtn" aria-label="Previous review">&lt;</button>
            
            <div class="testimonials-wrapper" id="testimonialsWrapper">
                <div class="testimonials-slide" id="testimonialsTrack">
                    <?php
                    $review_sql = "SELECT name, rating, message FROM feedback ORDER BY id DESC LIMIT 10";
                    $review_result = $conn->query($review_sql);
                    
                    if ($review_result && $review_result->num_rows > 0): ?>
                        <?php while($row = $review_result->fetch_assoc()): ?>
                            <div class="testimonial-card">
                                <div class="testimonial-name"><?php echo htmlspecialchars($row['name']); ?></div>
                                <div class="testimonial-message"><?php echo htmlspecialchars($row['message']); ?></div>
                                <div class="testimonial-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?php echo ($i <= $row['rating']) ? 'active' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                    <span class="rating-text">(<?php echo $row['rating']; ?>/5)</span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>

                        <div class="testimonial-card">
                            <div class="testimonial-name">test</div>
                            <div class="testimonial-message">test</div>
                            <div class="testimonial-rating">
                                <span class="star active">★</span>
                                <span class="star active">★</span>
                                <span class="star active">★</span>
                                <span class="star active">★</span>
                                <span class="star active">★</span>
                                <span class="rating-text">(5/5)</span>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-name">test</div>
                            <div class="testimonial-message">Good Service!</div>
                            <div class="testimonial-rating">
                                <span class="star active">★</span>
                                <span class="star active">★</span>
                                <span class="star active">★</span>
                                <span class="star active">★</span>
                                <span class="star active">★</span>
                                <span class="rating-text">(5/5)</span>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div class="testimonial-name">test</div>
                            <div class="testimonial-message">good!</div>
                            <div class="testimonial-rating">
                                <span class="star active">★</span>
                                <span class="star active">★</span>
                                <span class="star active">★</span>
                                <span class="star active">★</span>
                                <span class="star active">★</span>
                                <span class="rating-text">(5/5)</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <button class="carousel-btn next-btn" id="nextBtn" aria-label="Next review">&gt;</button>
        </div>
    </div>
</section>


<section id="blog" class="blog-section">
        <div class="blog-container">
            <h2 class="blog-title">BLOGS</h2>
            <div class="blog-grid">

                <div class="blog-card">
                    <div class="blog-image">
                        <img src="Images/blog_img1.jpg" alt="Workout Routines">
                    </div>
                    <div class="blog-content">
                        <h3>Workout Routines</h3>
                        <p>Discover personalized workout plans to build strength.</p>
                        <a href="blogpage.php?category=workout_plans" class="read-more">Read More</a>

                    </div>
                </div>


                <div class="blog-card">
                    <div class="blog-image">
                        <img src="Images/blog_img2.jpg" alt="Healthy Meal Plans">
                    </div>
                    <div class="blog-content">
                        <h3>Healthy Meal Plans</h3>
                        <p>Fuel your body with wholesome and balanced healthy meals.</p>
                        <a href="blogpage.php?category=healthy_meal_plans" class="read-more">Read More</a>
                    </div>
                </div>


                <div class="blog-card">
                    <div class="blog-image">
                        <img src="Images/blog_img3.jpg" alt="Healthy Recipes">
                    </div>
                    <div class="blog-content">
                        <h3>Healthy Recipes</h3>
                        <p>Create nourishing meals with flavorful and healthy recipes for every lifestyle.</p>
                        <a href="blogpage.php?category=healthy_recipes" class="read-more">Read More</a>
                    </div>
                </div>


                <div class="blog-card">
                    <div class="blog-image">
                        <img src="Images/blog_img6.jpg" alt="Success Stories">
                    </div>
                    <div class="blog-content">
                        <h3>Success Stories</h3>
                        <p>Celebrate journeys of achievement with success stories that motivate change.</p>
                        <a href="blogpage.php?category=success_stories" class="read-more">Read More</a>
                    </div>
                </div>
            </div>
        </div>
    </section>






    <footer section id="ContactUs">
        <div class="footer-container">
            <div class="footer-about">
                <img src="Images/logo_white.png" alt="FITNEST  Logo" class="footer-logo">
                <p>Transform your body, transform your life at FitNest Wellness Center.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#Aboutus">About Us</a></li>
                    <li><a href="classes.html">Classes</a></li>
                    <li><a href="Blog.html">Blog</a></li>
                    <li><a href="#ContactUs">Contact</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h3>Contact Us</h3>
                <p><i class="fas fa-map-marker-alt"></i> Fitness St, Seeduwa</p>
                <p><i class="fas fa-phone"></i> 011 345 6789</p>
                <p><i class="fas fa-envelope"></i> fintnestWellness@gmail.com</p>
                <p><i class="fas fa-clock"></i> Open 24/7</p>
            </div>
            <div class="footer-bmi">
                <h3>BMI Calculator</h3>
                <form id="bmiForm">
                    <div class="bmi-input">
                        <label for="height">Height (cm):</label>
                        <input type="number" id="height" required>
                    </div>
                    <div class="bmi-input">
                        <label for="weight">Weight (kg):</label>
                        <input type="number" id="weight" required>
                    </div>
                    <button type="submit" class="bmi-button">Calculate BMI</button>
                    <div id="bmi-result"></div>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 FITNEST WELLNESS Center. All rights reserved.</p>
        </div>
    </footer>


 
    <script src="JavaScript.js"></script>

    <script>

document.addEventListener('DOMContentLoaded', function() {

    console.log('Testimonials script loaded');
    
    const track = document.querySelector('.testimonials-slide');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    const cards = document.querySelectorAll('.testimonial-card');

    console.log('Track found:', !!track);
    console.log('Prev button found:', !!prevBtn);
    console.log('Next button found:', !!nextBtn);
    console.log('Number of cards:', cards.length);
    
    if (!track || !prevBtn || !nextBtn || cards.length === 0) {
        console.error('Carousel elements not found');
        return;
    }
    
    let currentIndex = 0;
    const totalCards = cards.length;
    let cardWidth = getCardWidth();
    
    function getCardWidth() {
        const card = cards[0];
        const styles = window.getComputedStyle(card);
        const width = card.offsetWidth + 
               parseInt(styles.marginLeft) + 
               parseInt(styles.marginRight);
        console.log('Card width calculated as:', width);
        return width;
    }
    

    function updateCarousel() {
        const translateX = -currentIndex * cardWidth;
        console.log('Updating carousel to position:', translateX);
        track.style.transform = `translateX(${translateX}px)`;
        

        prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '1';
        nextBtn.style.opacity = currentIndex >= totalCards - 1 ? '0.5' : '1';
    }
    
    updateCarousel();
    
    prevBtn.onclick = function() {
        console.log('Previous button clicked');
        if (currentIndex > 0) {
            currentIndex--;
            updateCarousel();
        }
    };
    
    nextBtn.onclick = function() {
        console.log('Next button clicked');
        if (currentIndex < totalCards - 1) {
            currentIndex++;
            updateCarousel();
        }
    };
    
    window.addEventListener('resize', function() {
        cardWidth = getCardWidth();
        updateCarousel();
    });
    
    let touchStartX = 0;
    let touchEndX = 0;
    
    track.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, {passive: true});
    
    track.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, {passive: true});
    
    function handleSwipe() {
        const swipeThreshold = 50;
        
        if (touchStartX - touchEndX > swipeThreshold) {

            nextBtn.onclick();
        } else if (touchEndX - touchStartX > swipeThreshold) {

            prevBtn.onclick();
        }
    }
    
    prevBtn.addEventListener('mousedown', function() {
        console.log('Previous button mousedown event');
    });
    
    nextBtn.addEventListener('mousedown', function() {
        console.log('Next button mousedown event');
    });
});
    </script>


</body>
</html>
