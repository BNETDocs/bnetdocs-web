<?php /* vim: set colorcolumn=: */
namespace BNETDocs\Templates;

/**
 * Adds CSS classes to Markdown output
 */
function MarkdownBootstrapFix(string $v)
{
  $v = str_replace('<table>', '<table class="table table-hover table-markdown table-striped">', $v);
  $v = str_replace('<blockquote>', '<blockquote class="blockquote">', $v);
  $v = str_replace('<img ', '<img class="img-fluid" ', $v);

  return $v;
}
