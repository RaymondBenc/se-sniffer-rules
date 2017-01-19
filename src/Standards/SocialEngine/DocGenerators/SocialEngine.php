<?php

class PHP_CodeSniffer_DocGenerators_SocialEngine extends PHP_CodeSniffer_DocGenerators_Generator
{
  /**
   * Generates the documentation for a standard.
   *
   * @see    processSniff()
   */
  public function generate()
  {
    ob_start();
    $this->printHeader();

    $standardFiles = $this->getStandardFiles();

    foreach( $standardFiles as $standard ) {
      $doc = new DOMDocument();
      $doc->load($standard);
      $documentation = $doc->getElementsByTagName('documentation')->item(0);
      $this->processSniff($documentation);
    }

    $this->printFooter();
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
  }


  /**
   * Print the markdown header.
   *
   * @return void
   */
  protected function printHeader()
  {
    $standard = $this->getStandard();

    echo "# $standard Coding Standard" . PHP_EOL;

  }


  /**
   * Print the markdown footer.
   *
   * @return void
   */
  protected function printFooter()
  {
    error_reporting(0);
    echo 'Documentation generated on ' . date('r');
  }


  /**
   * Process the documentation for a single sniff.
   *
   * @param DOMNode $doc The DOMNode object for the sniff.
   *                     It represents the "documentation" tag in the XML
   *                     standard file.
   *
   * @return void
   */
  protected function processSniff(DOMNode $doc)
  {
    $title = $this->getTitle($doc);
    echo "## $title\n\n";

    foreach( $doc->childNodes as $node ) {
      if( $node->nodeName === 'standard' ) {
        $this->printTextBlock($node);
      } elseif( $node->nodeName === 'code_comparison' ) {
        $this->printCodeComparisonBlock($node);
      }
    }
  }

  /**
   * Print a text block found in a standard.
   *
   * @param DOMNode $node The DOMNode object for the text block.
   *
   * @return void
   */
  protected function printTextBlock(DOMNode $node)
  {
    $content = trim($node->nodeValue);
    $content = htmlspecialchars($content);

    $content = str_replace('&lt;em&gt;', '*', $content);
    $content = str_replace('&lt;/em&gt;', '*', $content);

    echo $content . "\n\n";

  }

  /**
   * Print a code comparison block found in a standard.
   *
   * @param DOMNode $node The DOMNode object for the code comparison block.
   *
   * @return void
   */
  protected function printCodeComparisonBlock(DOMNode $node)
  {
    $codeBlocks = $node->getElementsByTagName('code');

    $firstTitle = $codeBlocks->item(0)->getAttribute('title');
    $first      = trim($codeBlocks->item(0)->nodeValue);
    $first      = str_replace('<em>', '', $first);
    $first      = str_replace('</em>', '', $first);

    $secondTitle = $codeBlocks->item(1)->getAttribute('title');
    $second      = trim($codeBlocks->item(1)->nodeValue);
    $second      = str_replace('<em>', '', $second);
    $second      = str_replace('</em>', '', $second);

    $parts = explode(':', $firstTitle);

    echo "####" . trim($parts[1]) . "\n";
    echo '  <table>'.PHP_EOL;
    echo '   <tr>'.PHP_EOL;
    echo "    <th><font color='green'>Valid</font></th>".PHP_EOL;
    echo "    <th><font color='red'>Invalid</font></th>".PHP_EOL;
    echo '   </tr>'.PHP_EOL;
    echo '   <tr>'.PHP_EOL;
    echo '<td>'.PHP_EOL;
    echo "```php\n";
    echo $first . "\n";
    echo "```\n";
    echo '</td>'.PHP_EOL;
    echo '<td>'.PHP_EOL.PHP_EOL;
    echo "```php\n";
    echo $second . "\n";
    echo "```\n";
    echo '</td>'.PHP_EOL;
    echo '   </tr>'.PHP_EOL;
    echo "  </table>\n";
  }
}
