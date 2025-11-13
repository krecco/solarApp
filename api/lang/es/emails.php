<?php

return [
    // Email subjects
    'welcome_subject' => 'Bienvenido a :app_name',
    'verification_subject' => 'Verifique su dirección de correo electrónico',
    'password_reset_subject' => 'Restablezca su contraseña',
    'investment_confirmed_subject' => 'Inversión Confirmada - :contract_number',
    'repayment_processed_subject' => 'Reembolso Procesado - :amount',
    'document_ready_subject' => 'Su documento está listo',
    'new_message_subject' => 'Tiene un nuevo mensaje',
    'plant_status_subject' => 'Actualización de Estado de Planta Solar - :plant_name',

    // Email greetings
    'greeting' => 'Hola',
    'greeting_with_name' => 'Hola :name',
    'regards' => 'Saludos cordiales',
    'team_signature' => 'El equipo de :app_name',

    // Welcome email
    'welcome_line1' => '¡Bienvenido a :app_name! Nos complace tenerte con nosotros.',
    'welcome_line2' => 'Ahora puede acceder a su cuenta y administrar sus inversiones.',
    'welcome_action' => 'Comenzar',

    // Email verification
    'verify_line1' => 'Por favor, haga clic en el botón de abajo para verificar su dirección de correo electrónico.',
    'verify_line2' => 'Si no creó una cuenta, no se requiere ninguna acción adicional.',
    'verify_action' => 'Verificar dirección de correo electrónico',
    'verify_expire' => 'Este enlace de verificación expirará en :count minutos.',

    // Password reset
    'reset_line1' => 'Recibe este correo electrónico porque recibimos una solicitud de restablecimiento de contraseña para su cuenta.',
    'reset_line2' => 'Si no solicitó un restablecimiento de contraseña, no se requiere ninguna acción adicional.',
    'reset_action' => 'Restablecer contraseña',
    'reset_expire' => 'Este enlace de restablecimiento de contraseña expirará en :count minutos.',

    // Investment notifications
    'investment_confirmed_line1' => 'Su inversión ha sido confirmada exitosamente.',
    'investment_confirmed_line2' => 'Monto: :amount',
    'investment_confirmed_line3' => 'Número de Contrato: :contract_number',
    'investment_confirmed_action' => 'Ver detalles de inversión',

    // Repayment notifications
    'repayment_processed_line1' => 'Se ha procesado un reembolso para su inversión.',
    'repayment_processed_line2' => 'Monto: :amount',
    'repayment_processed_line3' => 'Fecha: :date',
    'repayment_processed_action' => 'Ver detalles del reembolso',

    // Document notifications
    'document_ready_line1' => 'Su documento solicitado está ahora disponible para descargar.',
    'document_ready_line2' => 'Tipo de Documento: :type',
    'document_ready_action' => 'Descargar documento',

    // Message notifications
    'new_message_line1' => 'Ha recibido un nuevo mensaje.',
    'new_message_line2' => 'De: :from',
    'new_message_action' => 'Ver mensaje',

    // Plant status notifications
    'plant_status_line1' => 'Actualización de estado para su planta solar: :plant_name',
    'plant_status_line2' => 'Estado: :status',
    'plant_status_action' => 'Ver detalles de la planta',

    // Common email elements
    'button_trouble' => 'Si tiene problemas para hacer clic en el botón ":action", copie y pegue la siguiente URL en su navegador web:',
    'unsubscribe' => 'Si ya no desea recibir estos correos electrónicos, puede',
    'unsubscribe_link' => 'darse de baja aquí',
    'email_footer' => '© :year :app_name. Todos los derechos reservados.',
    'privacy_policy' => 'Política de Privacidad',
    'terms_of_service' => 'Términos de Servicio',
    'contact_us' => 'Contáctenos',
];
