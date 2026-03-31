<?php
include_once("db_conn.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>Fuwa..NG | World-Class Digital Services</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Primary Stylesheet -->
    <link rel="stylesheet" href="vtusite/css/modern-ui.css?v=<?= time() ?>">

    <style>
        /* Emergency Layout Reset */
        body { margin: 0; padding: 0; background-color: #020617; color: #f8fafc; font-family: 'Outfit', sans-serif; }
        .container { width: 100%; max-width: 1300px; margin: 0 auto; padding: 0 2rem; box-sizing: border-box; }
        .section { padding: 120px 0; width: 100%; box-sizing: border-box; }
        .hero { min-height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; }
        .services-grid, .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px; width: 100%; }
        img { max-width: 100%; height: auto; }
        * { box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="bg-mesh"></div>
    <div class="bg-glow blob-1"></div>
    <div class="bg-glow blob-2"></div>

    <nav class="navbar" id="navbar">
        <div class="container nav-container">
            <a href="index.php" class="logo">
                <i class="fa-solid fa-bolt"></i> Fuwa<span>..NG</span>
            </a>
            <div class="nav-links">
                <a href="#services">Services</a>
                <a href="#pricing">Pricing</a>
                <a href="#contact">Support</a>
                <?php if(!isset($_COOKIE['email'])): ?>
                    <a href="user_register.php" class="btn btn-outline nav-btn">Create Account</a>
                    <a href="user_login.php" class="btn btn-primary nav-btn">Login Portal</a>
                <?php else: ?>
                    <a href="welcome_pin.php" class="btn btn-primary nav-btn">Access Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <header class="hero">
        <div class="container">
            <div class="hero-content fade-up">
                <span class="badge">Next-Generation Infrastructure</span>
                <h1>Access Essential <br><span class="gradient-text">Digital Services</span></h1>
                <p>Verify identities, manage agency banking, and process telecom transactions with the most reliable and secure platform built for modern operations.</p>
                <div class="hero-actions">
                    <a href="#services" class="btn btn-primary btn-lg">Explore Our Services</a>
                    <a href="user_register.php" class="btn btn-outline btn-lg">Start for Free</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Services Section -->
    <section id="services" class="section">
        <div class="container">
            <div class="section-header fade-up">
                <span class="badge">Core Ecosystem</span>
                <h2>Unified <span class="gradient-text">Solutions</span></h2>
                <p>Everything you need to power your digital transactions, unified in one secure and fast infrastructure.</p>
            </div>

            <div class="services-grid">
                <!-- BVN Verification -->
                <div class="service-card fade-up" style="animation-delay: 0.1s">
                    <div class="card-icon bvn-icon"><i class="fa-solid fa-building-columns"></i></div>
                    <h3>BVN Verification</h3>
                    <p>Instant biometric matching and financial identity validation with bank-grade security protocols.</p>
                    <a href="verify_bvn.php" class="card-link">Launch Module <i class="fa-solid fa-arrow-right"></i></a>
                </div>

                <!-- NIN Verification -->
                <div class="service-card fade-up" style="animation-delay: 0.2s">
                    <div class="card-icon nin-icon"><i class="fa-regular fa-id-card"></i></div>
                    <h3>NIN Verification</h3>
                    <p>Real-time national identity linkage and validation. Compliant, secure, and blazing fast.</p>
                    <a href="verify_nin.php" class="card-link">Launch Module <i class="fa-solid fa-arrow-right"></i></a>
                </div>

                <!-- VTU Services -->
                <div class="service-card fade-up" style="animation-delay: 0.3s">
                    <div class="card-icon vtu-icon"><i class="fa-solid fa-mobile-screen-button"></i></div>
                    <h3>VTU Services</h3>
                    <p>Automated airtime, data bundles, and utility bill payments across all major network providers.</p>
                    <a href="user_register.php" class="card-link">Launch Module <i class="fa-solid fa-arrow-right"></i></a>
                </div>

                <!-- Notary Services -->
                <div class="service-card fade-up" style="animation-delay: 0.4s">
                    <div class="card-icon" style="background: rgba(139, 92, 246, 0.1); color: #a855f7;"><i class="fa-solid fa-file-signature"></i></div>
                    <h3>Notary Services</h3>
                    <p>Digitally connect with certified legal professionals for document authentication & affidavits.</p>
                    <a href="user_register.php" class="card-link">Launch Module <i class="fa-solid fa-arrow-right"></i></a>
                </div>

                <!-- Ticketing & Transport NIRs -->
                <div class="service-card fade-up" style="animation-delay: 0.5s">
                    <div class="card-icon" style="background: rgba(236, 72, 153, 0.1); color: #ec4899;"><i class="fa-solid fa-bus"></i></div>
                    <h3>Ticketing & Transport NIRs</h3>
                    <p>Book intra-city buses, inter-state travels, and flight tickets with comprehensive route data.</p>
                    <a href="user_register.php" class="card-link">Launch Module <i class="fa-solid fa-arrow-right"></i></a>
                </div>

                <!-- Verified Auctions -->
                <div class="service-card fade-up" style="animation-delay: 0.6s">
                    <div class="card-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;"><i class="fa-solid fa-gavel"></i></div>
                    <h3>Verified Auctions</h3>
                    <p>Participate in secure online bidding for vehicles, real estate, and government agency assets.</p>
                    <a href="user_register.php" class="card-link">Launch Module <i class="fa-solid fa-arrow-right"></i></a>
                </div>

                <!-- Post Office & Logistics -->
                <div class="service-card fade-up" style="animation-delay: 0.7s">
                    <div class="card-icon" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;"><i class="fa-solid fa-truck-fast"></i></div>
                    <h3>Post Office & Logistics</h3>
                    <p>Track packages, estimate shipping costs, and schedule nationwide deliveries from your desk.</p>
                    <a href="user_register.php" class="card-link">Launch Module <i class="fa-solid fa-arrow-right"></i></a>
                </div>

                <!-- Virtual Cards -->
                <div class="service-card fade-up" style="animation-delay: 0.8s">
                    <div class="card-icon" style="background: rgba(6, 182, 212, 0.1); color: #06b6d4;"><i class="fa-solid fa-credit-card"></i></div>
                    <h3>Virtual Cards</h3>
                    <p>Instant USD & NGN virtual Visa/Mastercard cards. Shop globally on Netflix, AWS, Amazon and more with full spend controls.</p>
                    <a href="user_register.php" class="card-link">Launch Module <i class="fa-solid fa-arrow-right"></i></a>
                </div>

                <!-- Invoicing & Subscriptions -->
                <div class="service-card fade-up" style="animation-delay: 0.9s">
                    <div class="card-icon" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                    <h3>Invoicing & Subscriptions</h3>
                    <p>Automate recurring billing, generate branded invoices, and manage subscriptions with payment links and smart reminders.</p>
                    <a href="user_register.php" class="card-link">Launch Module <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <section id="pricing" class="section">
        <div class="container">
            <div class="section-header fade-up">
                <span class="badge">Pricing Plans</span>
                <h2>Competitive <span class="gradient-text">Rates</span></h2>
                <p>Unlock premium access with our affordable data and verification plans.</p>
            </div>
            <div class="pricing-grid">
                <?php
                $networks = [
                    ['name' => 'MTN', 'icon' => 'vtusite/images/mtn.png'],
                    ['name' => 'GLO', 'icon' => 'vtusite/images/glo_icon.jpg'],
                    ['name' => 'AIRTEL', 'icon' => 'vtusite/images/airtel_icon.jpg']
                ];
                foreach ($networks as $index => $net): ?>
                    <div class="pricing-card fade-up" style="animation-delay: <?= $index * 0.1 ?>s">
                        <div class="title">
                            <img src="<?= $net['icon'] ?>" alt="<?= $net['name'] ?>">
                            <?= $net['name'] ?> Bundles
                        </div>
                        <?php
                        $sql = "SELECT * FROM price_list WHERE network = '{$net['name']}' LIMIT 6";
                        $result = ($conn !== null) ? mysqli_query($conn, $sql) : false;
                        if ($result && mysqli_num_rows($result) > 0): ?>
                            <table>
                                <thead><tr><th>Plan</th><th>Price</th><th>Validity</th></tr></thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?= $row['data_plan'] ?></td>
                                            <td>₦<?= $row['amount'] ?></td>
                                            <td><?= $row['validate'] ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p style="padding: 40px 0; text-align: center; color: #64748b;">Live pricing currently unavailable.</p>
                        <?php endif; ?>
                        <a href="user_register.php" class="btn btn-primary" style="width:100%; margin-top:20px;">Get Started</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container footer-grid">
            <div class="footer-about">
                <a href="index.php" class="logo"><i class="fa-solid fa-bolt"></i> Fuwa<span>..NG</span></a>
                <p style="margin-top: 24px;">The premium infrastructure for digital services in Nigeria.</p>
                <div class="social-links">
                    <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#"><i class="fa-brands fa-whatsapp"></i></a>
                    <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
                </div>
            </div>
            <div>
                <h4>Navigation</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                </ul>
            </div>
            <div>
                <h4>Legal</h4>
                <ul class="footer-links">
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                </ul>
            </div>
            <div>
                <h4>Support</h4>
                <ul class="footer-links">
                    <li><a href="mailto:support@futureverify.com">Email Support</a></li>
                    <li><a href="#">System Status</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <div class="container">
                <p>&copy; 2026 Fuwa..NG. Built for the next generation of fintech.</p>
            </div>
        </div>
    </footer>

    <script>
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
