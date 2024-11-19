<?php

namespace App\DTO;

class findAdsByIAdminDTO
{
    public $id;
    public $title;
    public $price;
    public $description;
    public $zipCode;
    public $width;
    public $length;
    public $height;
    public $isVerified;
    public $userName;
    public $image;
    public function __construct(
        int $id,
        string $title,
        int $price,
        string $description,
        int $zipCode,
        int $width,
        int $length,
        int $height,
        bool $isVerified,
        string $userName
    ) {
        $this->id =  $id;
        $this->title = $title;
        $this->price = $price;
        $this->description = $description;
        $this->zipCode = $zipCode;
        $this->width = $width;
        $this->length = $length;
        $this->height = $height;
        $this->isVerified = $isVerified;
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
    public function getImage()
    {
        return $this->image;
    }
    public function getZipCode()
    {
        return $this->zipCode;
    }
    public function getWidth()
    {
        return $this->width;
    }
    public function getLength()
    {
        return $this->length;
    }
    public function getHeight()
    {
        return $this->height;
    }
    public function getIsVerified()
    {
        return $this->isVerified;
    }

}?>
    