<?php

return [
    'module_name' => 'Autovermietungsverwaltung',
    'vehicles' => 'Fahrzeuge',
    'rentals' => 'Vermietungen',
    'bookings' => 'Buchungen',

    'vehicle' => [
        'title' => 'Fahrzeug',
        'add_new' => 'Neues Fahrzeug hinzufügen',
        'edit' => 'Fahrzeug bearbeiten',
        'delete' => 'Fahrzeug löschen',
        'details' => 'Fahrzeugdetails',
        'available' => 'Verfügbare Fahrzeuge',
        'unavailable' => 'Nicht verfügbare Fahrzeuge',
    ],

    'rental' => [
        'title' => 'Vermietung',
        'book_now' => 'Jetzt buchen',
        'my_rentals' => 'Meine Vermietungen',
        'rental_number' => 'Vermietungsnummer',
        'pickup_date' => 'Abholdatum',
        'return_date' => 'Rückgabedatum',
        'total_days' => 'Gesamttage',
        'daily_rate' => 'Tagesrate',
        'total_amount' => 'Gesamtbetrag',
    ],

    'status' => [
        'available' => 'Verfügbar',
        'rented' => 'Vermietet',
        'maintenance' => 'In Wartung',
        'retired' => 'Ausgemustert',
        'draft' => 'Entwurf',
        'pending' => 'Warten auf Überprüfung',
        'verified' => 'Verifiziert',
        'confirmed' => 'Bestätigt',
        'active' => 'Aktive Vermietung',
        'completed' => 'Abgeschlossen',
        'overdue' => 'Überfällig',
        'cancelled' => 'Storniert',
        'rejected' => 'Abgelehnt',
    ],

    'category' => [
        'economy' => 'Economy',
        'compact' => 'Kompakt',
        'midsize' => 'Mittelklasse',
        'luxury' => 'Luxus',
        'suv' => 'SUV',
        'van' => 'Van',
    ],

    'extras' => [
        'title' => 'Extras',
        'gps' => [
            'name' => 'GPS-Navigation',
            'description' => 'Tragbares GPS-Gerät für einfache Navigation',
        ],
        'child_seat' => [
            'name' => 'Kindersitz',
            'description' => 'Sicherheitskindersitz für Kinder',
        ],
        'additional_driver' => [
            'name' => 'Zusätzlicher Fahrer',
            'description' => 'Fügen Sie einen zusätzlichen autorisierten Fahrer hinzu',
        ],
        'insurance_premium' => [
            'name' => 'Premium-Versicherung',
            'description' => 'Vollkaskoversicherung ohne Selbstbeteiligung',
        ],
    ],

    'notifications' => [
        'booking_submitted' => [
            'subject' => 'Buchung erfolgreich eingereicht',
            'greeting' => 'Hallo :name,',
            'message' => 'Ihre Mietbuchung :rental_number für :vehicle wurde erfolgreich eingereicht und wartet auf Überprüfung.',
        ],
        'booking_verified' => [
            'subject' => 'Buchung verifiziert',
            'greeting' => 'Hallo :name,',
            'message' => 'Ihre Mietbuchung :rental_number für :vehicle wurde verifiziert. Bitte fahren Sie mit der Zahlung fort.',
        ],
        'booking_confirmed' => [
            'subject' => 'Buchung bestätigt',
            'greeting' => 'Hallo :name,',
            'message' => 'Ihre Mietbuchung :rental_number für :vehicle wurde bestätigt. Sie können das Fahrzeug am geplanten Datum abholen.',
        ],
        'rental_completed' => [
            'subject' => 'Vermietung abgeschlossen',
            'greeting' => 'Hallo :name,',
            'message' => 'Ihre Vermietung :rental_number für :vehicle wurde abgeschlossen. Vielen Dank, dass Sie uns gewählt haben!',
        ],
        'view_rental' => 'Vermietung anzeigen',
    ],

    'documents' => [
        'rental_agreement' => 'Mietvertrag',
        'vehicle_inspection' => 'Fahrzeuginspektion-Checkliste',
        'payment_receipt' => 'Zahlungsbeleg',
        'booking_confirmation' => 'Buchungsbestätigung',
    ],
];
