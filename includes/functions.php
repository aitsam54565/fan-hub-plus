<?php
require_once __DIR__ . '/auth.php';

function e(mixed $value): string { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function input(string $key, string $default = '', ?array $source = null): string {
    $value = ($source ?? $_POST)[$key] ?? $default;
    return is_scalar($value) ? trim((string)$value) : $default;
}
function get(string $key, string $default = ''): string { return input($key, $default, $_GET); }
function raw_input(string $key): string { return isset($_POST[$key]) && is_string($_POST[$key]) ? $_POST[$key] : ''; }
function flash(string $type, string $message): void { $_SESSION['flash'][] = [$type, $message]; }
function get_flashes(): array { $messages = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $messages; }
function redirect(string $url): never { header('Location: ' . $url); exit; }
function slugify(string $text): string { return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($text)), '-'); }
function category_id(string $slug): ?int {
    $row = db_one('SELECT category_id FROM categories WHERE slug=?', [$slug]);
    return $row ? (int)$row['category_id'] : null;
}
function categories(): array { return db_all('SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order,name'); }
function category_url(string $slug): string {
    $map = ['tv-shows'=>'tvshows','k-pop'=>'kpop'];
    return base_url(($map[$slug] ?? $slug) . '.php');
}
function log_activity(?int $userId, string $action, string $entity, ?int $entityId = null): void {
    db_query('INSERT INTO activity_logs(user_id,action,entity,entity_id) VALUES(?,?,?,?)', [$userId,$action,$entity,$entityId]);
}
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">'; }
function fail(int $status, string $message): never {
    http_response_code($status);
    if (str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/')) {
        header('Content-Type: application/json'); echo json_encode(['success'=>false,'message'=>$message]);
    } else { echo e($message); }
    exit;
}
function safe_return(string $default = ''): string {
    $next = input('next', get('next'));
    if ($next && str_starts_with($next, base_url()) && !str_starts_with($next, '//') && !preg_match('/[\x00-\x20\\\\]/', $next)) return $next;
    return $default ?: base_url('User/dashboard.php');
}
function safe_url(?string $url, string $fallback = ''): string {
    $url = trim((string)$url);
    if ($url === '' || preg_match('/[\x00-\x20\\\\<>"\']/', $url)) return $fallback;
    if (preg_match('~^https?://~i', $url)) {
        return filter_var($url, FILTER_VALIDATE_URL) && !parse_url($url, PHP_URL_USER) ? $url : $fallback;
    }
    if (str_starts_with($url, '//') || str_contains($url, '..') || str_contains($url, ':')) return $fallback;
    if (str_starts_with($url, '/')) return str_starts_with($url, base_url()) ? $url : $fallback;
    return preg_match('~^[a-zA-Z0-9_./?=&%+#-]+$~', $url) ? base_url($url) : $fallback;
}
function image_url(?string $url): string {
    $url = preg_replace('~^(\.\./)+~', '', str_replace('\\', '/', (string)$url));
    if ($url && !preg_match('~^https?://~i', $url) && !is_file(APP_ROOT . '/' . ltrim($url, '/'))) return base_url('assets/images/placeholder.svg');
    return safe_url($url, base_url('assets/images/placeholder.svg'));
}
function upload_image(string $field, string $folder): ?string {
    $file = $_FILES[$field] ?? null;
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024) throw new InvalidArgumentException('Choose an image smaller than 5 MB.');
    $info = @getimagesize($file['tmp_name']);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $types = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if (!$info || !isset($types[$mime]) || $info[0] * $info[1] > 24000000) throw new InvalidArgumentException('Use a valid JPG, PNG or WebP image, up to 24 megapixels.');
    $directory = APP_ROOT . '/assets/uploads/' . $folder;
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) throw new RuntimeException('The upload folder is not writable.');
    $path = 'assets/uploads/' . $folder . '/' . bin2hex(random_bytes(16)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], APP_ROOT . '/' . $path)) throw new RuntimeException('The image could not be saved.');
    return $path;
}
function valid_date(string $date): bool {
    $parsed = DateTime::createFromFormat('!Y-m-d', $date);
    return $parsed && $parsed->format('Y-m-d') === $date;
}
function settings(): array {
    static $values;
    return $values ??= array_column(db_all('SELECT setting_key,setting_value FROM site_settings'), 'setting_value', 'setting_key');
}
function excerpt(?string $text, int $length = 130): string { return mb_strimwidth(strip_tags((string)$text), 0, $length, '…'); }
function rich_text(string $html): string {
    $document = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors(); libxml_use_internal_errors($previous);
    $allowed = ['div','p','br','strong','b','em','i','u','h2','h3','ul','ol','li','blockquote','a','img'];
    $clean = function (DOMNode $node) use (&$clean, $allowed): string {
        if ($node instanceof DOMText) return e($node->textContent);
        if (!$node instanceof DOMElement) return '';
        $tag = strtolower($node->tagName);
        if (!in_array($tag, $allowed, true)) return '';
        $children = ''; foreach ($node->childNodes as $child) $children .= $clean($child);
        if ($tag === 'div') return $children;
        if ($tag === 'a') { $url=safe_url($node->getAttribute('href')); return $url ? '<a href="'.e($url).'" rel="noopener noreferrer">'.$children.'</a>' : $children; }
        if ($tag === 'img') { $url=safe_url($node->getAttribute('src')); return $url ? '<img loading="lazy" src="'.e($url).'" alt="'.e($node->getAttribute('alt')).'">' : ''; }
        return '<'.$tag.'>'.$children.($tag==='br' ? '' : '</'.$tag.'>');
    };
    return $document->documentElement ? $clean($document->documentElement) : '';
}
function render_media(string $type, ?string $url): void {
    $url = safe_url($url);
    if (!$url) return;
    $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
    $path = parse_url($url, PHP_URL_PATH) ?? '';
    $embed = '';
    if (in_array($host, ['www.youtube.com','youtube.com','youtu.be','www.youtube-nocookie.com'], true)) {
        parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);
        $video = $host === 'youtu.be' ? trim($path,'/') : ($query['v'] ?? basename($path));
        if (is_string($video) && preg_match('/^[A-Za-z0-9_-]{11}$/', $video)) $embed = 'https://www.youtube-nocookie.com/embed/'.$video;
    } elseif (in_array($host, ['vimeo.com','www.vimeo.com'], true) && ctype_digit(basename($path))) $embed='https://player.vimeo.com/video/'.basename($path);
    if ($embed) echo '<iframe class="video" loading="lazy" src="'.e($embed).'" title="Media player" allow="fullscreen; picture-in-picture" allowfullscreen></iframe>';
    elseif (in_array($type, ['audio','podcast'],true)) echo '<audio controls preload="none" src="'.e($url).'">Your browser cannot play this audio.</audio>';
    elseif (in_array($type,['video','trailer'],true) && preg_match('/\.(mp4|webm|ogg)$/i',$path)) echo '<video controls preload="metadata" src="'.e($url).'">Your browser cannot play this video.</video>';
    elseif ($type==='image') echo '<a href="'.e($url).'" target="_blank" rel="noopener"><img class="detail-image" loading="lazy" src="'.e($url).'" alt="Media image"></a>';
    else echo '<a class="button" href="'.e($url).'" target="_blank" rel="noopener noreferrer">Open resource</a>';
}
function not_found(): never {
    http_response_code(404); $page_title='Page not found'; require __DIR__.'/header.php';
    echo '<main id="main" class="page"><h1>Page not found</h1><p>This item is unavailable or is no longer published.</p><a class="button" href="'.e(base_url('search.php')).'">Explore content</a></main>';
    require __DIR__.'/footer.php'; exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $token = input('csrf', $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!$token || !hash_equals(csrf_token(), $token)) fail(403, 'Your form expired. Refresh the page and try again.');
}
