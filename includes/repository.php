<?php
/**
 * Read helpers for public-facing content. Keeps queries in one place.
 */

declare(strict_types=1);

/** All active services ordered for display. */
function repo_active_services(): array
{
    $stmt = db()->query(
        "SELECT * FROM services WHERE status = 'active' ORDER BY sort_order ASC, id ASC"
    );
    return $stmt->fetchAll();
}

/** A single active service by slug. */
function repo_service_by_slug(string $slug): ?array
{
    $stmt = db()->prepare("SELECT * FROM services WHERE slug = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

/** Feature bullets for a service. */
function repo_service_features(int $serviceId): array
{
    $stmt = db()->prepare('SELECT feature FROM service_features WHERE service_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$serviceId]);
    return array_column($stmt->fetchAll(), 'feature');
}

/** FAQs for a service (or global FAQs when $serviceId is null). */
function repo_faqs(?int $serviceId = null): array
{
    if ($serviceId === null) {
        $stmt = db()->query('SELECT * FROM faqs WHERE is_active = 1 AND service_id IS NULL ORDER BY sort_order ASC, id ASC');
        return $stmt->fetchAll();
    }
    $stmt = db()->prepare('SELECT * FROM faqs WHERE is_active = 1 AND service_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$serviceId]);
    return $stmt->fetchAll();
}

/** Featured or recent portfolio projects. */
function repo_projects(bool $featuredOnly = false, int $limit = 12): array
{
    $sql = "SELECT * FROM portfolio_projects WHERE status = 'active'";
    if ($featuredOnly) {
        $sql .= ' AND is_featured = 1';
    }
    $sql .= ' ORDER BY sort_order ASC, created_at DESC LIMIT ' . (int) $limit;
    return db()->query($sql)->fetchAll();
}

function repo_project_by_slug(string $slug): ?array
{
    $stmt = db()->prepare("SELECT * FROM portfolio_projects WHERE slug = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function repo_has_projects(): bool
{
    return (int) db()->query("SELECT COUNT(*) FROM portfolio_projects WHERE status = 'active'")->fetchColumn() > 0;
}

/**
 * Published, publicly visible posts. Excludes drafts, scheduled-in-future and
 * anything without a published date in the past.
 */
function repo_published_posts_query(): string
{
    return "status = 'published' AND published_at IS NOT NULL AND published_at <= NOW()";
}

function repo_recent_posts(int $limit = 3): array
{
    $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM blog_posts p
            LEFT JOIN blog_categories c ON c.id = p.category_id
            WHERE ' . repo_published_posts_query() . '
            ORDER BY p.published_at DESC LIMIT ' . (int) $limit;
    return db()->query($sql)->fetchAll();
}

function repo_post_by_slug(string $slug): ?array
{
    $stmt = db()->prepare(
        'SELECT p.*, c.name AS category_name, c.slug AS category_slug, a.name AS admin_name
         FROM blog_posts p
         LEFT JOIN blog_categories c ON c.id = p.category_id
         LEFT JOIN admins a ON a.id = p.author_id
         WHERE p.slug = ? AND ' . repo_published_posts_query() . ' LIMIT 1'
    );
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function repo_post_tags(int $postId): array
{
    $stmt = db()->prepare(
        'SELECT t.name, t.slug FROM blog_tags t
         JOIN post_tags pt ON pt.tag_id = t.id
         WHERE pt.post_id = ? ORDER BY t.name ASC'
    );
    $stmt->execute([$postId]);
    return $stmt->fetchAll();
}

function repo_related_posts(int $postId, ?int $categoryId, int $limit = 3): array
{
    if ($categoryId) {
        $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
                FROM blog_posts p LEFT JOIN blog_categories c ON c.id = p.category_id
                WHERE ' . repo_published_posts_query() . '
                AND p.category_id = ? AND p.id <> ?
                ORDER BY p.published_at DESC LIMIT ' . (int) $limit;
        $stmt = db()->prepare($sql);
        $stmt->execute([$categoryId, $postId]);
        $rows = $stmt->fetchAll();
        if ($rows) {
            return $rows;
        }
    }
    // Fallback: most recent other posts.
    $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM blog_posts p LEFT JOIN blog_categories c ON c.id = p.category_id
            WHERE ' . repo_published_posts_query() . ' AND p.id <> ?
            ORDER BY p.published_at DESC LIMIT ' . (int) $limit;
    $stmt = db()->prepare($sql);
    $stmt->execute([$postId]);
    return $stmt->fetchAll();
}

function repo_categories_with_counts(): array
{
    $sql = 'SELECT c.*, COUNT(p.id) AS post_count
            FROM blog_categories c
            LEFT JOIN blog_posts p ON p.category_id = c.id AND ' . repo_published_posts_query() . '
            GROUP BY c.id HAVING post_count > 0 ORDER BY c.name ASC';
    return db()->query($sql)->fetchAll();
}

function repo_page_by_slug(string $slug): ?array
{
    $stmt = db()->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'published' LIMIT 1");
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

/** Dashboard-style counts (also used on some public pages). */
function repo_counts(): array
{
    $pdo = db();
    return [
        'services'        => (int) $pdo->query("SELECT COUNT(*) FROM services WHERE status='active'")->fetchColumn(),
        'posts_total'     => (int) $pdo->query('SELECT COUNT(*) FROM blog_posts')->fetchColumn(),
        'posts_published' => (int) $pdo->query('SELECT COUNT(*) FROM blog_posts WHERE ' . repo_published_posts_query())->fetchColumn(),
        'posts_draft'     => (int) $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status='draft'")->fetchColumn(),
        'projects'        => (int) $pdo->query("SELECT COUNT(*) FROM portfolio_projects WHERE status='active'")->fetchColumn(),
        'inquiries'       => (int) $pdo->query('SELECT COUNT(*) FROM contact_inquiries')->fetchColumn(),
        'inquiries_new'   => (int) $pdo->query("SELECT COUNT(*) FROM contact_inquiries WHERE status='new'")->fetchColumn(),
    ];
}
