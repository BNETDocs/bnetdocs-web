<?php
/**
 * BNETDocs static export script.
 *
 * Connects to the BNETDocs MariaDB database, queries all published content,
 * and writes a tree of JSON files for use by the bnetdocs-static site.
 *
 * Usage:
 *   php bin/export.php --output-dir /path/to/bnetdocs-static/data
 */

declare(strict_types=1);

// Bootstrap: chdir to repo root so all relative paths work correctly.
chdir(dirname(__DIR__));

// Autoload (composer vendor-dir is 'lib', not 'vendor').
require 'lib/autoload.php';

use BNETDocs\Libraries\Core\Config;
use BNETDocs\Libraries\Db\MariaDb;

date_default_timezone_set('Etc/UTC');

// ---------------------------------------------------------------------------
// CLI argument parsing
// ---------------------------------------------------------------------------

function parse_args(array $argv): array
{
    $opts = [];
    $argc = count($argv);
    for ($i = 1; $i < $argc; $i++)
    {
        if ($argv[$i] === '--output-dir' && isset($argv[$i + 1]))
        {
            $opts['output-dir'] = $argv[++$i];
        }
        elseif (str_starts_with($argv[$i], '--output-dir='))
        {
            $opts['output-dir'] = substr($argv[$i], strlen('--output-dir='));
        }
    }
    return $opts;
}

$opts = parse_args($argv ?? []);

if (empty($opts['output-dir']))
{
    fwrite(STDERR, "Error: --output-dir is required.\n");
    fwrite(STDERR, "Usage: php bin/export.php --output-dir /path/to/output\n");
    exit(1);
}

$output_dir = rtrim($opts['output-dir'], '/');

if (!is_dir($output_dir))
{
    fwrite(STDERR, sprintf("Error: output directory does not exist: %s\n", $output_dir));
    exit(1);
}

// ---------------------------------------------------------------------------
// Config: try phoenix config, fall back to sample.
// ---------------------------------------------------------------------------

$phoenix_config = 'etc/config.phoenix.json';
$sample_config  = 'etc/config.sample.json';

if (file_exists($phoenix_config) && is_readable($phoenix_config))
{
    // Config::loadJson() uses the hardcoded JSON_CONFIG_PATH constant which
    // points at etc/config.phoenix.json relative to the class file location —
    // that resolves correctly now that we chdir'd to repo root above.
    Config::loadJson();
}
elseif (file_exists($sample_config) && is_readable($sample_config))
{
    // Manually load the sample config into the protected static property via
    // a one-off anonymous child class trick.
    // Instead, we temporarily copy sample to phoenix path in memory by
    // using reflection to set the protected static.
    $rf = new ReflectionClass(Config::class);
    $prop = $rf->getProperty('json_config');
    $prop->setAccessible(true);
    $json = file_get_contents($sample_config);
    $prop->setValue(null, json_decode($json, true, 512, JSON_OBJECT_AS_ARRAY | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR));
    echo "Warning: using etc/config.sample.json (no config.phoenix.json found).\n";
}
else
{
    fwrite(STDERR, "Error: no config file found (tried etc/config.phoenix.json and etc/config.sample.json).\n");
    exit(1);
}

// ---------------------------------------------------------------------------
// Database connection
// ---------------------------------------------------------------------------

$db = MariaDb::instance();

// ---------------------------------------------------------------------------
// Helper functions
// ---------------------------------------------------------------------------

/**
 * Convert a string to a URL slug.
 * Matches the logic of StringProcessor::sanitizeForUrl().
 */
function slugify(string $value): string
{
    $result = preg_replace('/[^\da-z]+/im', '-', $value);
    $result = trim($result, '-');
    return strtolower($result);
}

/**
 * Format a datetime string from MariaDB into ISO 8601 with UTC offset, or return null.
 */
function fmt_dt(?string $value): ?string
{
    if ($value === null || $value === '') return null;
    $dt = new DateTimeImmutable($value, new DateTimeZone('UTC'));
    return $dt->format(DateTimeInterface::ATOM);
}

/**
 * Render content through Parsedown if the markdown bitmask flag is set.
 * For non-markdown content, HTML-encode it (matching what the web app does).
 */
