# souvikpati.in — Personal digital-services website

A complete, production-ready personal technology & digital-services website built with **plain PHP 8, MySQL/MariaDB, HTML5, CSS3 and vanilla JavaScript**. No Node.js, npm, Composer, Docker or build step required — upload the files, run the installer, and it works.

It includes a premium marketing site, a database-driven services + pricing system, a full blog CMS, a portfolio system, a secure admin panel, a dynamic SEO system (sitemap, robots, JSON-LD, Open Graph), a contact/inquiry system and a media library.

---

## 1. Requirements

- **PHP 8.0+** (tested on 8.4) with these extensions: `pdo_mysql`, `mbstring`, `fileinfo`, `json`, `dom`
- **MySQL 5.7+ / MariaDB 10.3+**
- **Apache** with `mod_rewrite` (standard on Hostinger & cPanel). `mod_headers`, `mod_deflate` and `mod_expires` are used when available but are optional.
- Ability to create a MySQL database and user (via Hostinger hPanel or cPanel)

No command line is required — everything can be done through your hosting file manager and the web installer.

---

## 2. Installation

### A. Create the database
1. In Hostinger hPanel / cPanel, create a **MySQL database** and a **database user**, and assign the user to the database with **all privileges**.
2. Note the **database host** (usually `localhost`), **name**, **user** and **password**.

### B. Upload the files
Upload the entire project into your site's document root (usually **`public_html`**). The project root — the folder containing `index.php` and `.htaccess` — must be the web root.

### C. Set folder permissions
Make these two folders writable so the installer can write the config and store uploads:

| Folder      | Permission |
|-------------|------------|
| `config/`   | `755` (or `775`) |
| `uploads/`  | `755` (or `775`) |

On most shared hosts `755` is enough because PHP runs as your user. If the installer reports a folder is not writable, set it to `775`.

### D. Run the installer
Open **`https://yourdomain.com/install/`** in a browser and complete the form:
1. Database host / name / user / password
2. Site URL (e.g. `https://souvikpati.in`) and site name
3. Your admin name, username, email and password (min 8 chars)

The installer will:
- Check server requirements
- Create all database tables (`database/database.sql`)
- Seed starter content — the 8 services with prices, starter FAQs and the Privacy / Terms / Disclaimer pages (**no fake projects, reviews or stats**)
- Create your admin account
- Write `config/config.php` (with a random app key)

### E. Remove the installer
After a successful install, click **“Remove installer & finish”**. This deletes the `/install` folder automatically. If it can't (permissions), **delete the `/install` folder manually** via your file manager. The installer refuses to run once `config/config.php` exists, but removing it is still recommended.

### F. Log in
Go to **`https://yourdomain.com/admin/login.php`** and sign in.

> **Manual install (optional):** import `database/database.sql` via phpMyAdmin, copy `config/config.sample.php` to `config/config.php` and fill in the values, then insert an admin row (with a `password_hash()`-generated hash). The installer is strongly recommended instead.

---

## 3. Configuration

All runtime configuration lives in **`config/config.php`** (created by the installer):

| Constant | Meaning |
|----------|---------|
| `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` | Database connection |
| `SITE_URL` | Absolute base URL, no trailing slash |
| `APP_KEY`  | Random secret (generated automatically) |
| `APP_ENV`  | `production` (hides errors) or `development` |

Everything else — site name, contact details, social links, SEO defaults, analytics, verification tags, currency, prices — is managed in the **admin panel**, not in code.

---

## 4. HTTPS setup

