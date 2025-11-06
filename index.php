<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InLET - Innovation in Language & Educational Technology</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #10b981;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--dark);
        }

        /* Header & Navigation */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        /* Hero Section */
        .hero {
            margin-top: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8rem 2rem 6rem;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="white" stroke-width="0.5" opacity="0.1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
        }

        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            animation: fadeInUp 0.8s ease;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            animation: fadeInUp 0.8s ease 0.2s backwards;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            animation: fadeInUp 0.8s ease 0.4s backwards;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn-primary {
            background: white;
            color: var(--primary);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .btn-secondary {
            background: transparent;
            border: 2px solid white;
            color: white;
        }

        .btn-secondary:hover {
            background: white;
            color: var(--primary);
        }

        /* Stats Section */
        .stats {
            background: var(--light);
            padding: 4rem 2rem;
        }

        .stats-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            text-align: center;
        }

        .stat-item {
            padding: 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }

        .stat-item:hover {
            transform: translateY(-10px);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--gray);
            font-size: 1.1rem;
        }

        /* Features Section */
        .features {
            padding: 6rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .section-title p {
            color: var(--gray);
            font-size: 1.1rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 3rem;
        }

        .feature-card {
            padding: 2.5rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: white;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .feature-card p {
            color: var(--gray);
            line-height: 1.8;
        }

        /* Research Areas */
        .research {
            background: var(--light);
            padding: 6rem 2rem;
        }

        .research-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .research-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .research-item {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            border-left: 4px solid var(--primary);
            transition: all 0.3s;
        }

        .research-item:hover {
            border-left-width: 8px;
            transform: translateX(5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .research-item h4 {
            font-size: 1.3rem;
            margin-bottom: 0.8rem;
            color: var(--primary);
        }

        /* Team Section */
        .team {
            padding: 6rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
        }

        .team-card {
            text-align: center;
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }

        .team-card:hover {
            transform: translateY(-10px);
        }

        .team-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            font-weight: 700;
        }

        .team-card h4 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .team-card p {
            color: var(--gray);
            margin-bottom: 1rem;
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 4rem 2rem 2rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1.5rem;
            color: white;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.8rem;
        }

        .footer-section a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-section a:hover {
            color: var(--secondary);
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 0 auto;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            color: rgba(255,255,255,0.7);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .cta-buttons {
                flex-direction: column;
            }

            .section-title h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <nav>
            <a href="#" class="logo">🚀 InLET</a>
            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#research">Research</a></li>
                <li><a href="#team">Team</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Innovation in Language & Educational Technology</h1>
            <p>Transforming language education through cutting-edge research and technology</p>
            <div class="cta-buttons">
                <a href="#research" class="btn btn-primary">Explore Research</a>
                <a href="#contact" class="btn btn-secondary">Get Involved</a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">50+</div>
                <div class="stat-label">Research Projects</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">15+</div>
                <div class="stat-label">Expert Researchers</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">100+</div>
                <div class="stat-label">Publications</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">20+</div>
                <div class="stat-label">Collaborations</div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="about">
        <div class="section-title">
            <h2>What We Do</h2>
            <p>Leading innovation in language learning technologies</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🎓</div>
                <h3>Educational Innovation</h3>
                <p>Developing cutting-edge methodologies that integrate technology for optimal educational outcomes in language learning.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🤖</div>
                <h3>AI-Powered Learning</h3>
                <p>Leveraging artificial intelligence to create adaptive and personalized language learning experiences.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3>Mobile Solutions</h3>
                <p>Building innovative mobile applications for on-the-go language learning with interactive features.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Learning Analytics</h3>
                <p>Utilizing data analytics to measure and improve language learning effectiveness and student performance.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🌐</div>
                <h3>Digital Assessment</h3>
                <p>Creating comprehensive digital tools to evaluate language proficiency and track learning progress.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎮</div>
                <h3>Gamification</h3>
                <p>Implementing game-based learning strategies to enhance engagement and motivation in language education.</p>
            </div>
        </div>
    </section>

    <!-- Research Areas -->
    <section class="research" id="research">
        <div class="research-container">
            <div class="section-title">
                <h2>Research Focus Areas</h2>
                <p>Pioneering research in language and educational technology</p>
            </div>
            <div class="research-grid">
                <div class="research-item">
                    <h4>Computer-Assisted Language Learning (CALL)</h4>
                    <p>Exploring technology integration in language instruction and learning environments.</p>
                </div>
                <div class="research-item">
                    <h4>Natural Language Processing</h4>
                    <p>Applying NLP techniques to enhance language learning tools and assessment systems.</p>
                </div>
                <div class="research-item">
                    <h4>Virtual Reality in Education</h4>
                    <p>Creating immersive VR environments for authentic language learning experiences.</p>
                </div>
                <div class="research-item">
                    <h4>Adaptive Learning Systems</h4>
                    <p>Developing intelligent systems that adapt to individual learner needs and progress.</p>
                </div>
                <div class="research-item">
                    <h4>Educational Data Mining</h4>
                    <p>Analyzing learning patterns to optimize educational strategies and outcomes.</p>
                </div>
                <div class="research-item">
                    <h4>Mobile-Assisted Language Learning</h4>
                    <p>Researching effective mobile learning approaches and application design.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team" id="team">
        <div class="section-title">
            <h2>Our Research Team</h2>
            <p>Meet the experts driving innovation</p>
        </div>
        <div class="team-grid">
            <div class="team-card">
                <div class="team-avatar">DR</div>
                <h4>Dr. Research Lead</h4>
                <p>Principal Investigator</p>
                <p>Specializing in AI and Language Education</p>
            </div>
            <div class="team-card">
                <div class="team-avatar">PM</div>
                <h4>Prof. Mobile Expert</h4>
                <p>Senior Researcher</p>
                <p>Mobile Learning Technologies</p>
            </div>
            <div class="team-card">
                <div class="team-avatar">DA</div>
                <h4>Dr. Analytics Pro</h4>
                <p>Data Scientist</p>
                <p>Learning Analytics & Assessment</p>
            </div>
            <div class="team-card">
                <div class="team-avatar">VR</div>
                <h4>Dr. VR Specialist</h4>
                <p>Tech Innovator</p>
                <p>Immersive Learning Environments</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>InLET</h3>
                <p>Innovation in Language and Educational Technology</p>
                <p>Advancing language education through research and innovation.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#research">Research</a></li>
                    <li><a href="#team">Team</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Research Areas</h3>
                <ul>
                    <li><a href="#">AI in Education</a></li>
                    <li><a href="#">Mobile Learning</a></li>
                    <li><a href="#">Learning Analytics</a></li>
                    <li><a href="#">Virtual Reality</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <ul>
                    <li>Email: info@inlet.edu</li>
                    <li>Phone: +62 XXX XXX XXX</li>
                    <li>Address: Malang, East Java</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 InLET - Innovation in Language & Educational Technology. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>