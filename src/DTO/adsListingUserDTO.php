<?php

namespace App\DTO;

class adsListingUserDTO
{
    public $id;
    public $title;
    public $userName;
    public $price;
    public $description;
    public function __construct(int $id, string $title, int $price, string $description, string $userName)
    {
        $this->id = $id;
        $this->title = $title;
        $this->price = $price ;
        $this->description = $description ;
        $this->userName = $userName;
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
    public function getPrice()
    {
        return $this->price;
    }
    public function getDescription()
    {
        return $this->description;
    }
}?>
    