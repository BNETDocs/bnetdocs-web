<?php

namespace BNETDocs\Tests\Libraries\Core;

use \BNETDocs\Libraries\Core\StringProcessor;
use \PHPUnit\Framework\TestCase;

class StringProcessorTest extends TestCase
{
    // fuzzyMatch

    public function testFuzzyMatchWildcardSuffix(): void
    {
        $this->assertTrue(StringProcessor::fuzzyMatch('api.*', 'api.enabled'));
    }

    public function testFuzzyMatchWildcardSuffixNoMatch(): void
    {
        $this->assertFalse(StringProcessor::fuzzyMatch('api.*', 'other.enabled'));
    }

    public function testFuzzyMatchExact(): void
    {
        $this->assertTrue(StringProcessor::fuzzyMatch('exact', 'exact'));
    }

    public function testFuzzyMatchCaseInsensitive(): void
    {
        $this->assertTrue(StringProcessor::fuzzyMatch('exact', 'EXACT'));
    }

    public function testFuzzyMatchWildcardMiddle(): void
    {
        $this->assertTrue(StringProcessor::fuzzyMatch('foo.*.bar', 'foo.baz.bar'));
    }

    // isBrowser

    public function testIsBrowserFirefox(): void
    {
        $this->assertTrue(StringProcessor::isBrowser('Mozilla/5.0 (Windows NT 10.0; rv:109.0) Gecko/20100101 Firefox/115.0'));
    }

    public function testIsBrowserCurl(): void
    {
        $this->assertFalse(StringProcessor::isBrowser('curl/7.88.1'));
    }

    public function testIsBrowserEmpty(): void
    {
        $this->assertFalse(StringProcessor::isBrowser(''));
    }

    // sanitizeForUrl

    public function testSanitizeForUrlBasic(): void
    {
        $this->assertSame('hello-world', StringProcessor::sanitizeForUrl('Hello World!'));
    }

    public function testSanitizeForUrlTrimsLeadingTrailingDashes(): void
    {
        $this->assertSame('hello', StringProcessor::sanitizeForUrl('  hello  '));
    }

    public function testSanitizeForUrlCollapsesMultipleSeparators(): void
    {
        $this->assertSame('foo-bar', StringProcessor::sanitizeForUrl('foo---bar'));
    }

    public function testSanitizeForUrlLowercaseFalse(): void
    {
        $this->assertSame('Hello-World', StringProcessor::sanitizeForUrl('Hello World', false));
    }

    public function testSanitizeForUrlArray(): void
    {
        $this->assertSame(['hello-world', 'foo-bar'], StringProcessor::sanitizeForUrl(['Hello World', 'Foo Bar']));
    }

    // stripExcessLines

    public function testStripExcessLinesCollapsesMultiple(): void
    {
        $this->assertSame("a\n\nb", StringProcessor::stripExcessLines("a\n\n\n\nb"));
    }

    public function testStripExcessLinesLeavesDoubleNewlineAlone(): void
    {
        $this->assertSame("a\n\nb", StringProcessor::stripExcessLines("a\n\nb"));
    }

    public function testStripExcessLinesSingleNewlineUnchanged(): void
    {
        $this->assertSame("a\nb", StringProcessor::stripExcessLines("a\nb"));
    }

    // stripLeftPattern

    public function testStripLeftPatternRemovesPrefix(): void
    {
        $this->assertSame('bar', StringProcessor::stripLeftPattern('foobar', 'foo'));
    }

    public function testStripLeftPatternNoMatchReturnsOriginal(): void
    {
        $this->assertSame('foobar', StringProcessor::stripLeftPattern('foobar', 'baz'));
    }

    public function testStripLeftPatternEmptyNeedle(): void
    {
        $this->assertSame('foobar', StringProcessor::stripLeftPattern('foobar', ''));
    }

    // stripToSnippet

    public function testStripToSnippetShortStringUnchanged(): void
    {
        $this->assertSame('short', StringProcessor::stripToSnippet('short', 100));
    }

    public function testStripToSnippetTruncatesAtWordBoundary(): void
    {
        $result = StringProcessor::stripToSnippet('Hello world foo', 8);
        $this->assertStringEndsWith('...', $result);
        $this->assertLessThanOrEqual(8, strlen($result));
    }
}
