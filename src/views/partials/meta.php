<?php /** @var array $data */
$title       = $title ?? AFS_NAME . ' — ' . AFS_TAGLINE;
$description = $description ?? AFS_DESC;
$canonical   = $canonical ?? url(current_path());
?>
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:title"        content="<?= e($title) ?>">
<meta property="og:description"  content="<?= e($description) ?>">
<meta property="og:type"         content="website">
<meta property="og:url"          content="<?= e($canonical) ?>">
<meta property="og:site_name"    content="<?= e(AFS_NAME) ?>">
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e($description) ?>">
<meta name="theme-color"         content="#0A0A0A">
<meta name="csrf-token"          content="<?= e(Csrf::token()) ?>">
