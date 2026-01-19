<?php
class Pizza {
    private $name;
    private $description;
    private $price;
    private $image;
    private $subject;

    public function __construct($name, $description, $price, $image) {
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->image = $image;
        $this->subject = new NotificationSubject();
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getImage() {
        return $this->image;
    }

    public function addObserver(Observer $observer) {
        $this->subject->attach($observer);
    }
}
?>