1. Enable a free SSL certificate for your domain (Hostinger and most cPanel hosts offer Let's Encrypt in one click).
2. HTTP→HTTPS redirection is already configured in `.htaccess`. If your SSL is not yet active, comment out the “Force HTTPS” block in `.htaccess` until it is.
3. Once HTTPS is confirmed working everywhere, you may enable **HSTS** by uncommenting the `Strict-Transport-Security` header in `.htaccess`.
4. **www vs non-www:** to force non-www, uncomment the “Canonical host” block in `.htaccess`.

---

## 5. Using the admin panel

Admin URL: **`/admin/`**

- **Dashboard** — real counts (posts, drafts, services, inquiries) and recent activity. No fabricated analytics.
- **Services** — add/edit services, prices, features, icon, image and per-service SEO.
- **Blog Posts** — full editor (headings, bold/italic, links, images, lists, quotes, code, tables, YouTube embeds), categories, tags, excerpt, author, reading time, featured image, scheduling and per-post SEO.
- **Categories / Tags** — organise the blog.
- **Portfolio** — add real projects (the public “Work” section stays hidden until you add one).
- **FAQs** — global (homepage) or per-service; FAQ structured data is generated only where the FAQs are actually shown.
- **Pages** — static/legal pages (Privacy, Terms, Disclaimer, or any custom page). Creating a page with the slug `about` overrides the default About page.
- **Media** — upload and manage images (JPG, PNG, WebP, GIF, SVG).
- **Inquiries** — read, mark replied, search and delete contact submissions.
- **Redirects** — 301 / 302 / 410 rules.
- **Settings** — identity, contact, socials, branding, currency.
- **SEO & Search** — default title/description/OG image, Google & Bing verification, Analytics / Tag Manager IDs, email notifications.
- **Admins** — manage admin accounts, update your profile and change your password.

### Changing prices
Prices are **never hardcoded**. Edit any service under **Services → Edit → Starting price**. Leave it blank to show “On request”. The currency symbol/code is set under **Settings → Currency**.

### Creating a blog post
**Blog Posts → New post** → write the title (the slug auto-fills) → write content in the editor → set category/tags/featured image → choose **Published** and a publish date. Set the date in the **future** to schedule the post (it stays hidden and out of the sitemap until then). Save as **Draft** to keep it private.

### How SEO fields work
Every service, post and page has an optional **SEO & social** section. Anything left blank falls back to sensible automatic defaults (the title becomes `Title — Site name`, the description is generated from the content, the OG image falls back to your default). So you only fill in what you want to override. `noindex` keeps a page out of search engines **and** the sitemap.

---

## 6. SEO, sitemap & Search Console

- **Sitemap:** `https://yourdomain.com/sitemap.xml` — generated dynamically from the homepage, services, service pages, blog, published posts, categories, projects and published pages. Drafts, scheduled-in-future posts and `noindex` content are excluded automatically.
- **robots.txt:** `https://yourdomain.com/robots.txt` — generated dynamically and references the sitemap.
- **Structured data (JSON-LD):** Person + WebSite site-wide, plus Service, BlogPosting, BreadcrumbList and FAQPage where appropriate. No fake ratings or organization data.
- **Open Graph & Twitter cards** on every page.
- **Canonical URLs, robots meta, clean URLs, breadcrumbs and image alt text** throughout.

### Configure Google Search Console
1. In Search Console, add your property (Domain or URL-prefix).
2. For the **HTML tag** method, copy the `content` value from the meta tag Google gives you and paste it into **Admin → SEO & Search → Google Search Console**. Save. The verification meta tag is then rendered on every page.
3. Verify in Search Console.
4. Under **Sitemaps**, submit `sitemap.xml`.
5. Do the same in **Bing Webmaster Tools** using the Bing field.

> Note: correct technical setup helps search engines crawl and index the site, but no tool can *guarantee* indexing — that is always Google's/Bing's decision.

---

## 7. Project structure

```
/ (public_html)
├── index.php            Front controller / router (clean URLs)
├── .htaccess            Rewrites, HTTPS, security headers, caching
├── config/
│   ├── config.php       Generated by installer (DB creds, site URL) — not committed
│   └── config.sample.php
├── includes/            Core: db, functions, auth, csrf, session, settings, seo, repository
├── templates/           Public header, footer, nav
├── pages/               Page handlers (home, services, blog, contact, sitemap, robots, errors…)
│   └── partials/        Reusable: post card, faq list, pagination
├── admin/               Secure admin panel + admin/includes (guard, layout, editor, seo-fields)
├── assets/
│   ├── css/             style.css (public), admin.css
│   ├── js/              main.js (public), admin.js
│   └── images/
├── uploads/             Media uploads (writable; PHP execution disabled here)
├── database/database.sql
└── install/             Web installer (delete after setup)
```

---

## 8. Writable permissions (summary)

| Path | Needs write? | Why |
|------|--------------|-----|
| `config/` | Yes, during install | To write `config.php` |
| `uploads/` (and subfolders) | Yes | To store media uploads |
| everything else | No | Read-only is fine (and safer) |

After install you can tighten `config/` back to `755` if you like; `config.php` itself is written as `0640`.

---

## 9. Security notes

Built-in protections: PDO prepared statements everywhere, CSRF tokens on all forms, output escaping (XSS), a whitelist HTML sanitiser for editor content, `password_hash()`/`password_verify()`, login throttling, secure session cookies (HttpOnly, SameSite, Secure on HTTPS), upload MIME/type/size validation with safe filenames, SVG sanitisation, PHP execution disabled inside `uploads/`, protected internal folders, and security headers via `.htaccess`.

Recommendations:
- Keep `APP_ENV=production` on the live site.
- Use a strong, unique admin password; create separate admin accounts per person.
- Keep PHP and your hosting up to date.
- Consider restricting `/admin/` by IP at the host level if you have a static IP.

---

## 10. Backups

- **Database:** export regularly from phpMyAdmin (Export → Quick → SQL), or use your host's automatic backups. This holds all content, settings, services, posts and inquiries.
- **Files:** back up the `uploads/` folder (your media) and `config/config.php`. The rest of the codebase is this repository.

---

## 11. Troubleshooting

- **Blank page / 500:** temporarily set `APP_ENV` to `development` in `config/config.php` to see the error, then set it back.
- **“Database connection failed”:** re-check the four DB constants in `config/config.php`.
- **Uploads fail:** ensure `uploads/` (and the year/month subfolders) are writable.
- **Clean URLs 404:** confirm `mod_rewrite` is enabled and `.htaccess` was uploaded (it's a hidden file — enable “show hidden files” in your file manager).
- **Emails not sending:** shared hosts often need an SMTP setup for PHP `mail()` to deliver; inquiries are always saved to the database regardless.

---

Built to be uploaded to `public_html`, configured once, and run as a real professional website.
