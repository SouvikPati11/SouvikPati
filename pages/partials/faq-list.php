<?php
/**
 * Renders an accordion FAQ list and its FAQPage JSON-LD.
 * Expects $faqs (array of rows with question/answer).
 */
if (!defined('BASE_PATH')) { exit; }
if (empty($faqs)) { return; }

$faqSchema = [];
foreach ($faqs as $i => $faq):
?>
<div class="faq-item<?= $i === 0 ? '' : '' ?>">
  <button class="faq-q" aria-expanded="false">
    <span><?= e($faq['question']) ?></span>
    <span class="faq-ic" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></span>
  </button>
  <div class="faq-a">
    <div class="faq-a-inner"><?= nl2br(e($faq['answer'])) ?></div>
  </div>
</div>
<?php
  $faqSchema[] = [
    '@type' => 'Question',
    'name'  => $faq['question'],
    'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($faq['answer'])],
  ];
endforeach;

// This partial renders inside <body>, after the head has been emitted, so we
// output the FAQPage structured data inline here. It reflects exactly the FAQ
// content shown on the page.
echo '<script type="application/ld+json">'
    . json_encode([
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $faqSchema,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    . "</script>\n";
