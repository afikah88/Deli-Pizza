<?php
interface Observer {
    public function update($message);
}

class NotificationManager implements Observer {
    private $notifications = [];

    public function update($message, $type = 'info') {
        $this->notifications[] = ['message' => $message, 'type' => $type];
        $this->displayNotifications();
    }

    private function displayNotifications() {
        echo '<style>
            .notification-container {
                position: fixed;
                top: 20px;
                right: 20px;
                display: flex;
                flex-direction: column-reverse; /* Newest on top */
                gap: 10px;
                z-index: 1000;
            }

            .notification-banner {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 15px 20px;
                min-width: 300px;
                border-radius: 8px;
                font-family: Arial, sans-serif;
                font-size: 14px;
                color: white;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                animation: fadeIn 0.5s ease-out;
                position: relative;
            }

            .notification-banner .btn {
                padding: 5px 10px;
                margin-left: 10px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 13px;
                font-weight: bold;
            }

            .notification-banner .close-btn {
                position: absolute;
                top: 8px;
                right: 10px;
                background: none;
                border: none;
                font-size: 18px;
                color: white;
                cursor: pointer;
            }

            /* Notification Types */
            .success { background-color: #28a745; }
            .warning { background-color: #ffc107; color: #333; }
            .error   { background-color: #dc3545; }
            .info    { background-color: #007bff; }

            /* Button Styles */
            .btn-visit { background-color: #ffeb3b; color: #333; }
            .btn-accept { background-color: #d4edda; color: #155724; }
            .btn-subscribe { background-color: #ff9800; color: white; }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>';

        echo '<div class="notification-container">';
        foreach ($this->notifications as $notif) {
            $message = $notif['message'];
            $type = $notif['type'];
            echo "<div class='notification-banner {$type}'>
                    {$message}
                    <button class='close-btn' onclick='this.parentElement.style.display=\"none\";'>&times;</button>
                  </div>";
        }
        echo '</div>';
    }
}

class NotificationSubject {
    private $observers = array();
    
    public function attach(Observer $observer) {
        $this->observers[] = $observer;
    }
    
    public function detach(Observer $observer) {
        $key = array_search($observer, $this->observers, true);
        if ($key !== false) {
            unset($this->observers[$key]);
        }
    }
    
    public function notify($message) {
        foreach ($this->observers as $observer) {
            $observer->update($message);
        }
    }
}
?>