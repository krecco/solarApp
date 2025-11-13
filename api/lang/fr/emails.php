<?php

return [
    // Email subjects
    'welcome_subject' => 'Bienvenue sur :app_name',
    'verification_subject' => 'Vérifiez votre adresse e-mail',
    'password_reset_subject' => 'Réinitialisez votre mot de passe',
    'investment_confirmed_subject' => 'Investissement Confirmé - :contract_number',
    'repayment_processed_subject' => 'Remboursement Traité - :amount',
    'document_ready_subject' => 'Votre document est prêt',
    'new_message_subject' => 'Vous avez un nouveau message',
    'plant_status_subject' => 'Mise à jour du statut de la centrale solaire - :plant_name',

    // Email greetings
    'greeting' => 'Bonjour',
    'greeting_with_name' => 'Bonjour :name',
    'regards' => 'Cordialement',
    'team_signature' => 'L\'équipe :app_name',

    // Welcome email
    'welcome_line1' => 'Bienvenue sur :app_name ! Nous sommes ravis de vous accueillir.',
    'welcome_line2' => 'Vous pouvez maintenant accéder à votre compte et gérer vos investissements.',
    'welcome_action' => 'Commencer',

    // Email verification
    'verify_line1' => 'Veuillez cliquer sur le bouton ci-dessous pour vérifier votre adresse e-mail.',
    'verify_line2' => 'Si vous n\'avez pas créé de compte, aucune autre action n\'est requise.',
    'verify_action' => 'Vérifier l\'adresse e-mail',
    'verify_expire' => 'Ce lien de vérification expirera dans :count minutes.',

    // Password reset
    'reset_line1' => 'Vous recevez cet e-mail car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.',
    'reset_line2' => 'Si vous n\'avez pas demandé de réinitialisation de mot de passe, aucune autre action n\'est requise.',
    'reset_action' => 'Réinitialiser le mot de passe',
    'reset_expire' => 'Ce lien de réinitialisation de mot de passe expirera dans :count minutes.',

    // Investment notifications
    'investment_confirmed_line1' => 'Votre investissement a été confirmé avec succès.',
    'investment_confirmed_line2' => 'Montant : :amount',
    'investment_confirmed_line3' => 'Numéro de Contrat : :contract_number',
    'investment_confirmed_action' => 'Voir les détails de l\'investissement',

    // Repayment notifications
    'repayment_processed_line1' => 'Un remboursement a été traité pour votre investissement.',
    'repayment_processed_line2' => 'Montant : :amount',
    'repayment_processed_line3' => 'Date : :date',
    'repayment_processed_action' => 'Voir les détails du remboursement',

    // Document notifications
    'document_ready_line1' => 'Votre document demandé est maintenant disponible au téléchargement.',
    'document_ready_line2' => 'Type de Document : :type',
    'document_ready_action' => 'Télécharger le document',

    // Message notifications
    'new_message_line1' => 'Vous avez reçu un nouveau message.',
    'new_message_line2' => 'De : :from',
    'new_message_action' => 'Voir le message',

    // Plant status notifications
    'plant_status_line1' => 'Mise à jour du statut de votre centrale solaire : :plant_name',
    'plant_status_line2' => 'Statut : :status',
    'plant_status_action' => 'Voir les détails de la centrale',

    // Common email elements
    'button_trouble' => 'Si vous avez des difficultés à cliquer sur le bouton ":action", copiez et collez l\'URL ci-dessous dans votre navigateur web :',
    'unsubscribe' => 'Si vous ne souhaitez plus recevoir ces e-mails, vous pouvez',
    'unsubscribe_link' => 'vous désabonner ici',
    'email_footer' => '© :year :app_name. Tous droits réservés.',
    'privacy_policy' => 'Politique de Confidentialité',
    'terms_of_service' => 'Conditions d\'Utilisation',
    'contact_us' => 'Contactez-nous',
];
