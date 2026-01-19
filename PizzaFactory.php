<?php
require_once 'Pizza.php';

class PizzaFactory {
    public static function createPizza($row) {
        return new Pizza(
            $row['FoodName'],
            $row['About'],
            $row['FoodPrice'],
            $row['ItemImage']
        );
    }
}
?>