<?php

namespace Config;

class Config
{
    public const TEMPLATE_VIEW_PATH = './views/templates/';
    public const MAIN_VIEW_PATH     = self::TEMPLATE_VIEW_PATH . 'main.php';

    public const DB_HOST = 'localhost';
    public const DB_NAME = 'blog_forteroche';
    public const DB_USER = 'root';
    public const DB_PASS = '';
}
