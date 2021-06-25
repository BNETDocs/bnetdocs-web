<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Templates;

/**
 * Adds CSS classes to Markdown output
 */
function MarkdownBootstrapFix(string $v)
{
  // Tables
  $v = str_replace('<table>', '<table class="table table-hover table-markdown table-striped">', $v);

  // Blockquotes
  $v = str_replace('<blockquote>', '<blockquote class="blockquote">', $v);

  // Images
  $v = str_replace('<img ', '<img class="img-fluid" ', $v);

  // Code
  $v = str_replace('<code>', '<code class="language-plaintext">', $v);

  // Code Blocks
  $v = str_replace('<pre><code', '<pre class="border border-primary overflow-auto pre-scrollable rounded bg-dark text-light"><code', $v);

  return $v;
}
