<?php

/**
 * @file
 * PDF template for printing.
 *
 * Available variables are:
 *  - $entity - The entity itself.
 *  - $entity_array - The renderable array of this entity.
 *  - $entity_print_css - An array of stylesheets to be rendered.
 */
?>

<html>
<head>
  <title>PDF</title>
  <?php print drupal_get_css($entity_print_css);?>
</head>
<body>
<div class="page">
  <?php print render($entity_array); ?>
</div>
</body>
</html>
