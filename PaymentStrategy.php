<?php
// PaymentStrategy.php
interface PaymentStrategy {
    public function getPaymentDetails(array $postData): array;
    public function getName(): string;
}

class CreditCardPayment implements PaymentStrategy {
    public function getPaymentDetails(array $postData): array {
        return [
            'type' => 'credit_card',
            'card_number' => substr($postData['card_number'], -4), // Store only last 4 digits
            'card_holder' => $postData['card_holder'],
            'expiry' => $postData['expiry']
        ];
    }

    public function getName(): string {
        return "Credit Card";
    }
}

class OnlineBankingPayment implements PaymentStrategy {
    public function getPaymentDetails(array $postData): array {
        return [
            'type' => 'online_banking',
            'bank_name' => $postData['bank_name']
        ];
    }

    public function getName(): string {
        return "Online Banking";
    }
}

class CashPaymentStrategy implements PaymentStrategy {
    public function getPaymentDetails(array $postData): array {
        return [
            'type' => 'cash',
            'method' => 'Cash on Delivery'
        ];
    }

    public function getName(): string {
        return "Cash on Delivery";
    }
}

class PaymentProcessor {
    private $paymentStrategy;

    public function setPaymentStrategy(PaymentStrategy $strategy) {
        $this->paymentStrategy = $strategy;
    }

    public function getPaymentDetails(array $postData): array {
        return $this->paymentStrategy->getPaymentDetails($postData);
    }
}