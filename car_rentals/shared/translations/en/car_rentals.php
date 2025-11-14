<?php

return [
    'module_name' => 'Car Rental Management',
    'vehicles' => 'Vehicles',
    'rentals' => 'Rentals',
    'bookings' => 'Bookings',

    'vehicle' => [
        'title' => 'Vehicle',
        'add_new' => 'Add New Vehicle',
        'edit' => 'Edit Vehicle',
        'delete' => 'Delete Vehicle',
        'details' => 'Vehicle Details',
        'available' => 'Available Vehicles',
        'unavailable' => 'Unavailable Vehicles',
    ],

    'rental' => [
        'title' => 'Rental',
        'book_now' => 'Book Now',
        'my_rentals' => 'My Rentals',
        'rental_number' => 'Rental Number',
        'pickup_date' => 'Pickup Date',
        'return_date' => 'Return Date',
        'total_days' => 'Total Days',
        'daily_rate' => 'Daily Rate',
        'total_amount' => 'Total Amount',
    ],

    'status' => [
        'available' => 'Available',
        'rented' => 'Rented',
        'maintenance' => 'Under Maintenance',
        'retired' => 'Retired',
        'draft' => 'Draft',
        'pending' => 'Pending Verification',
        'verified' => 'Verified',
        'confirmed' => 'Confirmed',
        'active' => 'Active Rental',
        'completed' => 'Completed',
        'overdue' => 'Overdue',
        'cancelled' => 'Cancelled',
        'rejected' => 'Rejected',
    ],

    'category' => [
        'economy' => 'Economy',
        'compact' => 'Compact',
        'midsize' => 'Midsize',
        'luxury' => 'Luxury',
        'suv' => 'SUV',
        'van' => 'Van',
    ],

    'extras' => [
        'title' => 'Extras',
        'gps' => [
            'name' => 'GPS Navigation',
            'description' => 'Portable GPS device for easy navigation',
        ],
        'child_seat' => [
            'name' => 'Child Seat',
            'description' => 'Safety child seat for children',
        ],
        'additional_driver' => [
            'name' => 'Additional Driver',
            'description' => 'Add an additional authorized driver',
        ],
        'insurance_premium' => [
            'name' => 'Premium Insurance',
            'description' => 'Full coverage insurance with zero deductible',
        ],
    ],

    'notifications' => [
        'booking_submitted' => [
            'subject' => 'Booking Submitted Successfully',
            'greeting' => 'Hello :name,',
            'message' => 'Your rental booking :rental_number for :vehicle has been submitted successfully and is pending verification.',
        ],
        'new_booking_received' => [
            'subject' => 'New Booking Received',
            'greeting' => 'Hello :name,',
            'message' => 'A new rental booking :rental_number for :vehicle has been received and requires verification.',
        ],
        'booking_verified' => [
            'subject' => 'Booking Verified',
            'greeting' => 'Hello :name,',
            'message' => 'Your rental booking :rental_number for :vehicle has been verified. Please proceed with payment.',
        ],
        'booking_confirmed' => [
            'subject' => 'Booking Confirmed',
            'greeting' => 'Hello :name,',
            'message' => 'Your rental booking :rental_number for :vehicle has been confirmed. You can pick up the vehicle on the scheduled date.',
        ],
        'booking_ready_for_pickup' => [
            'subject' => 'Booking Ready for Pickup',
            'greeting' => 'Hello :name,',
            'message' => 'Rental booking :rental_number for :vehicle is ready for pickup.',
        ],
        'rental_reminder_7_days' => [
            'subject' => 'Rental Reminder - 7 Days',
            'greeting' => 'Hello :name,',
            'message' => 'This is a reminder that your rental :rental_number for :vehicle is scheduled in 7 days.',
        ],
        'rental_reminder_1_day' => [
            'subject' => 'Rental Reminder - Tomorrow',
            'greeting' => 'Hello :name,',
            'message' => 'This is a reminder that your rental :rental_number for :vehicle is scheduled for tomorrow.',
        ],
        'return_reminder_1_day' => [
            'subject' => 'Return Reminder',
            'greeting' => 'Hello :name,',
            'message' => 'This is a reminder that your rental :rental_number for :vehicle is due to be returned tomorrow.',
        ],
        'rental_completed' => [
            'subject' => 'Rental Completed',
            'greeting' => 'Hello :name,',
            'message' => 'Your rental :rental_number for :vehicle has been completed. Thank you for choosing us!',
        ],
        'rental_overdue' => [
            'subject' => 'Rental Overdue',
            'greeting' => 'Hello :name,',
            'message' => 'Your rental :rental_number for :vehicle is overdue. Please return the vehicle as soon as possible.',
        ],
        'rental_overdue_alert' => [
            'subject' => 'Rental Overdue Alert',
            'greeting' => 'Hello :name,',
            'message' => 'Rental :rental_number for :vehicle is overdue and requires attention.',
        ],
        'booking_cancelled' => [
            'subject' => 'Booking Cancelled',
            'greeting' => 'Hello :name,',
            'message' => 'Your rental booking :rental_number for :vehicle has been cancelled.',
        ],
        'booking_rejected' => [
            'subject' => 'Booking Rejected',
            'greeting' => 'Hello :name,',
            'message' => 'Unfortunately, your rental booking :rental_number for :vehicle has been rejected.',
        ],
        'review_request' => [
            'subject' => 'How was your rental experience?',
            'greeting' => 'Hello :name,',
            'message' => 'We hope you enjoyed your rental :rental_number for :vehicle. Please take a moment to leave a review.',
        ],
        'view_rental' => 'View Rental',
    ],

    'documents' => [
        'rental_agreement' => 'Rental Agreement',
        'vehicle_inspection' => 'Vehicle Inspection Checklist',
        'payment_receipt' => 'Payment Receipt',
        'booking_confirmation' => 'Booking Confirmation',
    ],
];
