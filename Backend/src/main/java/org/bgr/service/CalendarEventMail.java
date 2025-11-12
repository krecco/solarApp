package org.bgr.service;

import biweekly.Biweekly;
import biweekly.ICalendar;
import biweekly.component.VEvent;
import biweekly.parameter.ParticipationLevel;
import biweekly.property.Attendee;
import biweekly.property.Method;
import biweekly.property.Organizer;
import biweekly.util.Duration;
import org.eclipse.microprofile.config.inject.ConfigProperty;

import javax.activation.DataHandler;
import javax.activation.DataSource;
import javax.enterprise.context.ApplicationScoped;
import javax.mail.Message;
import javax.mail.Session;
import javax.mail.Transport;
import javax.mail.internet.*;
import javax.mail.util.ByteArrayDataSource;
import java.time.LocalDateTime;
import java.util.Date;
import java.util.Properties;

@ApplicationScoped
public class CalendarEventMail {
    @ConfigProperty(name = "quarkus.mailer.host")
    String mailHost;

    @ConfigProperty(name = "quarkus.mailer.port")
    String mailPort;

    @ConfigProperty(name = "quarkus.mailer.username")
    String mailUser;

    @ConfigProperty(name = "quarkus.mailer.password")
    String maiPass;

    @ConfigProperty(name = "app.webInfo.notificationMail")
    String notificationMail;

    @ConfigProperty(name = "app.webInfo.eventSendMail")
    String eventSendMail;

    public void sendEvent(String subject, String body, String eventSubject, String eventDescription, Date eventDate, String sendTo, String location) throws Exception {
        // Create a mail session, specifying SMTP server
        final Properties properties = System.getProperties();
        properties.setProperty("mail.transport.protocol", "smtp");
        properties.setProperty("mail.smtp.host", mailHost);
        properties.setProperty("mail.smtp.port", mailPort);
        properties.setProperty("mail.smtp.auth", "true");
        properties.setProperty("mail.smtp.starttls.enable", "true");
        properties.setProperty("mail.debug", "true");

        properties.setProperty("mail.smtp.socketFactory.class", "javax.net.ssl.SSLSocketFactory");
        properties.setProperty("mail.smtp.class", "com.sun.mail.smtp.SMTPTransport");

        final Session mailSession = Session.getDefaultInstance(properties);

        // Calendar as message content
        final MimeMessage message = createMultipartTextFileAndCalendar(mailSession, body, eventSubject, eventDescription, eventDate, location);
        // Simple Calendar-only mail:
        //final MimeMessage message = createICalPart(new MimeMessage(mailSession));

        // send
        final InternetAddress toAddress = new InternetAddress(sendTo);
        final InternetAddress fromAddress = new InternetAddress(eventSendMail);
        message.setRecipient(Message.RecipientType.TO, toAddress);
        message.setSender(fromAddress);
        message.setFrom(fromAddress);
        message.setSubject(subject);

        try {
            Transport.send(message, mailUser, maiPass);
            System.out.println("this is OK");
        } catch (Exception e) {
            System.out.println("this is fail");
            System.out.println(e);
        }

    }

    private static MimeMessage createMultipartTextFileAndCalendar(final Session mailSession, String body, String eventSubject, String eventDescription, Date eventDate, String location) throws Exception {
        final MimeMessage message = new MimeMessage(mailSession);
        final MimeMultipart mixedMultipart = new MimeMultipart("mixed");
        mixedMultipart.addBodyPart(createICalPart(new MimeBodyPart(), eventSubject, eventDescription, eventDate, location));
        mixedMultipart.addBodyPart(createBodyContent(body));
        //disable for now
        //mixedMultipart.addBodyPart(createTextAttachment());

        message.setContent(mixedMultipart);
        return message;
    }

    public static MimeBodyPart createTextAttachment() throws Exception {
        final MimeBodyPart textAttachmentPart =  new MimeBodyPart();
        textAttachmentPart.setContent("no stuffs", "text/plain; charset=UTF-8");
        textAttachmentPart.addHeader("Content-Disposition", "attachment; filename=fun.txt");
        return textAttachmentPart;
    }

    public static MimeBodyPart createBodyContent(String body) throws Exception {
        final MimeBodyPart htmlPart =  new MimeBodyPart();
        htmlPart.setContent( body, "text/html; charset=utf-8" );
        return htmlPart;
    }

    public static <T extends MimePart> T createICalPart(T mimePartForCalendar, String eventSubject, String eventDescription, Date eventDate, String location) throws Exception {
        final DataSource source = new ByteArrayDataSource(generateICalData(eventSubject, eventDescription, eventDate, location), "text/calendar; charset=UTF-8");
        mimePartForCalendar.setDataHandler(new DataHandler(source));
        mimePartForCalendar.setHeader("Content-Type", "text/calendar; charset=UTF-8; method=REQUEST");
        return mimePartForCalendar;
    }


    public static String generateICalData(String eventSubject, String eventDescription, Date eventDate, String location) {
        ICalendar ical = new ICalendar();
        ical.addProperty(new Method(Method.REQUEST));

        VEvent event = new VEvent();
        event.setSummary(eventSubject);
        event.setDescription(eventDescription);

        event.setDateStart(eventDate);

        event.setDuration(new Duration.Builder()
                .hours(0)
                .minutes(45)
                .build());

        event.setLocation(location);

        event.setOrganizer(new Organizer("solar.family", "info@solar.family"));

        /*
        Attendee a = new Attendee("", "borut.grauf@gmail.com");
        a.setParticipationLevel(ParticipationLevel.REQUIRED);
        event.addAttendee(a);
         */

        ical.addEvent(event);
        return Biweekly.write(ical).go();
    }
}