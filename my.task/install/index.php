<?php

/**
 * Учебный модуль "my.task"
 */
class my_task extends CModule
{
    public $MODULE_ID = "my.task";
    public $MODULE_NAME = "Тренировочный модуль (Логирование)";
    public $MODULE_DESCRIPTION = "Задание 5 недели: события и агенты.";
    public $PARTNER_NAME = "Alexandr";
    public $PARTNER_URI = "http://localhost";
    public $MODULE_VERSION = "1.0.0";
    public $MODULE_VERSION_DATE = "2026-04-24 22:00:00";

    public function __construct()
    {
        $this->MODULE_ID = "my.task";
        $this->MODULE_NAME = "Тренировочный модуль (Логирование)";
        $this->MODULE_DESCRIPTION = "Задание 5 недели: события и агенты.";
        $this->MODULE_VERSION = "1.0.0";
        $this->MODULE_VERSION_DATE = "2026-04-24 22:00:00";
    }

    public function DoInstall()
    {
        RegisterModule($this->MODULE_ID);
    }

    public function DoUninstall()
    {
        UnRegisterModule($this->MODULE_ID);
    }
}