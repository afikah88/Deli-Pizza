<?php
class NotificationService {
    private $subject;

    public function __construct() {
        $this->subject = new NotificationSubject();
    }

    public function addObserver(Observer $observer) {
        $this->subject->attach($observer);
    }

    public function sendNotification($type) {
        $message = $this->getNotificationMessage($type);
        $this->subject->notify($message);
    }

    private function getNotificationMessage($type) {
        $messages = [
            'login_required' => "🔒 Please log in to proceed to checkout and enjoy your pizza!",
            'loyalty_points' => "🎁 Create an account to start earning loyalty points with every order!",
            'new_user' => "👋 New here? Register now to get exclusive discounts on your first order!"
        ];

        return $messages[$type] ?? "🍕 Welcome to Delicious Pizza!";
    }
}
?>