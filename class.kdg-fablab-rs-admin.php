<?php
  class KdGFablab_IS_Admin {
    private static $initiated = false;

    public static function init() {
      if (!self::$initiated) {
        self::init_hooks();
      }
    }

    private static function init_hooks() {
      self::$initiated = true;
    }
