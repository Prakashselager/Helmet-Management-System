<?php
require_once 'includes/header.php';
?>

<style>
    /* About Page Styles */
    .about-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        animation: fadeIn 1s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .about-content section {
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 5px solid #2874f0;
    }
    
    .about-content section:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .about-content h2 {
        text-align: center;
        color: #2874f0;
        font-size: 2.5rem;
        margin-bottom: 40px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    
    .about-content h3 {
        color: #2874f0;
        font-size: 1.8rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .about-content h3::before {
        content: "•";
        color: #FF5733;
        font-size: 2rem;
    }
    
    .about-content p {
        line-height: 1.8;
        color: #333;
        font-size: 1.1rem;
    }
    
    .about-content ul {
        padding-left: 20px;
    }
    
    .about-content li {
        margin-bottom: 10px;
        position: relative;
        padding-left: 30px;
        line-height: 1.6;
    }
    
    .about-content li::before {
        content: "✓";
        color: #33FF57;
        position: absolute;
        left: 0;
        font-weight: bold;
    }
    
    .team-section {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
    }
    
    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin-top: 30px;
    }
    
    .team-member {
        background: white;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .team-member:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    }
    
    .team-member img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid #2874f0;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    
    .team-member:hover img {
        transform: scale(1.05);
        border-color: #FF5733;
    }
    
    .team-member h4 {
        color: #2874f0;
        margin: 10px 0 5px;
        font-size: 1.3rem;
    }
    
    .team-member p {
        color: #666;
        font-style: italic;
    }
</style>

<h2>About Helmet World</h2>

<div class="about-content">
    <section>
        <h3><i class="fas fa-history"></i> Our Story</h3>
        <p>Helmet World was founded in 2010 with a simple mission: to provide high-quality helmets for all types of riders at affordable prices. What started as a small local shop has grown into one of the leading online helmet retailers in the country.</p>
    </section>

    <section>
        <h3><i class="fas fa-helmet-safety"></i> Our Products</h3>
        <p>We offer a wide range of helmets including:</p>
        <ul>
            <li>Motorcycle helmets (Full-face, Modular, Open-face)</li>
            <li>Bicycle helmets (Road, Mountain, Commuter)</li>
            <li>Sports helmets (Skateboarding, Snowboarding, Climbing)</li>
            <li>Industrial safety helmets</li>
        </ul>
        <p>All our products meet or exceed international safety standards.</p>
    </section>

    <section>
        <h3><i class="fas fa-handshake"></i> Our Commitment</h3>
        <p>At Helmet World, we're committed to:</p>
        <ul>
            <li>Providing only certified, high-quality helmets</li>
            <li>Exceptional customer service</li>
            <li>Competitive pricing</li>
            <li>Fast and reliable shipping</li>
            <li>30-day return policy</li>
        </ul>
    </section>

    <section class="team-section">
        <h3><i class="fas fa-users"></i> Meet Our Team</h3>
        <div class="team-grid">
            <div class="team-member">
                <img src="assets/images/team1.jpg" alt="Team Member">
                <h4>John Smith</h4>
                <p>Founder & CEO</p>
            </div>
            <div class="team-member">
                <img src="assets/images/team2.jpg" alt="Team Member">
                <h4>Sarah Johnson</h4>
                <p>Head of Operations</p>
            </div>
            <div class="team-member">
                <img src="assets/images/team3.jpg" alt="Team Member">
                <h4>Mike Davis</h4>
                <p>Customer Support</p>
            </div>
        </div>
    </section>
</div>

<?php
require_once 'includes/footer.php';
?>