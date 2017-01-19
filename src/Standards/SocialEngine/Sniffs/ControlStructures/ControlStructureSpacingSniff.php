<?php

class SocialEngine_Sniffs_ControlStructures_ControlStructureSpacingSniff extends PSR2_Sniffs_ControlStructures_ControlStructureSpacingSniff
{
  /**
   * How many spaces should follow the opening bracket.
   *
   * @var int
   */
  public $requiredSpacesAfterOpen = 1;

  /**
   * How many spaces should precede the closing bracket.
   *
   * @var int
   */
  public $requiredSpacesBeforeClose = 1;
}//end class
