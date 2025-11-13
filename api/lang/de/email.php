<?php

return [
    'welcome' => [
        'subject' => 'Willkommen bei der Solar-Investitionsplattform',
        'greeting' => 'Willkommen, :name!',
        'body' => 'Vielen Dank, dass Sie unserer Solar-Investitionsplattform beigetreten sind. Wir freuen uns, Sie an Bord zu haben.',
        'next_steps' => 'Nächste Schritte',
        'verify_email' => 'Bitte bestätigen Sie Ihre E-Mail-Adresse, um zu beginnen.',
        'complete_profile' => 'Vervollständigen Sie Ihr Profil, um mit dem Investieren zu beginnen.',
        'browse_projects' => 'Durchsuchen Sie verfügbare Solarprojekte.',
        'footer' => 'Bei Fragen wenden Sie sich bitte an unser Support-Team.',
    ],

    'document' => [
        'verified_subject' => 'Dokument erfolgreich verifiziert',
        'rejected_subject' => 'Dokumentenverifizierung fehlgeschlagen',
        'greeting' => 'Hallo, :name!',
        'verified_body' => 'Ihr Dokument ":document_type" wurde erfolgreich verifiziert.',
        'rejected_body' => 'Leider wurde Ihr Dokument ":document_type" abgelehnt.',
        'rejection_reason' => 'Grund: :reason',
        'next_action_verified' => 'Sie können nun mit Ihrer Investitionsanmeldung fortfahren.',
        'next_action_rejected' => 'Bitte laden Sie ein neues Dokument hoch, das unseren Anforderungen entspricht.',
        'view_documents' => 'Meine Dokumente anzeigen',
    ],

    'investment' => [
        'confirmation_subject' => 'Investitionsbestätigung',
        'greeting' => 'Hallo, :name!',
        'confirmation_body' => 'Ihre Investition wurde erfolgreich bestätigt.',
        'investment_details' => 'Investitionsdetails',
        'amount' => 'Betrag',
        'solar_plant' => 'Solaranlage',
        'duration' => 'Laufzeit',
        'interest_rate' => 'Zinssatz',
        'start_date' => 'Startdatum',
        'expected_return' => 'Erwartete Rendite',
        'next_steps' => 'Sie erhalten regelmäßige Updates zu Ihrer Investition.',
        'view_investment' => 'Investitionsdetails anzeigen',
    ],

    'repayment' => [
        'upcoming_subject' => 'Anstehende Rückzahlung fällig',
        'overdue_subject' => 'Überfällige Rückzahlung',
        'final_notice_subject' => 'LETZTE MAHNUNG: Überfällige Rückzahlung',
        'reminder_subject' => 'Rückzahlungserinnerung',
        'greeting' => 'Hallo, :name!',
        'upcoming_body' => 'Dies ist eine Erinnerung, dass Ihre Rückzahlung bald fällig ist.',
        'overdue_body' => 'Ihre Rückzahlung ist jetzt überfällig. Bitte zahlen Sie so schnell wie möglich.',
        'final_notice_body' => 'Dies ist eine letzte Mahnung. Ihre Rückzahlung ist erheblich überfällig und erfordert sofortige Aufmerksamkeit.',
        'repayment_details' => 'Rückzahlungsdetails',
        'amount_due' => 'Fälliger Betrag',
        'due_date' => 'Fälligkeitsdatum',
        'investment_ref' => 'Investitionsreferenz',
        'payment_instructions' => 'Zahlungsanweisungen',
        'make_payment' => 'Zahlung tätigen',
    ],

    'invoice' => [
        'subject' => 'Rechnung :invoice_number',
        'greeting' => 'Hallo, :name!',
        'body' => 'Bitte finden Sie Ihre Rechnung im Anhang.',
        'invoice_details' => 'Rechnungsdetails',
        'invoice_number' => 'Rechnungsnummer',
        'invoice_date' => 'Rechnungsdatum',
        'due_date' => 'Fälligkeitsdatum',
        'amount' => 'Gesamtbetrag',
        'payment_instructions' => 'Zahlungsanweisungen',
        'view_invoice' => 'Rechnung online ansehen',
        'attached' => 'Rechnungs-PDF ist dieser E-Mail beigefügt.',
    ],

    'common' => [
        'button' => [
            'login' => 'Zum Dashboard anmelden',
            'view' => 'Details anzeigen',
            'contact_support' => 'Support kontaktieren',
        ],
        'footer' => [
            'thanks' => 'Vielen Dank, dass Sie sich für unsere Plattform entschieden haben!',
            'questions' => 'Bei Fragen kontaktieren Sie uns bitte unter :email',
            'unsubscribe' => 'Von diesen E-Mails abmelden',
        ],
    ],
];
