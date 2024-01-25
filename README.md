# Group Aggregation Module
This module is derived from a Drupal core patch that adds Group Concat as an aggregation option.  
The source of the patch is here: https://www.drupal.org/project/drupal/issues/2902481  
The idea of Sql extend is found here: https://gist.github.com/reinis-kinkeris/fa7f98960e7e681d465f2bea259e53f0/

This module is only compatible with Drupal sites running with SQL databases that supports the GROUP_CONCAT() aggregate function. It does not support databases such as PostgreSQL.