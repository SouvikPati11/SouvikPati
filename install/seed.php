<?php
/**
 * Seeds sensible starter content on install.
 *
 * IMPORTANT: This intentionally seeds ONLY real, non-fabricated content —
 * services, FAQs, legal pages and settings. It never creates fake projects,
 * testimonials, reviews, stats or client logos.
 */
declare(strict_types=1);

function install_seed(PDO $pdo, array $opts): void
{
    $siteName = $opts['site_name'] ?? 'Souvik Pati';
    $siteUrl  = $opts['site_url'] ?? '';
    $email    = $opts['admin_email'] ?? '';

    // ---- Settings ---------------------------------------------------------
    $settings = [
        'site_name'   => $siteName,
        'site_url'    => $siteUrl,
        'tagline'     => 'Digital products, websites & apps built for real-world use.',
        'short_bio'   => 'I design and build fast, reliable websites, web applications, Telegram bots and Android apps — from first idea to production launch.',
        'email'       => $email,
        'location'    => 'India',
        'currency_symbol' => '₹',
        'currency_code'   => 'INR',
        'default_seo_title' => $siteName . ' — Websites, Apps & Digital Products',
        'default_meta_description' => 'Independent developer building websites, e-commerce stores, Telegram bots, Android apps, custom web applications and automation. Clear scope, transparent pricing.',
        'contact_notify_email' => $email,
        'enable_email_notifications' => '0',
    ];
    $setStmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    foreach ($settings as $k => $v) { $setStmt->execute([$k, $v]); }

    // ---- Services ---------------------------------------------------------
    $services = [
        [
            'title' => 'Website Design & Development', 'slug' => 'website-development', 'icon' => 'code',
            'short' => 'Fast, modern, responsive websites designed to convert and easy to maintain.',
            'price' => 14999.00, 'note' => 'For a typical multi-page business or personal site.',
            'features' => ['Custom, responsive design', 'SEO-ready structure & clean URLs', 'Content management where needed', 'Performance & Core Web Vitals tuning', 'Contact forms & integrations'],
            'full' => '<p>A website that looks the part and works everywhere. I design and build responsive, fast-loading sites tailored to your goals — whether that\'s a marketing site, a personal brand or a landing page that converts.</p><p>Every build is structured for search engines and easy to maintain, with clean code you own outright.</p>',
        ],
        [
            'title' => 'E-commerce Website', 'slug' => 'ecommerce-website', 'icon' => 'cart',
            'short' => 'Online stores with secure checkout, product management and payment integration.',
            'price' => 29999.00, 'note' => 'Scales with your catalogue and payment needs.',
            'features' => ['Product & inventory management', 'Secure checkout & payment gateways', 'Order management dashboard', 'Mobile-first shopping experience', 'SEO for product & category pages'],
            'full' => '<p>Sell online with a store built around your products and your customers. From catalogue and cart to secure checkout and order management, I build e-commerce experiences that are quick, trustworthy and simple to run.</p>',
        ],
        [
            'title' => 'Telegram Bot', 'slug' => 'telegram-bot', 'icon' => 'bot',
            'short' => 'Custom Telegram bots for automation, support, notifications and business workflows.',
            'price' => 7999.00, 'note' => 'Depends on commands, integrations and hosting.',
            'features' => ['Custom commands & conversation flows', 'Payments & subscriptions (where supported)', 'Database-backed logic', 'Admin controls & broadcasting', 'Webhook or long-polling setup'],
            'full' => '<p>Automate the repetitive parts of your business with a Telegram bot built around your workflow — support, notifications, orders, content delivery or internal tooling. Reliable, database-backed and easy to extend.</p>',
        ],
        [
            'title' => 'Telegram Mini App', 'slug' => 'telegram-mini-app', 'icon' => 'app',
            'short' => 'Web apps that run right inside Telegram, tightly integrated with your bot.',
            'price' => 19999.00, 'note' => 'Based on features and backend complexity.',
            'features' => ['Native-feeling in-Telegram UI', 'Bot & backend integration', 'Payments & user data handling', 'Responsive, themed to Telegram', 'Secure API layer'],
            'full' => '<p>Give your users a rich, app-like experience without leaving Telegram. Mini Apps combine a modern web interface with your bot and backend — ideal for stores, dashboards, bookings and interactive tools.</p>',
        ],
        [
            'title' => 'Android App Development', 'slug' => 'android-app', 'icon' => 'app',
            'short' => 'Practical Android apps connected to your backend and APIs.',
            'price' => 34999.00, 'note' => 'Scope-based; includes core features & release build.',
            'features' => ['Clean, modern Android UI', 'API & backend integration', 'Push notifications', 'Offline-friendly where useful', 'Play Store release guidance'],
            'full' => '<p>Android apps built to do a job well — connected to your data, easy to use and ready for the Play Store. I focus on the features that matter and keep the app maintainable as it grows.</p>',
        ],
        [
            'title' => 'Custom Web Application', 'slug' => 'custom-web-application', 'icon' => 'grid',
            'short' => 'Bespoke web apps and internal tools built exactly around your process.',
            'price' => 39999.00, 'note' => 'Every application is scoped and quoted individually.',
            'features' => ['Tailored to your exact workflow', 'Secure authentication & roles', 'Database design & reporting', 'Third-party integrations', 'Built to scale & maintain'],
            'full' => '<p>When off-the-shelf software doesn\'t fit, a custom web application can. I build tools around the way you actually work — portals, booking systems, internal tools, SaaS MVPs and more — with security and maintainability built in.</p>',
        ],
        [
            'title' => 'Admin Panel / Dashboard', 'slug' => 'admin-panel', 'icon' => 'dashboard',
            'short' => 'Clean dashboards to manage content, data and operations in one place.',
            'price' => 24999.00, 'note' => 'Depends on modules and data complexity.',
            'features' => ['Role-based access control', 'CRUD for your data models', 'Charts & useful reporting', 'Search, filters & exports', 'Responsive on desktop & mobile'],
            'full' => '<p>A well-designed admin panel saves hours every week. I build clean, secure dashboards that make managing your content, customers and operations straightforward — with the reports and controls you actually need.</p>',
        ],
        [
            'title' => 'API Integration & Automation', 'slug' => 'api-integration-automation', 'icon' => 'automation',
            'short' => 'Connect your tools and automate manual work with reliable integrations.',
            'price' => 9999.00, 'note' => 'Priced by the systems and workflows involved.',
            'features' => ['Payment, messaging & CRM APIs', 'Webhooks & scheduled jobs', 'Data sync between systems', 'Custom automation scripts', 'Error handling & logging'],
            'full' => '<p>Stop copying data between tools by hand. I connect the services you already use — payments, messaging, CRMs, spreadsheets and more — and automate the workflows that eat your time, with sensible error handling so things don\'t break silently.</p>',
        ],
    ];

    $svcStmt = $pdo->prepare('INSERT INTO services (title, slug, icon, short_description, full_description, starting_price, price_note, status, sort_order) VALUES (?,?,?,?,?,?,?,?,?)');
    $featStmt = $pdo->prepare('INSERT INTO service_features (service_id, feature, sort_order) VALUES (?,?,?)');
    $order = 0;
    foreach ($services as $s) {
        $svcStmt->execute([$s['title'], $s['slug'], $s['icon'], $s['short'], $s['full'], $s['price'], $s['note'], 'active', $order++]);
        $sid = (int) $pdo->lastInsertId();
        $fo = 0;
        foreach ($s['features'] as $f) { $featStmt->execute([$sid, $f, $fo++]); }
    }

    // ---- Global FAQs ------------------------------------------------------
    $faqs = [
        ['How do you price projects?', 'Each service has a transparent starting price. The final quote depends on your exact scope, which we agree together before any work begins — no surprises.'],
        ['How long does a project take?', 'It depends on scope. A simple website might take a week or two; a custom application takes longer. You\'ll get a realistic timeline before we start.'],
        ['Do I own the code and content?', 'Yes. Everything I build for you is yours, with a clean handover and documentation where it helps.'],
        ['Do you offer ongoing support?', 'Yes — ongoing maintenance and support are available if you\'d like them, but they\'re never mandatory.'],
        ['How do we get started?', 'Send a message through the contact form with a rough idea of what you need. I\'ll reply with honest feedback, a suggested approach and a starting estimate.'],
    ];
    $faqStmt = $pdo->prepare('INSERT INTO faqs (question, answer, is_active, sort_order) VALUES (?,?,1,?)');
    $fo = 0;
    foreach ($faqs as $f) { $faqStmt->execute([$f[0], $f[1], $fo++]); }

    // ---- Legal pages ------------------------------------------------------
    $pageStmt = $pdo->prepare('INSERT INTO pages (title, slug, content, status, is_system) VALUES (?,?,?,?,1)');

    $privacy = '<p><em>Last updated on installation. Please review and edit this to reflect your actual practices.</em></p>'
        . '<h2>Overview</h2><p>This Privacy Policy explains how ' . htmlspecialchars($siteName) . ' ("we", "I") handles information collected through this website.</p>'
        . '<h2>Information collected</h2><p>When you submit the contact form, we collect the name, email, and any other details you choose to provide, solely to respond to your inquiry. Standard server logs (such as IP address and browser type) may be recorded for security and diagnostics.</p>'
        . '<h2>How information is used</h2><p>Your information is used only to reply to your message and provide the services you request. It is not sold or shared with third parties for marketing.</p>'
        . '<h2>Cookies & analytics</h2><p>This site uses a session cookie for basic functionality. If analytics are enabled, aggregate, non-identifying usage data may be collected to improve the site.</p>'
        . '<h2>Your rights</h2><p>You may request access to, correction of, or deletion of the personal information you have shared by contacting us.</p>'
        . '<h2>Contact</h2><p>Questions about this policy can be sent through the <a href="/contact">contact page</a>.</p>';

    $terms = '<p><em>Last updated on installation. Please review and edit these terms for your situation.</em></p>'
        . '<h2>Use of this website</h2><p>By using this website you agree to use it lawfully and not to misuse its content or functionality.</p>'
        . '<h2>Services</h2><p>Prices shown are starting points. Every project is quoted individually based on agreed scope. A quote and scope will be confirmed before work begins.</p>'
        . '<h2>Intellectual property</h2><p>Content on this site is owned by ' . htmlspecialchars($siteName) . ' unless stated otherwise. Work delivered to clients is transferred as agreed in the project terms.</p>'
        . '<h2>Limitation of liability</h2><p>This website is provided "as is". We are not liable for any indirect or consequential loss arising from its use.</p>'
        . '<h2>Contact</h2><p>For any questions about these terms, please use the <a href="/contact">contact page</a>.</p>';

    $disclaimer = '<p><em>Last updated on installation. Edit as appropriate.</em></p>'
        . '<h2>General</h2><p>The information on this website is provided in good faith and for general information only. It does not constitute professional advice for your specific situation.</p>'
        . '<h2>External links</h2><p>This site may link to external websites that are not under our control. We are not responsible for the content or practices of those sites.</p>'
        . '<h2>Estimates</h2><p>Any prices, timelines or estimates shown are indicative and subject to a confirmed quote.</p>';

    $pageStmt->execute(['Privacy Policy', 'privacy-policy', $privacy, 'published']);
    $pageStmt->execute(['Terms & Conditions', 'terms', $terms, 'published']);
    $pageStmt->execute(['Disclaimer', 'disclaimer', $disclaimer, 'published']);
}
