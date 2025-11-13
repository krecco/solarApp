<?php

return [
    // Email subjects
    'welcome_subject' => 'Willkommen bei :app_name',
    'verification_subject' => 'Bestätigen Sie Ihre E-Mail-Adresse',
    'password_reset_subject' => 'Setzen Sie Ihr Passwort zurück',
    'investment_confirmed_subject' => 'Investition bestätigt - :contract_number',
    'repayment_processed_subject' => 'Rückzahlung verarbeitet - :amount',
    'document_ready_subject' => 'Ihr Dokument ist bereit',
    'new_message_subject' => 'Sie haben eine neue Nachricht',
    'plant_status_subject' => 'Solaranlagen-Statusaktualisierung - :plant_name',

    // Email greetings
    'greeting' => 'Hallo',
    'greeting_with_name' => 'Hallo :name',
    'regards' => 'Mit freundlichen Grüßen',
    'team_signature' => 'Das :app_name Team',

    // Welcome email
    'welcome_line1' => 'Willkommen bei :app_name! Wir freuen uns, Sie an Bord zu haben.',
    'welcome_line2' => 'Sie können jetzt auf Ihr Konto zugreifen und Ihre Investitionen verwalten.',
    'welcome_action' => 'Jetzt starten',

    // Email verification
    'verify_line1' => 'Bitte klicken Sie auf die Schaltfläche unten, um Ihre E-Mail-Adresse zu bestätigen.',
    'verify_line2' => 'Wenn Sie kein Konto erstellt haben, ist keine weitere Aktion erforderlich.',
    'verify_action' => 'E-Mail-Adresse bestätigen',
    'verify_expire' => 'Dieser Bestätigungslink läuft in :count Minuten ab.',

    // Password reset
    'reset_line1' => 'Sie erhalten diese E-Mail, weil wir eine Anfrage zum Zurücksetzen des Passworts für Ihr Konto erhalten haben.',
    'reset_line2' => 'Wenn Sie kein Zurücksetzen des Passworts angefordert haben, ist keine weitere Aktion erforderlich.',
    'reset_action' => 'Passwort zurücksetzen',
    'reset_expire' => 'Dieser Link zum Zurücksetzen des Passworts läuft in :count Minuten ab.',

    // Investment notifications
    'investment_confirmed_line1' => 'Ihre Investition wurde erfolgreich bestätigt.',
    'investment_confirmed_line2' => 'Betrag: :amount',
    'investment_confirmed_line3' => 'Vertragsnummer: :contract_number',
    'investment_confirmed_action' => 'Investitionsdetails anzeigen',

    // Repayment notifications
    'repayment_processed_line1' => 'Eine Rückzahlung für Ihre Investition wurde verarbeitet.',
    'repayment_processed_line2' => 'Betrag: :amount',
    'repayment_processed_line3' => 'Datum: :date',
    'repayment_processed_action' => 'Rückzahlungsdetails anzeigen',

    // Document notifications
    'document_ready_line1' => 'Ihr angefordertes Dokument steht jetzt zum Download bereit.',
    'document_ready_line2' => 'Dokumenttyp: :type',
    'document_ready_action' => 'Dokument herunterladen',

    // Message notifications
    'new_message_line1' => 'Sie haben eine neue Nachricht erhalten.',
    'new_message_line2' => 'Von: :from',
    'new_message_action' => 'Nachricht anzeigen',

    // Plant status notifications
    'plant_status_line1' => 'Statusaktualisierung für Ihre Solaranlage: :plant_name',
    'plant_status_line2' => 'Status: :status',
    'plant_status_action' => 'Anlagendetails anzeigen',

    // Common email elements
    'button_trouble' => 'Wenn Sie Probleme haben, auf die Schaltfläche ":action" zu klicken, kopieren Sie die folgende URL und fügen Sie sie in Ihren Webbrowser ein:',
    'unsubscribe' => 'Wenn Sie diese E-Mails nicht mehr erhalten möchten, können Sie sich',
    'unsubscribe_link' => 'hier abmelden',
    'email_footer' => '© :year :app_name. Alle Rechte vorbehalten.',
    'privacy_policy' => 'Datenschutzrichtlinie',
    'terms_of_service' => 'Nutzungsbedingungen',
    'contact_us' => 'Kontaktieren Sie uns',
];
