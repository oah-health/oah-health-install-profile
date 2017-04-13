<?php

namespace Drupal\office_hours;

interface OfficeHoursListInterface {

  public function isOpen($time = NULL);

  public function getOfficeHoursArray($time = NULL);

}
