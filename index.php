<?php
require_once 'includes/header.php';
?>

<style>
    /* Main styles */
    body {
        font-family: 'Poppins', sans-serif;
        overflow-x: hidden;
    }
    
    /* Welcome text - stable version */
    .welcome-text {
        text-align: center;
        margin: 30px 0;
        font-size: 3.5rem;
        font-weight: bold;
        background: linear-gradient(45deg, #3357FF);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        background-size: 300% 300%;
        animation: gradient 8s ease infinite;
    }
    
    @keyframes gradient {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    /* Hero section */
    .hero-section {
        position: relative;
        text-align: center;
        margin: 40px 0;
        overflow: hidden;
    }
    
    .hero-image {
        width: 100%;
        max-height: 500px;
        object-fit: cover;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        transition: transform 0.5s ease;
    }
    
    .hero-image:hover {
        transform: scale(1.02);
    }
    
    .shop-now-btn {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        padding: 15px 30px;
        background: linear-gradient(45deg, #FF5733, #FFC300);
        color: white;
        border: none;
        border-radius: 50px;
        font-size: 1.5rem;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        z-index: 10;
    }
    
    .shop-now-btn:hover {
        transform: translate(-50%, -50%) scale(1.1);
        box-shadow: 0 8px 20px rgba(0,0,0,0.4);
        animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(255,87,51,0.7); }
        70% { box-shadow: 0 0 0 15px rgba(255,87,51,0); }
        100% { box-shadow: 0 0 0 0 rgba(255,87,51,0); }
    }
    
    /* Image slider */
    .slider-container {
        width: 100%;
        margin: 40px 0;
        overflow: hidden;
        position: relative;
        border-radius: 10px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    
    .slider {
        display: flex;
        transition: transform 0.5s ease;
        height: 700px;
    }
    
    .slide {
        min-width: 100%;
        height: 100%;
    }
    
    .slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    /* About section */
    .about-section {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 60px 20px;
        margin: 60px 0;
        border-radius: 10px;
        position: relative;
        overflow: hidden;
    }
    
    .about-section::before {
        content: "";
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0) 70%);
        animation: rotate 15s linear infinite;
        z-index: 0;
    }
    
    @keyframes rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .about-content {
        position: relative;
        z-index: 1;
        max-width: 1000px;
        margin: 0 auto;
        text-align: center;
    }
    
    .about-title {
        font-size: 2.8rem;
        margin-bottom: 30px;
        color: #2874f0;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        animation: fadeIn 1s ease;
    }
    
    .about-text {
        font-size: 1.2rem;
        line-height: 1.8;
        color: #333;
        margin-bottom: 30px;
        animation: fadeIn 1.5s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Features section */
    .features-section {
        margin: 60px 0;
        padding: 40px 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        color: white;
    }
    
    .features-title {
        text-align: center;
        margin-bottom: 40px;
        font-size: 2.5rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    .features-container {
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
    }
    
    .feature-box {
        width: 30%;
        min-width: 300px;
        text-align: center;
        padding: 30px;
        margin: 15px;
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(10px);
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.5s ease;
        border: 1px solid rgba(255,255,255,0.2);
    }
    
    .feature-box:hover {
        transform: translateY(-10px) scale(1.05);
        box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        background: rgba(255,255,255,0.2);
    }
    
    .feature-icon {
        font-size: 3rem;
        margin-bottom: 20px;
        display: inline-block;
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    .feature-title {
        font-size: 1.8rem;
        margin-bottom: 15px;
        color: white;
    }
    
    /* Tools section */
    .tools-section {
        margin: 60px 0;
        text-align: center;
        background: url('assets/images/pattern.png');
        padding: 40px 20px;
        border-radius: 10px;
        position: relative;
        overflow: hidden;
    }
    
    .tools-section::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.05);
        z-index: 0;
    }
    
    .tools-title {
        font-size: 2.8rem;
        margin-bottom: 30px;
        color: #333;
        position: relative;
        z-index: 1;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    
    .tools-container {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 30px;
        position: relative;
        z-index: 1;
    }
    
    .tool-item {
        width: 120px;
        height: 120px;
        background: linear-gradient(45deg, var(--color1), var(--color2));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        transition: all 0.5s ease;
        cursor: pointer;
    }
    
    .tool-item:hover {
        transform: rotate(360deg) scale(1.2);
        box-shadow: 0 15px 30px rgba(0,0,0,0.3);
    }
</style>

<!-- Welcome Text - Stable Gradient Animation -->
<div class="welcome-text">
    WELCOME TO BIKE BARBERS HELMET SHOP
</div>

<!-- Hero Section -->
<div class="hero-section">
    <img src="assets/images/Picture1.png" alt="Bike Barbers Helmet Shop" class="hero-image">
    <button class="shop-now-btn" onclick="window.location.href='products.php'">SHOP NOW</button>
</div>

<!-- Image Slider -->
<div class="slider-container">
    <div class="slider">
        <div class="slide">
            <img src="assets/images/slide1.jpg" alt="Helmet Collection 1">
        </div>
        <div class="slide">
            <img src="assets/images/slide2.jpg" alt="Helmet Collection 2">
        </div>
        <div class="slide">
            <img src="assets/images/slide3.jpg" alt="Helmet Collection 3">
        </div>
    </div>
</div>

<!-- About Bike Barbers -->
<div class="about-section">
    <div class="about-content">
        <h2 class="about-title">ABOUT BIKE BARBERS</h2>
        <p class="about-text">
            Bike Barbers is your premier destination for high-quality motorcycle helmets and accessories. 
            Founded in 2010 by passionate riders, we've grown from a small local shop to a nationally 
            recognized brand. Our mission is simple: provide riders with the best protection without 
            compromising on style or comfort.
        </p>
        <p class="about-text">
            Every helmet in our collection is hand-picked by our team of experts, ensuring only the 
            safest and most durable products make it to our shelves. We're not just a store - we're 
            a community of riders who understand what you need on the road.
        </p>
    </div>
</div>

<!-- Why Choose Us Section -->
<div class="features-section">
    <h2 class="features-title">WHY CHOOSE US</h2>
    <div class="features-container">
        <div class="feature-box">
            <div class="feature-icon">🛡️</div>
            <h3 class="feature-title">Premium Quality</h3>
            <p>Our helmets meet the highest safety standards with durable materials and advanced protection technology.</p>
        </div>
        
        <div class="feature-box">
            <div class="feature-icon">💰</div>
            <h3 class="feature-title">Best Prices</h3>
            <p>We offer competitive prices without compromising on quality, with regular discounts and offers.</p>
        </div>
        
        <div class="feature-box">
            <div class="feature-icon">🚚</div>
            <h3 class="feature-title">Fast Shipping</h3>
            <p>Get your helmet delivered quickly with our reliable shipping partners across the country.</p>
        </div>
    </div>
</div>

<!-- Tools & Services Section -->
<div class="tools-section">
    <h2 class="tools-title">OUR TOOLS & SERVICES</h2>
    <div class="tools-container">
        <div class="tool-item" style="--color1: #FF5733; --color2: #FFC300;">🔧</div>
        <div class="tool-item" style="--color1: #33FF57; --color2: #33A2FF;">🛠️</div>
        <div class="tool-item" style="--color1: #B933FF; --color2: #FF33A2;">⚙️</div>
        <div class="tool-item" style="--color1: #33FFF5; --color2: #338CFF;">🧰</div>
        <div class="tool-item" style="--color1: #FF8C33; --color2: #FF3333;">🔩</div>
    </div>
</div>

<script>
    // Image slider functionality
    const slider = document.querySelector('.slider');
    const slides = document.querySelectorAll('.slide');
    let currentIndex = 0;
    const slideCount = slides.length;
    
    function nextSlide() {
        currentIndex = (currentIndex + 1) % slideCount;
        slider.style.transform = `translateX(-${currentIndex * 100}%)`;
    }
    
    // Auto-advance slides every 5 seconds
    setInterval(nextSlide, 5000);
    
    // Animate elements when they come into view
    const animateOnScroll = () => {
        const elements = document.querySelectorAll('.feature-box, .tool-item, .about-title, .about-text');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.3;
            
            if (elementPosition < screenPosition) {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }
        });
    };
    
    // Set initial state for animation
    document.querySelectorAll('.feature-box, .tool-item').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'all 0.8s ease';
    });
    
    document.querySelectorAll('.about-title, .about-text').forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = `all 0.8s ease ${i * 0.2}s`;
    });
    
    window.addEventListener('scroll', animateOnScroll);
    window.addEventListener('load', animateOnScroll);
</script>

<?php
require_once 'includes/footer.php';
?>