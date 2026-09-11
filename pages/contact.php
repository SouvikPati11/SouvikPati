<?php
/** Contact / inquiry page — renders the form and processes submissions. */
if (!defined('BASE_PATH')) { exit; }

$siteName = setting('site_name', 'Souvik Pati');
$services = repo_active_services();
$errors = [];
$done = false;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    csrf_verify();

    // Honeypot: bots fill this hidden field.
    if (trim((string) post('website')) !== '') {
        // Silently accept to avoid tipping off bots.
        if (wants_json()) { json_response(['ok' => true, 'message' => 'Thanks — your message has been sent.']); }
        flash('success', 'Thanks — your message has been sent.');
        redirect(path('contact'));
    }

    // Simple rate limit: max 4 submissions per IP in 10 minutes.
    $ip = client_ip();
    $rl = db()->prepare('SELECT COUNT(*) FROM contact_inquiries WHERE ip_address = ? AND created_at > (NOW() - INTERVAL 10 MINUTE)');
    $rl->execute([$ip]);
    if ((int) $rl->fetchColumn() >= 4) {
        $msg = 'You have sent several messages recently. Please wait a few minutes before trying again.';
        if (wants_json()) { json_response(['ok' => false, 'error' => $msg], 429); }
        $errors['_'] = $msg;
    }

    $name    = mb_substr(post_str('name'), 0, 140);
    $email   = mb_substr(post_str('email'), 0, 190);
    $phone   = mb_substr(post_str('phone'), 0, 60);
    $service = mb_substr(post_str('service'), 0, 160);
    $budget  = mb_substr(post_str('budget'), 0, 80);
    $message = mb_substr(post_str('message'), 0, 5000);

    if (!$errors) {
        if ($name === '')                 { $errors['name'] = 'Please enter your name.'; }
        if ($email === '')                { $errors['email'] = 'Please enter your email.'; }
        elseif (!is_valid_email($email))  { $errors['email'] = 'Please enter a valid email address.'; }
        if (mb_strlen($message) < 10)     { $errors['message'] = 'Please add a little more detail (at least 10 characters).'; }
    }

    if (!$errors) {
        $stmt = db()->prepare(
            'INSERT INTO contact_inquiries (name, email, phone, service, budget, message, status, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$name, $email, $phone ?: null, $service ?: null, $budget ?: null, $message, 'new', $ip, client_user_agent()]);

        // Optional email notification to the owner.
        if (setting_bool('enable_email_notifications')) {
            $to = setting('contact_notify_email') ?: setting('email');
            if ($to && is_valid_email($to)) {
                $subject = 'New inquiry from ' . $name . ' — ' . $siteName;
                $body = "New contact inquiry\n\n"
                    . "Name: $name\nEmail: $email\nPhone: " . ($phone ?: '-') . "\n"
                    . "Service: " . ($service ?: '-') . "\nBudget: " . ($budget ?: '-') . "\n\n"
                    . "Message:\n$message\n\n---\nSent from " . setting('site_url');
                $headers = 'From: ' . $siteName . ' <no-reply@' . preg_replace('#^https?://(www\.)?#', '', setting('site_url')) . ">\r\n"
                    . 'Reply-To: ' . $email . "\r\n"
                    . "Content-Type: text/plain; charset=UTF-8\r\n";
                @mail($to, $subject, $body, $headers);
            }
        }

        $done = true;
        $successMsg = 'Thanks for reaching out — I\'ll get back to you personally, usually within a day or two.';
        if (wants_json()) { json_response(['ok' => true, 'message' => $successMsg]); }
        flash('success', $successMsg);
        redirect(path('contact'));
    }

    // Errors — respond to AJAX with field-level detail.
    if (wants_json()) {
        json_response(['ok' => false, 'error' => $errors['_'] ?? 'Please check the highlighted fields.', 'errors' => array_diff_key($errors, ['_' => 1])], 422);
    }
    flash_old(['name' => $name ?? '', 'email' => $email ?? '', 'phone' => $phone ?? '', 'service' => $service ?? '', 'budget' => $budget ?? '', 'message' => $message ?? '']);
}

$prefService = (string) get('service', '');

seo_set([
    'title'       => 'Contact & Project Inquiry',
    'description' => 'Start a project or ask a question. Tell me what you\'re building and I\'ll reply with honest feedback and a starting estimate.',
    'canonical'   => url('contact'),
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => url()],
        ['name' => 'Contact', 'url' => url('contact')],
    ],
]);

