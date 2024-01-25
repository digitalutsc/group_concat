<?php

namespace Drupal\group_concat\Plugin\views\filter;

use Drupal\Core\Database\Database;
use Drupal\views\Plugin\views\filter\StringFilter;

/**
 * Simple filter to handle greater than/less than filters.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("groupby_string")
 */
class GroupByString extends StringFilter {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    $field = $this->getField();

    $info = $this->operators();
    if (!empty($info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($field);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function opEqual($field) {
    $placeholder = $this->placeholder();
    $this->query->addHavingExpression($this->options['group'], "$field $this->operator $placeholder", [
      $placeholder => $this->value,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function opContains($field) {
    $placeholder = $this->placeholder();
    $this->query->addHavingExpression($this->options['group'], "$field LIKE $placeholder", [
      $placeholder => '%' . Database::getConnection()->escapeLike($this->value) . '%',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function opContainsWord($field) {
    // Don't filter on empty strings.
    if (empty($this->value)) {
      return;
    }

    $haystack = [];

    preg_match_all(static::WORDS_PATTERN, ' ' . $this->value, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
      $phrase = FALSE;
      // Strip off phrase quotes.
      if ($match[2][0] == '"') {
        $match[2] = substr($match[2], 1, -1);
        $phrase = TRUE;
      }
      $words = trim($match[2], ',?!();:-');
      $words = $phrase ? [$words] : preg_split('/ /', $words, -1, PREG_SPLIT_NO_EMPTY);

      foreach ($words as $word) {
        $haystack[] = $word;
      }
    }

    if (empty($haystack)) {
      return;
    }

    if ($this->operator == 'word') {
      $placeholder = $this->placeholder() . '[]';
      $this->query->addHavingExpression($this->options['group'], "$field IN ($placeholder)", [
        $placeholder => $haystack,
      ]);
    }
    else {
      $snippet = [];

      foreach ($haystack as $word) {
        $snippet[] = "$field LIKE '%" . Database::getConnection()->escapeLike($word) . "%'";
      }

      $this->query->addHavingExpression($this->options['group'], implode(') AND (', $snippet));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function opStartsWith($field) {
    $placeholder = $this->placeholder();
    $this->query->addHavingExpression($this->options['group'], "$field LIKE $placeholder", [
      $placeholder => Database::getConnection()->escapeLike($this->value) . '%',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function opNotStartsWith($field) {
    $placeholder = $this->placeholder();
    $this->query->addHavingExpression($this->options['group'], "$field NOT LIKE $placeholder", [
      $placeholder => Database::getConnection()->escapeLike($this->value) . '%',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function opEndsWith($field) {
    $placeholder = $this->placeholder();
    $this->query->addHavingExpression($this->options['group'], "$field LIKE $placeholder", [
      $placeholder => '%' . Database::getConnection()->escapeLike($this->value),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function opNotEndsWith($field) {
    $placeholder = $this->placeholder();
    $this->query->addHavingExpression($this->options['group'], "$field NOT LIKE $placeholder", [
      $placeholder => '%' . Database::getConnection()->escapeLike($this->value),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function opNotLike($field) {
    $placeholder = $this->placeholder();
    $this->query->addHavingExpression($this->options['group'], "$field NOT LIKE $placeholder", [
      $placeholder => '%' . Database::getConnection()->escapeLike($this->value) . '%',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function opShorterThan($field) {
    $placeholder = $this->placeholder();
    // Type cast the argument to an integer because the SQLite database driver
    // has to do some specific alterations to the query base on that data type.
    $this->query->addHavingExpression($this->options['group'], "LENGTH($field) < $placeholder", [
      $placeholder => (int) $this->value,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function opLongerThan($field) {
    $placeholder = $this->placeholder();
    // Type cast the argument to an integer because the SQLite database driver
    // has to do some specific alterations to the query base on that data type.
    $this->query->addHavingExpression($this->options['group'], "LENGTH($field) > $placeholder", [
      $placeholder => (int) $this->value,
    ]);
  }

  /**
   * Filters by a regular expression.
   *
   * @param string $field
   *   The expression pointing to the queries field, for example "foo.bar".
   */
  protected function opRegex($field) {
    $placeholder = $this->placeholder();
    // Type cast the argument to an integer because the SQLite database driver
    // has to do some specific alterations to the query base on that data type.
    $this->query->addHavingExpression($this->options['group'], "$field REGEXP $placeholder", [
      $placeholder => $this->value,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function opEmpty($field) {
    if ($this->operator == 'empty') {
      $operator = "IS NULL";
    }
    else {
      $operator = "IS NOT NULL";
    }

    $this->query->addHavingExpression($this->options['group'], "$field $operator");
  }

  /**
   * {@inheritdoc}
   */
  public function adminLabel($short = FALSE) {
    return $this->getField(parent::adminLabel($short));
  }

  /**
   * {@inheritdoc}
   */
  public function canGroup() {
    return FALSE;
  }

}
