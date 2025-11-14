<?php

return [
    'module_name' => 'Gestion de location de voitures',
    'vehicles' => 'Véhicules',
    'rentals' => 'Locations',
    'bookings' => 'Réservations',

    'vehicle' => [
        'title' => 'Véhicule',
        'add_new' => 'Ajouter un nouveau véhicule',
        'edit' => 'Modifier le véhicule',
        'delete' => 'Supprimer le véhicule',
        'details' => 'Détails du véhicule',
        'available' => 'Véhicules disponibles',
        'unavailable' => 'Véhicules indisponibles',
    ],

    'rental' => [
        'title' => 'Location',
        'book_now' => 'Réserver maintenant',
        'my_rentals' => 'Mes locations',
        'rental_number' => 'Numéro de location',
        'pickup_date' => 'Date de prise en charge',
        'return_date' => 'Date de retour',
        'total_days' => 'Jours totaux',
        'daily_rate' => 'Tarif journalier',
        'total_amount' => 'Montant total',
    ],

    'status' => [
        'available' => 'Disponible',
        'rented' => 'Loué',
        'maintenance' => 'En maintenance',
        'retired' => 'Retiré',
        'draft' => 'Brouillon',
        'pending' => 'En attente de vérification',
        'verified' => 'Vérifié',
        'confirmed' => 'Confirmé',
        'active' => 'Location active',
        'completed' => 'Terminé',
        'overdue' => 'En retard',
        'cancelled' => 'Annulé',
        'rejected' => 'Rejeté',
    ],

    'category' => [
        'economy' => 'Économique',
        'compact' => 'Compacte',
        'midsize' => 'Moyenne',
        'luxury' => 'Luxe',
        'suv' => 'SUV',
        'van' => 'Fourgonnette',
    ],

    'extras' => [
        'title' => 'Extras',
        'gps' => [
            'name' => 'Navigation GPS',
            'description' => 'Appareil GPS portable pour une navigation facile',
        ],
        'child_seat' => [
            'name' => 'Siège enfant',
            'description' => 'Siège de sécurité pour enfants',
        ],
        'additional_driver' => [
            'name' => 'Conducteur supplémentaire',
            'description' => 'Ajouter un conducteur supplémentaire autorisé',
        ],
        'insurance_premium' => [
            'name' => 'Assurance Premium',
            'description' => 'Assurance tous risques sans franchise',
        ],
    ],

    'notifications' => [
        'booking_submitted' => [
            'subject' => 'Réservation soumise avec succès',
            'greeting' => 'Bonjour :name,',
            'message' => 'Votre réservation de location :rental_number pour :vehicle a été soumise avec succès et est en attente de vérification.',
        ],
        'booking_confirmed' => [
            'subject' => 'Réservation confirmée',
            'greeting' => 'Bonjour :name,',
            'message' => 'Votre réservation de location :rental_number pour :vehicle a été confirmée. Vous pouvez récupérer le véhicule à la date prévue.',
        ],
        'rental_completed' => [
            'subject' => 'Location terminée',
            'greeting' => 'Bonjour :name,',
            'message' => 'Votre location :rental_number pour :vehicle est terminée. Merci de nous avoir choisis!',
        ],
        'view_rental' => 'Voir la location',
    ],

    'documents' => [
        'rental_agreement' => 'Contrat de location',
        'vehicle_inspection' => 'Liste de vérification du véhicule',
        'payment_receipt' => 'Reçu de paiement',
        'booking_confirmation' => 'Confirmation de réservation',
    ],
];
