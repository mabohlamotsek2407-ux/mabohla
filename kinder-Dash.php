<?php
session_start();

// Check if there is a success message to display
if (isset($_SESSION['success_message'])) {
    echo '<script>alert("' . $_SESSION['success_message'] . '");</script>';
    // Clear the message after displaying it
    unset($_SESSION['success_message']);
}
?>

<!-- Rest of your dashboard HTML content -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kindergarten Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #FF4757;  /* Brighter red */
            --secondary: #1DD1A1; /* Brighter teal */
            --accent: #FFD700;   /* Gold yellow */
            --dark: #FF7F50;     /* Coral */
            --light: #F8F9FF;    /* Very light blue */
            --rainbow-1: #FF9FF3; /* Pink */
            --rainbow-2: #FECA57; /* Yellow */
            --rainbow-3: #FF6B6B; /* Red */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
    font-family: 'Comic Neue', cursive;
    background-color: var(--light);
    background-image: url('https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=1470&q=80');
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
    color: #333;
    min-height: 100vh;
    overflow-x: hidden;
}


        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            display: flex; /* Use flexbox for layout */
            justify-content: space-between; /* Space between items */
            align-items: center; /* Center items vertically */
            margin-bottom: 30px;
            position: relative;
            animation: bounceIn 1s ease-out, floatHeader 8s ease-in-out infinite;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .header-image {
            width: 70px; /* Reduced size from 110px to 70px */
            height: auto;
            border-radius: 10px;
            margin-right: 20px;
        }

        @keyframes floatHeader {
            0%, 100% { transform: translateY(0) rotate(-1deg); }
            50% { transform: translateY(-10px) rotate(1deg); }
        }

        header h1 {
            color: var(--dark);
            font-size: 2.5rem;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }

        header h1::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--accent);
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.5s ease;
        }

        header h1:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        .bg {
            background-color: var(--primary);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.3s ease;
            cursor: pointer;
            width: auto; /* Set width to auto for the button */
            margin-left: 20px; /* Add some space between the title and the button */
        }

        .bg:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }

        .card {
            background-color: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            position: relative;
            height: 200px;
            transform-style: preserve-3d;
            perspective: 1000px;
        }

        .card:hover {
            transform: translateY(-25px) rotate(8deg) scale(1.1);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.25);
            z-index: 10;
            background: white url('https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/577a6ea9-ea1e-482d-89f8-ce780682f3fc.png') center/50px no-repeat;
            animation: wiggle 0.5s ease-in-out;
        }

        @keyframes wiggle {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(5deg); }
            75% { transform: rotate(-5deg); }
        }

        .card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            text-align: center;
            transition: transform 0.8s;
            transform-style: preserve-3d;
        }

        .card:hover .card-inner {
            transform: rotateY(180deg);
        }

        .card-front, .card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card-front {
            background-color: white;
        }

        .card-back {
            background-color: var(--dark);
            color: white;
            transform: rotateY(180deg);
        }

        .card-icon {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            transition: transform 0.3s ease;
            animation: floatIcon 2s ease-in-out infinite alternate;
        }

        @keyframes floatIcon {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-10px) rotate(5deg); }
        }

        .card:hover .card-icon {
            transform: scale(1.1);
        }

        .card h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 1rem;
            color: #666;
        }

        .card-back p {
            color: white;
        }

        .nav-btn {
            margin-top: 15px;
            padding: 12px 24px;
            background-color: var(--accent);
            color: white;
            border: none;
            border-radius: 30px;
            transform-style: preserve-3d;
            font-family: 'Comic Neue', cursive;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            display: inline-block;
        }

        .nav-btn:hover {
            background-color: var(--primary);
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        /* Specific card colors */
        .profile {
            border-top: 5px solid var(--primary);
        }

        .profile .card-back {
            background-color: var(--primary);
        }

        .announcements {
            border-top: 5px solid var(--secondary);
        }

        .announcements .card-back {
            background-color: var(--secondary);
        }

        .utilities {
            border-top: 5px solid var(--accent);
        }

        .utilities .card-back {
            background-color: var(--accent);
        }

        .infrastructure {
            border-top: 5px solid #9B5DE5;
        }

        .infrastructure .card-back {
            background-color: #9B5DE5;
        }

        .enrollments {
            border-top: 5px solid #43AA8B;
        }

        .enrollments .card-back {
            background-color: #43AA8B;
        }

        /* Floating animations */
        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .float {
            animation: float 3s ease-in-out infinite, rainbowPulse 10s linear infinite;
        }

        .delay-1 {
            animation-delay: 0.2s;
        }

        .delay-2 {
            animation-delay: 0.4s;
        }

        .delay-3 {
            animation-delay: 0.6s;
        }

        .delay-4 {
            animation-delay: 0.8s;
        }

        .delay-5 {
            animation-delay: 1s;
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.5) rotate(-5deg);
            }
            30% {
                opacity: 0.8;
                transform: scale(1.2) rotate(5deg);
            }
            50% {
                transform: scale(0.9) rotate(-3deg);
            }
            70% {
                transform: scale(1.1) rotate(2deg);
            }
            100% {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }

        @keyframes rainbowPulse {
            0% { box-shadow: 0 0 10px #FF9E7D; }
            20% { box-shadow: 0 0 15px #8BD3DD; }
            40% { box-shadow: 0 0 15px #F9C74F; }
            60% { box-shadow: 0 0 15px #9B5DE5; }
            80% { box-shadow: 0 0 15px #43AA8B; }
            100% { box-shadow: 0 0 10px #FF9E7D; }
        }

        .fade-in {
            animation: fadeIn 1s ease-in;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
        }

        img {
            width: 75px;
            position: relative;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <img src="moet.png" alt="Ministry of Education" class="header-image">
            <h1>Kindergarten Dashboard</h1>
            <marquee>
                <p>Welcome to the Kindergarten Dashboard! Here you can manage student profiles, view announcements, access utilities, and more.</p>
            </marquee>
            <button class="bg" onclick="window.location.href='logout.php'"><i class="fas fa-sign-out-alt mr-2"></i>Logout</button>
        </header>

        <div class="dashboard">
            <!-- Profile Card -->
            <div class="card profile float">
                <div class="card-inner">
                    <div class="card-front">
                        <div class="card-icon">
                            <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/199d990c-1194-47bf-ad1b-817f59d49834.png" alt="Smiling child's face with rainbow background" width="80" height="80" />
                        </div>
                        <h2>Profile</h2>
                        <p>View and edit student profiles</p>
                    </div>
                    <div class="card-back">
                        <h2>Your Profile</h2>
                        <p>Personal information, photos, and notes</p>
                        <a href="update_school.php" class="nav-btn">View Profile</a>
                    </div>
                </div>
            </div>

            <!-- Announcements Card -->
            <div class="card announcements float delay-1">
                <div class="card-inner">
                    <div class="card-front">
                        <div class="card-icon">
                            <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/fb475867-2600-44d3-9600-47028f744d1f.png" alt="Megaphone with rainbow colors and musical notes coming out" width="80" height="80" />
                        </div>
                        <h2>Announcements</h2>
                        <p>Latest news and updates</p>
                    </div>
                    <div class="card-back">
                        <h2>Announcements</h2>
                        <p>Important notices and events</p>
                        <a href="announcements.html" class="nav-btn">View Announcements</a>
                    </div>
                </div>
            </div>

            <!-- Utilities Card -->
            <div class="card utilities float delay-2">
                <div class="card-inner">
                    <div class="card-front">
                        <div class="card-icon">
                            <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/7808c31b-beaa-4d66-b194-79aef5656423.png" alt="Toolbox with child-friendly tools like crayons and safety scissors" width="80" height="80" />
                        </div>
                        <h2>Utilities</h2>
                        <p>Helpful tools and resources</p>
                    </div>
                    <div class="card-back">
                        <h2>Utilities</h2>
                        <p>Various helpful tools</p>
                        <a href="utilities.html" class="nav-btn">Use Utilities</a>
                    </div>
                </div>
            </div>

            <!-- Infrastructure Card -->
            <div class="card infrastructure float delay-3">
                <div class="card-inner">
                    <div class="card-front">
                        <div class="card-icon">
                            <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/11847214-e413-4daf-bd57-7ce154f0abf0.png" alt="Colorful kindergarten building with playground equipment and trees" width="80" height="80" />
                        </div>
                        <h2>Infrastructure</h2>
                        <p>Facilities information</p>
                    </div>
                    <div class="card-back">
                        <h2>Infrastructure</h2>
                        <p>Our facilities and resources</p>
                        <a href="infrastructure.html" class="nav-btn">View Facilities</a>
                    </div>
                </div>
            </div>

            <!-- Enrollments Card -->
            <div class="card enrollments float delay-4">
                <div class="card-inner">
                    <div class="card-front">
                        <div class="card-icon">
                            <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/7f973fd7-c9f9-4e91-8b53-a8bc1b4f4cd6.png" alt="Clipboard with colorful papers and a smiling pencil character" width="80" height="80" />
                        </div>
                        <h2>Enrollments</h2>
                        <p>Registration and admissions</p>
                    </div>
                    <div class="card-back">
                        <h2>Enrollments</h2>
                        <p>Admission process and forms</p>
                        <a href="kinder-enroll.php" class="nav-btn">Register Now</a>
                        <a href="viewkinder-enroll.php" class="nav-btn">View enrollments</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="successModal" class="modal hidden">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <p id="modalMessage"></p>
        <button onclick="closeModal()">OK</button>
    </div>
</div>

    <script>
        // Add click event to entire card for navigation
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Don't navigate if the click was on the button itself
                if(!e.target.classList.contains('nav-btn')) {
                    const link = this.querySelector('.nav-btn');
                    if(link) {
                        window.location.href = link.href;
                    }
                }
            });
        });

      
    function closeModal() {
        document.getElementById('successModal').style.display = 'none';
    }
    document.addEventListener('DOMContentLoaded', function() {
        // Check if there is a success message
        <?php if (isset($_SESSION['success_message'])): ?>
            document.getElementById('modalMessage').innerText = "<?php echo $_SESSION['success_message']; ?>";
            document.getElementById('successModal').style.display = 'flex';
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
    });

    </script>
</body>
</html>
