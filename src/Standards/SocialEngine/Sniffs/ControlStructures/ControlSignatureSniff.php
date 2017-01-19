<?php

class SocialEngine_Sniffs_ControlStructures_ControlSignatureSniff extends PHP_CodeSniffer_Standards_AbstractPatternSniff
{
  /**
   * If true, comments will be ignored if they are found in the code.
   *
   * @var boolean
   */
  public $ignoreComments = true;


  /**
   * Returns the patterns that this test wishes to verify.
   *
   * @return string[]
   */
  protected function getPatterns()
  {
    return array(
      'do {EOL...} while(...);EOL',
      'try {EOL...} catch(...) {EOL...}',
      'while(...) {EOL',
      'for(...) {EOL',
      'if(...) {EOL',
      'foreach(...) {EOL',
      '} else if(...) {EOL',
      '} elseif(...) {EOL',
      '} else {EOL',
      'do {EOL',
    );

  }//end getPatterns()
}//end class
