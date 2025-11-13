<?php

return [
    'welcome' => [
        'subject' => 'Bienvenue sur la Plateforme d\'Investissement Solaire',
        'greeting' => 'Bienvenue, :name !',
        'body' => 'Merci de rejoindre notre plateforme d\'investissement solaire. Nous sommes ravis de vous accueillir.',
        'next_steps' => 'Prochaines Étapes',
        'verify_email' => 'Veuillez vérifier votre adresse e-mail pour commencer.',
        'complete_profile' => 'Complétez votre profil pour commencer à investir.',
        'browse_projects' => 'Parcourir les projets solaires disponibles.',
        'footer' => 'Si vous avez des questions, n\'hésitez pas à contacter notre équipe de support.',
    ],

    'document' => [
        'verified_subject' => 'Document Vérifié avec Succès',
        'rejected_subject' => 'Échec de la Vérification du Document',
        'greeting' => 'Bonjour, :name !',
        'verified_body' => 'Votre document ":document_type" a été vérifié avec succès.',
        'rejected_body' => 'Malheureusement, votre document ":document_type" a été rejeté.',
        'rejection_reason' => 'Raison : :reason',
        'next_action_verified' => 'Vous pouvez maintenant procéder à votre demande d\'investissement.',
        'next_action_rejected' => 'Veuillez télécharger un nouveau document qui répond à nos exigences.',
        'view_documents' => 'Voir Mes Documents',
    ],

    'investment' => [
        'confirmation_subject' => 'Confirmation d\'Investissement',
        'greeting' => 'Bonjour, :name !',
        'confirmation_body' => 'Votre investissement a été confirmé avec succès.',
        'investment_details' => 'Détails de l\'Investissement',
        'amount' => 'Montant',
        'solar_plant' => 'Centrale Solaire',
        'duration' => 'Durée',
        'interest_rate' => 'Taux d\'Intérêt',
        'start_date' => 'Date de Début',
        'expected_return' => 'Rendement Attendu',
        'next_steps' => 'Vous recevrez des mises à jour régulières sur votre investissement.',
        'view_investment' => 'Voir les Détails de l\'Investissement',
    ],

    'repayment' => [
        'upcoming_subject' => 'Remboursement à Venir',
        'overdue_subject' => 'Avis de Remboursement en Retard',
        'final_notice_subject' => 'AVIS FINAL : Remboursement en Retard',
        'reminder_subject' => 'Rappel de Remboursement',
        'greeting' => 'Bonjour, :name !',
        'upcoming_body' => 'Ceci est un rappel que votre remboursement est dû bientôt.',
        'overdue_body' => 'Votre remboursement est maintenant en retard. Veuillez effectuer le paiement dès que possible.',
        'final_notice_body' => 'Ceci est un avis final. Votre remboursement est considérablement en retard et nécessite une attention immédiate.',
        'repayment_details' => 'Détails du Remboursement',
        'amount_due' => 'Montant Dû',
        'due_date' => 'Date d\'Échéance',
        'investment_ref' => 'Référence d\'Investissement',
        'payment_instructions' => 'Instructions de Paiement',
        'make_payment' => 'Effectuer le Paiement',
    ],

    'invoice' => [
        'subject' => 'Facture :invoice_number',
        'greeting' => 'Bonjour, :name !',
        'body' => 'Veuillez trouver ci-joint votre facture.',
        'invoice_details' => 'Détails de la Facture',
        'invoice_number' => 'Numéro de Facture',
        'invoice_date' => 'Date de Facture',
        'due_date' => 'Date d\'Échéance',
        'amount' => 'Montant Total',
        'payment_instructions' => 'Instructions de Paiement',
        'view_invoice' => 'Voir la Facture en Ligne',
        'attached' => 'Le PDF de la facture est joint à cet e-mail.',
    ],

    'common' => [
        'button' => [
            'login' => 'Se Connecter au Tableau de Bord',
            'view' => 'Voir les Détails',
            'contact_support' => 'Contacter le Support',
        ],
        'footer' => [
            'thanks' => 'Merci d\'avoir choisi notre plateforme !',
            'questions' => 'Si vous avez des questions, contactez-nous à :email',
            'unsubscribe' => 'Se désabonner de ces e-mails',
        ],
    ],
];
