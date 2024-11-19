<?php

namespace App\DTO;

class adsDTO
{
    public $id;
    public $title;
    public $userName;
    public $reportCount;

    public function __construct(int $id, string $title, string $userName, int $reportCount)
    {
        $this->id = $id;
        $this->title = $title;
        $this->userName = $userName;
        $this->reportCount = $reportCount;
    }

    public function getId()
    {
        return $this->id;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function getUserName()
    {
        return $this->userName;
    }
    public function getReportCount()
    {
        return $this->reportCount;
    }
}
