<?php

	/**
	 * Elgg 2 column left sidebar canvas layout
	 * 
	 * @package Elgg
	 * @subpackage Core
	 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
	 * @author Curverider Ltd
	 * @copyright Curverider Ltd 2008
	 * @link http://elgg.org/
	 */

?>
<!-- left sidebar -->
<div id="two_column_left_sidebar">

    <div id="owner_block">
		<?php echo $vars['area1']; ?>
	</div>

    <?php echo $vars['area3']; ?>

</div><!-- /two_column_left_sidebar -->

<!-- main content -->
<div id="two_column_left_sidebar_maincontent">

<?php echo $vars['area2']; ?>

</div><!-- /two_column_left_sidebar_maincontent -->
