<?php /* vim: set colorcolumn=: */
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

  // Code Blocks
  $v = str_replace('<pre><code>', '<pre class="border border-primary overflow-auto p-2 pre-scrollable rounded text-light"><code>', $v);

  return $v;
}
