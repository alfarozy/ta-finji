<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finji - Catat Transaksi via WhatsApp</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #696cff;
            --primary-light: rgba(105, 108, 255, 0.16);
            --whatsapp-color: #25D366;
            --whatsapp-light: rgba(37, 211, 102, 0.1);
            --dark-color: #3a3b5a;
            --light-color: #f8f9fa;
            --gray-color: #6c757d;
            --white: #ffffff;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-color);
            color: var(--dark-color);
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        .logo i {
            font-size: 28px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--dark-color);
            font-weight: 500;
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .nav-buttons {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
        }

        .btn-outline {
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .btn-outline:hover {
            background-color: var(--primary-light);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
            border: 1px solid var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #5a5de0;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(105, 108, 255, 0.3);
        }

        .btn-whatsapp {
            background-color: var(--whatsapp-color);
            color: var(--white);
            border: 1px solid var(--whatsapp-color);
        }

        .btn-whatsapp:hover {
            background-color: #20b858;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        }

        .mobile-menu {
            display: none;
            font-size: 24px;
            cursor: pointer;
        }

        /* Hero Section */
        .hero {
            padding: 150px 0 100px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .hero-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 50px;
        }

        .hero-text {
            flex: 1;
        }

        .hero-image {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .hero-image img {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .whatsapp-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: var(--whatsapp-color);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3);
        }

        .hero h1 {
            font-size: 48px;
            line-height: 1.2;
            margin-bottom: 20px;
            color: var(--dark-color);
        }

        .hero h1 span {
            color: var(--primary-color);
        }

        .hero p {
            font-size: 18px;
            color: var(--gray-color);
            margin-bottom: 30px;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        /* WhatsApp Feature Section */
        .whatsapp-feature {
            padding: 100px 0;
            background-color: var(--white);
        }

        .whatsapp-content {
            display: flex;
            align-items: center;
            gap: 50px;
        }

        .whatsapp-demo {
            flex: 1;
            background-color: var(--whatsapp-light);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .whatsapp-chat {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .chat-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            margin-bottom: 15px;
        }

        .chat-avatar {
            width: 40px;
            height: 40px;
            background-color: var(--whatsapp-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .chat-info h4 {
            margin-bottom: 3px;
        }

        .chat-info p {
            font-size: 12px;
            color: var(--gray-color);
        }

        .chat-messages {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-height: 300px;
            overflow-y: auto;
        }

        .message {
            padding: 10px 15px;
            border-radius: 10px;
            max-width: 80%;
            font-size: 14px;
        }

        .message-in {
            background-color: #f1f1f1;
            align-self: flex-start;
        }

        .message-out {
            background-color: var(--whatsapp-light);
            align-self: flex-end;
            border: 1px solid rgba(37, 211, 102, 0.2);
        }

        .message-system {
            background-color: var(--primary-light);
            align-self: center;
            font-size: 12px;
            color: var(--primary-color);
        }

        .whatsapp-info {
            flex: 1;
        }

        .section-title {
            margin-bottom: 30px;
        }

        .section-title h2 {
            font-size: 36px;
            color: var(--dark-color);
            margin-bottom: 15px;
        }

        .section-title p {
            color: var(--gray-color);
        }

        .feature-list {
            list-style: none;
        }

        .feature-list li {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .feature-list i {
            color: var(--whatsapp-color);
            margin-top: 3px;
        }

        /* Steps Section */
        .steps-section {
            padding: 100px 0;
            background-color: var(--light-color);
        }

        .steps {
            display: flex;
            justify-content: space-between;
            gap: 30px;
            margin-top: 50px;
        }

        .step {
            flex: 1;
            text-align: center;
            padding: 30px 20px;
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .step:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .step-number {
            width: 50px;
            height: 50px;
            background-color: var(--primary-color);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 20px;
            font-weight: 700;
        }

        .step h3 {
            margin-bottom: 15px;
            color: var(--dark-color);
        }

        .step p {
            color: var(--gray-color);
        }

        /* CTA Section */
        .cta {
            padding: 100px 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, #5a5de0 100%);
            color: var(--white);
            text-align: center;
        }

        .cta h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }

        .cta p {
            max-width: 600px;
            margin: 0 auto 30px;
            font-size: 18px;
            opacity: 0.9;
        }

        .cta .btn {
            background-color: var(--white);
            color: var(--primary-color);
        }

        .cta .btn:hover {
            background-color: var(--light-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Footer */
        footer {
            background-color: var(--dark-color);
            color: var(--white);
            padding: 70px 0 30px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 50px;
        }

        .footer-column h3 {
            font-size: 18px;
            margin-bottom: 20px;
            color: var(--white);
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--white);
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            text-decoration: none;
            transition: var(--transition);
        }

        .social-links a:hover {
            background-color: var(--primary-color);
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {

            .hero-content,
            .whatsapp-content {
                flex-direction: column;
                text-align: center;
            }

            .hero-buttons {
                justify-content: center;
            }

            .steps {
                flex-direction: column;
            }
        }

        @media (max-width: 768px) {

            .nav-links,
            .nav-buttons {
                display: none;
            }

            .mobile-menu {
                display: block;
            }

            .hero h1 {
                font-size: 36px;
            }

            .section-title h2 {
                font-size: 30px;
            }
        }
    </style>
</head>

<body>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Catat Transaksi <span>Lebih Mudah</span> via WhatsApp</h1>
                    <p>Finji menghadirkan cara revolusioner mengelola keuangan. Cukup kirim pesan WhatsApp untuk
                        mencatat pemasukan dan pengeluaran Anda. Mudah, cepat, dan otomatis!</p>
                    <div class="hero-buttons">
                        <a href="#" class="btn btn-whatsapp">
                            <i class="fab fa-whatsapp"></i> Mulai dengan WhatsApp
                        </a>
                        <a href="#" class="btn btn-outline">Pelajari Lebih Lanjut</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</body>

</html>