$active_nav = 'contact';
include BASE_PATH . '/templates/header.php';
?>
<section class="section-tight">
  <div class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="<?= attr(path()) ?>">Home</a><span class="sep">/</span><span>Contact</span></nav>
    <div class="split">
      <div>
        <span class="eyebrow">Contact</span>
        <h1>Let's build something.</h1>
        <p class="lead">Tell me about your project — even a rough idea is fine. I read every message personally and reply with honest feedback and a starting estimate.</p>

        <?php if (!empty($errors['_'])): ?><div class="flash flash-error"><?= e($errors['_']) ?></div><?php endif; ?>
        <div class="flash" id="formStatus" hidden></div>

        <form id="contactForm" method="post" action="<?= attr(path('contact')) ?>" novalidate>
          <?= csrf_field() ?>
          <div class="hp-field" aria-hidden="true">
            <label>Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
          </div>

          <div class="grid cols-2" style="gap:0 1rem;">
            <div class="field">
              <label for="c-name">Name</label>
              <input class="input" id="c-name" name="name" type="text" value="<?= attr((string) old('name')) ?>" required autocomplete="name">
              <?php if (!empty($errors['name'])): ?><div class="field-error"><?= e($errors['name']) ?></div><?php endif; ?>
            </div>
            <div class="field">
              <label for="c-email">Email</label>
              <input class="input" id="c-email" name="email" type="email" value="<?= attr((string) old('email')) ?>" required autocomplete="email">
              <?php if (!empty($errors['email'])): ?><div class="field-error"><?= e($errors['email']) ?></div><?php endif; ?>
            </div>
          </div>

          <div class="grid cols-2" style="gap:0 1rem;">
            <div class="field">
              <label for="c-phone">Phone / WhatsApp <span class="hint">(optional)</span></label>
              <input class="input" id="c-phone" name="phone" type="text" value="<?= attr((string) old('phone')) ?>" autocomplete="tel">
            </div>
            <div class="field">
              <label for="c-service">Service <span class="hint">(optional)</span></label>
              <select class="select" id="c-service" name="service">
                <option value="">Select a service…</option>
                <?php
                $sel = old('service') ?: $prefService;
                foreach ($services as $svc):
                  $isSel = ($sel === $svc['title']);
                ?>
                  <option value="<?= attr($svc['title']) ?>"<?= $isSel ? ' selected' : '' ?>><?= e($svc['title']) ?></option>
                <?php endforeach; ?>
                <option value="Other"<?= ($sel === 'Other') ? ' selected' : '' ?>>Something else</option>
              </select>
            </div>
          </div>

          <div class="field">
            <label for="c-budget">Budget range <span class="hint">(optional)</span></label>
            <select class="select" id="c-budget" name="budget">
              <?php
              $budgets = ['', 'Under ₹15,000', '₹15,000 – ₹50,000', '₹50,000 – ₹1,50,000', '₹1,50,000+', 'Not sure yet'];
              $selB = old('budget');
              foreach ($budgets as $b): ?>
                <option value="<?= attr($b) ?>"<?= ($selB === $b && $b !== '') ? ' selected' : '' ?>><?= $b === '' ? 'Select a range…' : e($b) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label for="c-message">Project details</label>
            <textarea class="textarea" id="c-message" name="message" required placeholder="What are you looking to build? Share goals, timeline and anything else that helps."><?= e((string) old('message')) ?></textarea>
            <?php if (!empty($errors['message'])): ?><div class="field-error"><?= e($errors['message']) ?></div><?php endif; ?>
          </div>

          <button type="submit" class="btn btn-lg btn-primary btn-block">Send message</button>
          <p class="muted" style="font-size:.82rem;margin-top:.8rem;">Your details are only used to reply to your inquiry. See the <a href="<?= attr(path('privacy-policy')) ?>" style="color:var(--accent);">privacy policy</a>.</p>
        </form>
        <?php clear_old(); ?>
      </div>

      <aside>
        <div class="card aside-card">
          <h3 style="font-size:1.05rem;">Other ways to reach me</h3>
          <ul class="mt-2">
            <?php if ($em = setting('email')): ?><li style="padding:.6rem 0;border-top:1px solid var(--border-soft);"><strong>Email</strong><br><a href="mailto:<?= attr($em) ?>" style="color:var(--accent);"><?= e($em) ?></a></li><?php endif; ?>
            <?php if ($wa = setting('whatsapp')): ?><li style="padding:.6rem 0;border-top:1px solid var(--border-soft);"><strong>WhatsApp</strong><br><a href="https://wa.me/<?= attr(preg_replace('/[^0-9]/', '', $wa)) ?>" target="_blank" rel="noopener" style="color:var(--accent);">Message me</a></li><?php endif; ?>
            <?php if ($tg = setting('telegram')): ?><li style="padding:.6rem 0;border-top:1px solid var(--border-soft);"><strong>Telegram</strong><br><a href="https://t.me/<?= attr(ltrim($tg, '@')) ?>" target="_blank" rel="noopener" style="color:var(--accent);">@<?= e(ltrim($tg, '@')) ?></a></li><?php endif; ?>
            <li style="padding:.6rem 0;border-top:1px solid var(--border-soft);"><strong>Response time</strong><br><span class="muted">Usually within 1–2 business days</span></li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</section>
<?php include BASE_PATH . '/templates/footer.php'; ?>
