<?php
session_start();
require_once 'includes/db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Aviation Family SL</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .about-wrapper {
            padding: 80px 0;
            background: #f8fafc;
        }
        .about-flex {
            display: flex;
            align-items: center;
            gap: 50px;
            margin-bottom: 60px;
        }
        .about-image {
            flex: 1;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .about-image img {
            width: 100%;
            display: block;
        }
        .about-content {
            flex: 1.2;
        }
        .about-content h1 {
            color: #002147;
            font-size: 2.5rem;
            margin-bottom: 20px;
            position: relative;
        }
        .about-content h1::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: #FFC107;
            margin-top: 10px;
        }
        .about-text p {
            font-size: 1.05rem;
            line-height: 1.8;
            color: #475569;
            margin-bottom: 20px;
        }
        .highlight-box {
            background: #002147;
            color: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            margin-top: 40px;
        }
        .highlight-box h2 {
            color: #FFC107;
            margin-bottom: 15px;
        }
        @media (max-width: 992px) {
            .about-flex { flex-direction: column; }
            .about-content { text-align: center; }
            .about-content h1::after { margin: 10px auto; }
        }
    </style>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <div class="about-wrapper">
        <div class="container">
            <div class="about-flex">
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1559136555-9303baea8ebd?q=80&w=1000" alt="Community">
                </div>
                <div class="about-content">
                    <h1>About Us</h1>
                    <div class="about-text">
                        <p><strong>Aviation Family Sri Lanka</strong> is a dedicated aviation community founded with the vision of connecting, educating, and inspiring aviation enthusiasts across Sri Lanka and beyond.</p>
                        <p>We bring together students, professionals, hobbyists, and industry followers who share a deep passion for aviation, aerospace, and everything that flies.</p>
                        <p>Our platform serves as a hub for aviation knowledge, news, career guidance, and community engagement. From commercial aviation and military aviation to flight simulation and aircraft engineering, we aim to create a space where curiosity meets expertise.</p>
                    </div>
                </div>
            </div>

            <div class="about-text">
                <p>At Aviation Family Sri Lanka, we believe aviation is more than an industry—it is a lifestyle and a global family. We are committed to promoting aviation awareness in Sri Lanka by sharing accurate information, encouraging young talent, and supporting those who aspire to build careers in aviation-related fields.</p>
                <p>Through digital content, community discussions, educational initiatives, and collaborations, we strive to bridge the gap between aviation dreams and real-world opportunities.</p>
                <p>Whether you are an aspiring pilot, engineer, cabin crew member, air traffic controller, aviation photographer, or simply an aviation lover, Aviation Family Sri Lanka welcomes you as part of our growing family.</p>
            </div>

            <div class="highlight-box">
                <h2>Together, We Fly Higher</h2>
                <p>Join our mission to elevate the Sri Lankan aviation community to new heights.</p>
                <a href="signup.php" class="btn-filled" style="display:inline-block; margin-top:20px; background:#FFC107; color:#002147;">Become a Member Today</a>
            </div>
        </div>
    </div>

</body>
</html>