function render_content(string $content, int $options_bitmask, int $markdown_flag): string
{
    if (empty($content)) return '';
    if (($options_bitmask & $markdown_flag) === $markdown_flag)
    {
        $md = new Parsedown();
        $md->setBreaksEnabled(true);
        return $md->text($content);
    }
    return htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Extract the first paragraph of rendered HTML.
 * Looks for the first <p>...</p> block. If none is found, returns the entire string.
 */
function first_paragraph(string $html): string
{
    if (empty($html)) return '';
    if (preg_match('#<p>.*?</p>#si', $html, $m))
    {
        return $m[0];
    }
    // Fall back: return first non-empty line wrapped in a <p>.
    $first_line = trim(strtok($html, "\n"));
    return $first_line !== '' ? '<p>' . $first_line . '</p>' : '';
}

/**
 * Write JSON to a file, creating parent directories as needed.
 * Uses JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES.
 */
function write_json(string $path, mixed $data): void
{
    $dir = dirname($path);
    if (!is_dir($dir))
    {
        mkdir($dir, 0755, true);
    }
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n");
}

/**
 * Fetch the username for a given user_id. Returns 'unknown' if not found.
 * Results are cached to avoid repeated queries for the same user.
 */
$user_cache = [];
function get_username(?int $user_id): string
{
    global $db, $user_cache;
    if ($user_id === null) return 'unknown';
    if (array_key_exists($user_id, $user_cache)) return $user_cache[$user_id];

    $q = $db->prepare('SELECT `username` FROM `users` WHERE `id` = ? LIMIT 1;');
    if (!$q || !$q->execute([$user_id]) || $q->rowCount() === 0)
    {
        $user_cache[$user_id] = 'unknown';
    }
    else
    {
        $row = $q->fetchObject();
        $q->closeCursor();
        $user_cache[$user_id] = $row->username ?? 'unknown';
    }
    return $user_cache[$user_id];
}

/**
 * Fetch comments for a given parent type and parent id.
 * Comment content is always rendered through Parsedown (safe mode for user input).
 */
function get_comments(int $parent_type, int $parent_id): array
{
    global $db;

    $q = $db->prepare('
        SELECT
            `content`,
            `created_datetime`,
            `id`,
            `user_id`
        FROM `comments`
        WHERE `parent_type` = :pt AND `parent_id` = :pid
        ORDER BY `created_datetime` ASC, `id` ASC;
    ');
    if (!$q || !$q->execute([':pt' => $parent_type, ':pid' => $parent_id]))
    {
        return [];
    }

    $comments = [];
    while ($row = $q->fetchObject())
    {
        $md = new Parsedown();
        $md->setBreaksEnabled(true);
        $md->setSafeMode(true);
        $comments[] = [
            'id'               => (int) $row->id,
            'author_username'  => get_username(isset($row->user_id) ? (int) $row->user_id : null),
            'created_datetime' => fmt_dt($row->created_datetime),
            'content_html'     => $md->text($row->content),
        ];
    }
    $q->closeCursor();
    return $comments;
}

$generated_at = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DateTimeInterface::ATOM);

// Bitmask constants (same values across all content types):
const OPTION_MARKDOWN  = 0x00000001;
const OPTION_PUBLISHED = 0x00000002;

// Comment parent type constants:
const PARENT_TYPE_DOCUMENT  = 1;
const PARENT_TYPE_NEWS_POST = 2;
const PARENT_TYPE_PACKET    = 3;

// ---------------------------------------------------------------------------
// Products
// ---------------------------------------------------------------------------

echo "Exporting products...\n";

$q = $db->prepare('SELECT `bnet_product_id`, `label`, `bnet_product_raw`, `bnls_product_id`, `sort` FROM `products` ORDER BY `bnet_product_id` ASC;');
$q->execute();

$products = [];
while ($row = $q->fetchObject())
{
    // bnet_product_raw is BINARY(4); convert to 4-char ASCII string.
    $raw = $row->bnet_product_raw;
    // PDO returns binary columns as raw strings; convert to printable ASCII.
    $ascii = '';
    for ($i = 0; $i < strlen($raw); $i++)
    {
        $ascii .= chr(ord($raw[$i]));
    }

    $products[] = [
        'bnet_product_id'  => (int) $row->bnet_product_id,
        'label'            => $row->label,
        'bnet_product_raw' => $ascii,
        'bnls_product_id'  => (int) $row->bnls_product_id,
        'sort'             => (int) $row->sort,
    ];
}
$q->closeCursor();

echo "Writing products.json...\n";
write_json($output_dir . '/products.json', $products);

// ---------------------------------------------------------------------------
// Application layers (from static PHP array, not DB)
// ---------------------------------------------------------------------------

echo "Exporting application-layers...\n";

$app_layers = [];
foreach (\BNETDocs\Libraries\Packet\Application::getAllAsArray() as $id => $info)
{
    $app_layers[] = ['id' => $id, 'label' => $info[0], 'tag' => $info[1]];
}

echo "Writing application-layers.json...\n";
write_json($output_dir . '/application-layers.json', $app_layers);

// ---------------------------------------------------------------------------
// Transport layers (from static PHP array, not DB)
// ---------------------------------------------------------------------------

echo "Exporting transport-layers...\n";

$transport_layers = [];
foreach (\BNETDocs\Libraries\Packet\Transport::getAllAsArray() as $id => $info)
{
    $transport_layers[] = ['id' => $id, 'label' => $info[0], 'tag' => $info[1]];
}

echo "Writing transport-layers.json...\n";
write_json($output_dir . '/transport-layers.json', $transport_layers);

// ---------------------------------------------------------------------------
// News categories
// ---------------------------------------------------------------------------

echo "Exporting news-categories...\n";

$q = $db->prepare('SELECT `id`, `label`, `filename`, `sort_id` FROM `news_categories` ORDER BY `id` ASC;');
$q->execute();

$news_categories = [];
while ($row = $q->fetchObject())
{
    $news_categories[] = [
        'id'       => (int) $row->id,
        'label'    => $row->label,
        'filename' => $row->filename,
        'sort_id'  => (int) $row->sort_id,
    ];
}
$q->closeCursor();

echo "Writing news-categories.json...\n";
write_json($output_dir . '/news-categories.json', $news_categories);

// ---------------------------------------------------------------------------
// Packets
// ---------------------------------------------------------------------------

// Build a product lookup keyed by bnet_product_id for fast used_by queries.
// (We'll query packet_used_by per packet below.)

echo "Exporting packets...\n";

// Query all published packets.
$q = $db->prepare(sprintf('
    SELECT
        `created_datetime`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `packet_application_layer_id`,
        `packet_brief`,
        `packet_direction_id`,
        `packet_format`,
        `packet_id`,
        `packet_name`,
        `packet_remarks`,
        `packet_transport_layer_id`,
        `user_id`
    FROM `packets`
    WHERE (`options_bitmask` & %d) = %d
    ORDER BY `id` ASC;
', OPTION_PUBLISHED, OPTION_PUBLISHED));
$q->execute();
$packet_rows = $q->fetchAll(PDO::FETCH_OBJ);
$q->closeCursor();

$packet_count = count($packet_rows);
echo sprintf("Exporting %d packets...\n", $packet_count);

$packets_index = [];

foreach ($packet_rows as $row)
{
    $id       = (int) $row->id;
    $options  = (int) $row->options_bitmask;
    $name     = $row->packet_name;
    $remarks  = $row->packet_remarks ?? '';
    $brief    = $row->packet_brief ?? '';

    // Render brief through Parsedown if markdown flag is set.
    $brief_html = render_content($brief, $options, OPTION_MARKDOWN);

    // Extract first paragraph of remarks for the index entry.
    $remarks_html = render_content($remarks, $options, OPTION_MARKDOWN);
    $brief_html_for_index = !empty($brief_html) ? first_paragraph($brief_html) : first_paragraph($remarks_html);

    // used_by: array of bnet_product_id integers.
    $uq = $db->prepare('
        SELECT `u`.`bnet_product_id`
        FROM `packet_used_by` AS `u`
        INNER JOIN `products` AS `p` ON `u`.`bnet_product_id` = `p`.`bnet_product_id`
        WHERE `u`.`id` = ?
        ORDER BY `p`.`sort` ASC;
    ');
    $uq->execute([$id]);
    $used_by = [];
    while ($urow = $uq->fetchObject()) $used_by[] = (int) $urow->bnet_product_id;
    $uq->closeCursor();

    $uri = sprintf('/packet/%d/%s', $id, slugify($name));

    $index_entry = [
        'id'                          => $id,
        'packet_id'                   => (int) $row->packet_id,
        'packet_name'                 => $name,
        'packet_direction_id'         => (int) $row->packet_direction_id,
        'packet_application_layer_id' => (int) $row->packet_application_layer_id,
        'packet_transport_layer_id'   => (int) $row->packet_transport_layer_id,
        'options_bitmask'             => $options,
        'brief_html'                  => $brief_html_for_index,
        'used_by'                     => $used_by,
        'author_username'             => get_username(isset($row->user_id) ? (int) $row->user_id : null),
        'uri'                         => $uri,
        'created_datetime'            => fmt_dt($row->created_datetime),
        'edited_datetime'             => fmt_dt($row->edited_datetime),
    ];

    $packets_index[] = $index_entry;

    // Write per-packet detail file.
    $comments = get_comments(PARENT_TYPE_PACKET, $id);

    $detail = array_merge($index_entry, [
        'format'       => $row->packet_format ?? '',
        'remarks_html' => $remarks_html,
        'comments'     => $comments,
    ]);

    write_json(sprintf('%s/packets/%d.json', $output_dir, $id), $detail);
}

echo "Writing packets/index.json...\n";
write_json($output_dir . '/packets/index.json', [
    'generated_at' => $generated_at,
    'packets'      => $packets_index,
]);

// ---------------------------------------------------------------------------
// Documents
// ---------------------------------------------------------------------------

echo "Exporting documents...\n";

$q = $db->prepare(sprintf('
    SELECT
        `brief`,
        `content`,
        `created_datetime`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `title`,
        `user_id`
    FROM `documents`
    WHERE (`options_bitmask` & %d) = %d
    ORDER BY `id` ASC;
', OPTION_PUBLISHED, OPTION_PUBLISHED));
$q->execute();
$doc_rows = $q->fetchAll(PDO::FETCH_OBJ);
$q->closeCursor();

$doc_count = count($doc_rows);
echo sprintf("Exporting %d documents...\n", $doc_count);

$documents_index = [];

foreach ($doc_rows as $row)
{
    $id      = (int) $row->id;
    $options = (int) $row->options_bitmask;
    $title   = $row->title;
    $brief   = $row->brief ?? '';
    $content = $row->content ?? '';

    // Render brief and content through Parsedown if markdown flag is set.
    $brief_html   = render_content($brief, $options, OPTION_MARKDOWN);
    $content_html = render_content($content, $options, OPTION_MARKDOWN);

    // For the index, use brief_html if available; otherwise first paragraph of content.
    $brief_html_for_index = !empty($brief_html) ? first_paragraph($brief_html) : first_paragraph($content_html);

    $uri = sprintf('/document/%d/%s', $id, slugify($title));

    $index_entry = [
        'id'               => $id,
        'title'            => $title,
        'options_bitmask'  => $options,
        'brief_html'       => $brief_html_for_index,
        'author_username'  => get_username(isset($row->user_id) ? (int) $row->user_id : null),
        'uri'              => $uri,
        'created_datetime' => fmt_dt($row->created_datetime),
        'edited_datetime'  => fmt_dt($row->edited_datetime),
    ];

    $documents_index[] = $index_entry;

    // Write per-document detail file.
    $comments = get_comments(PARENT_TYPE_DOCUMENT, $id);

    $detail = array_merge($index_entry, [
        'content_html' => $content_html,
        'comments'     => $comments,
    ]);

    write_json(sprintf('%s/documents/%d.json', $output_dir, $id), $detail);
}

echo "Writing documents/index.json...\n";
write_json($output_dir . '/documents/index.json', [
    'generated_at' => $generated_at,
    'documents'    => $documents_index,
]);

// ---------------------------------------------------------------------------
// News
// ---------------------------------------------------------------------------

echo "Exporting news...\n";

$q = $db->prepare(sprintf('
    SELECT
        `category_id`,
        `content`,
        `created_datetime`,
        `edited_datetime`,
        `id`,
        `options_bitmask`,
        `title`,
        `user_id`
    FROM `news_posts`
    WHERE (`options_bitmask` & %d) = %d
    ORDER BY `id` ASC;
', OPTION_PUBLISHED, OPTION_PUBLISHED));
$q->execute();
$news_rows = $q->fetchAll(PDO::FETCH_OBJ);
$q->closeCursor();

$news_count = count($news_rows);
echo sprintf("Exporting %d news posts...\n", $news_count);

$news_index = [];

foreach ($news_rows as $row)
{
    $id      = (int) $row->id;
    $options = (int) $row->options_bitmask;
    $title   = $row->title;
    $content = $row->content ?? '';

    // Render content (markdown or HTML-encoded, per the web app's getContent(true) logic).
    if (($options & OPTION_MARKDOWN) === OPTION_MARKDOWN)
    {
        $md = new Parsedown();
        $md->setBreaksEnabled(true);
        $content_html = $md->text($content);
    }
    else
    {
        $content_html = filter_var($content, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }

    $brief_html_for_index = first_paragraph($content_html);
    $uri = sprintf('/news/%d/%s', $id, slugify($title));

    $index_entry = [
        'id'               => $id,
        'title'            => $title,
        'category_id'      => (int) $row->category_id,
        'options_bitmask'  => $options,
        'brief_html'       => $brief_html_for_index,
        'author_username'  => get_username(isset($row->user_id) ? (int) $row->user_id : null),
        'uri'              => $uri,
        'created_datetime' => fmt_dt($row->created_datetime),
        'edited_datetime'  => fmt_dt($row->edited_datetime),
    ];

    $news_index[] = $index_entry;

    // Write per-post detail file.
    $comments = get_comments(PARENT_TYPE_NEWS_POST, $id);

    $detail = array_merge($index_entry, [
        'content_html' => $content_html,
        'comments'     => $comments,
    ]);

    write_json(sprintf('%s/news/%d.json', $output_dir, $id), $detail);
}

echo "Writing news/index.json...\n";
write_json($output_dir . '/news/index.json', [
    'generated_at' => $generated_at,
    'news_posts'   => $news_index,
]);

// ---------------------------------------------------------------------------
// Done
// ---------------------------------------------------------------------------

echo "Export complete.\n";
exit(0);
