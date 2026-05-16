<?php
/**
 * ai-disclosure — single source of truth for the "made with AI" mark.
 *
 * The line operators can flip globally with AFS_AI_DISCLOSURE_VISIBLE.
 * Default: visible. We mark AI-generated content honestly because it's
 * the right thing to do and because regulation will eventually require
 * it (NDPA + EU AI Act). Better to ship the pattern now.
 *
 * Inputs:
 *   string $provider  Provider name (e.g. 'pollinations', 'hackclub', 'cache')
 *   string $note      Optional extra clause, e.g. "Edited by the studio."
 */
if (!defined('AFS_AI_DISCLOSURE_VISIBLE') || !AFS_AI_DISCLOSURE_VISIBLE) return;
$provider = (string)($provider ?? '');
$note     = (string)($note     ?? '');
?>
<p class="ai-disclosure" role="note">
  <span class="ai-disclosure__dot" aria-hidden="true"></span>
  Drafted with AI<?php if ($provider): ?> · <?= e($provider) ?><?php endif; ?><?php if ($note): ?> · <?= e($note) ?><?php endif; ?>.
</p>
