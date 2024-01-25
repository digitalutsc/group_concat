<?php

namespace Drupal\group_concat\Plugin\views\query;

use Drupal\views\Plugin\views\query\Sql;

/**
 * Views query plugin for an custom SQL query.
 *
 * @ingroup views_query_plugins
 *
 * @ViewsQuery(
 *   id = "concat_query",
 *   title = @Translation("SQL Concat Query"),
 *   help = @Translation("Query will be generated and run using the Drupal database API.")
 * )
 */
class ConcatSql extends Sql {

  /**
   * {@inheritdoc}
   */
  public function getAggregationInfo() {
    $aggregation_info = parent::getAggregationInfo();

    $aggregation_info += [
      'group_concat' => [
        'title' => $this->t('Group Concat'),
        'method' => 'aggregationMethodSimple',
        'handler' => [
          'argument' => 'groupby_numeric',
          'field' => 'standard',
          'filter' => 'groupby_string',
          'sort' => 'groupby_numeric',
        ],
      ],
      'group_concat_distinct' => [
        'title' => $this->t('Group Concat DISTINCT'),
        'method' => 'aggregationMethodDistinct',
        'handler' => [
          'argument' => 'groupby_numeric',
          'field' => 'standard',
          'filter' => 'groupby_string',
          'sort' => 'groupby_numeric',
        ],
      ],
    ];

    return $aggregation_info;
  }

}
