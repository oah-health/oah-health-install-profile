<?php

namespace Drupal\office_hours\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemList;
use Drupal\office_hours\OfficeHoursDateHelper;
use Drupal\office_hours\OfficeHoursListInterface;

/**
 * Represents an Office hours field.
 */
class OfficeHoursItemList extends FieldItemList implements OfficeHoursListInterface {

  public function isOpen($time = NULL) {
    list(, $open) = $this->getOfficeHoursAndStatus($time);

    return $open;
  }

  private function getOfficeHoursAndStatus($time = NULL) {

    // Initialize days and times, using date_api as key (0=Sun, 6-Sat)
    // Empty days are not yet present in $items, and are now added in $days.
    for ($day = 0; $day < 7; $day++) {
      $office_hours[$day] = [
        'startday' => $day,
        'endday' => NULL,
        'times' => NULL,
        'current' => FALSE,
        'next' => FALSE,
      ];
    }

    // Loop through all lines.
    // Detect the current line and the open/closed status.
    // Convert the daynumber to (int) to get '0' for Sundays, not 'false'.
    if ($time === NULL) {
      $time = REQUEST_TIME;
    }
    $today = (int) idate('w', $time); // Get daynumber sun=0 - sat=6.
    $now = date('Gi', $time); // 'Gi' format, with leading zero (0900).
    $open = FALSE;
    $next = NULL;
    foreach ($this->getValue() as $key => $item) {
      // Calculate start and end times.
      $day = (int) $item['day'];
      // 'Gi' format, with leading zero (0900).
      $start = OfficeHoursDateHelper::datePad($item['starthours'], 4);
      $end = OfficeHoursDateHelper::datePad($item['endhours'], 4);

      $office_hours[$day]['times'][] = [
        'start' => $start,
        'end' => $end,
        'comment' => $item['comment'],
      ];

      // Are we currently open? If not, when is the next time?
      // Remember: empty days are not in $items; they are present in $days.
      if ($day < $today) {
        // Initialize to first day of (next) week, in case we're closed
        // the rest of the week.
        if ($next === NULL) {
          $next = $day;
        }
      }

      if ($day - $today == -1 || ($day - $today == 6)) {
        // We were open yesterday evening, check if we are still open.
        if ($start >= $end && $end >= $now) {
          $open = TRUE;
          $office_hours[$day]['current'] = TRUE;
          $next = $day;
        }
      }
      elseif ($day == $today) {
        if ($start <= $now) {
          // We were open today, check if we are still open.
          if (($start > $end)    // We are open until after midnight.
            || ($start == $end) // We are open 24hrs per day.
            || (($start < $end) && ($end > $now))
          ) {
            // We have closed already.
            $open = TRUE;
            $office_hours[$day]['current'] = TRUE;
            $next = $day;
          }
          else {
            // We have already closed.
          }
        }
        else {
          // We will open later today.
          $next = $day;
        }
      }
      elseif ($day > $today) {
        if ($next === NULL || $next < $today) {
          $next = $day;
        }
      }
    }
    if ($next !== NULL) {
      $office_hours[$next]['next'] = TRUE;
    }

    return [$office_hours, $open];
  }

  public function getOfficeHoursArray($time = NULL) {
    list($office_hours) = $this->getOfficeHoursAndStatus($time);

    return $office_hours;
  }

}
